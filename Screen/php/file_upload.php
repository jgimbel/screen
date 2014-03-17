<?php

include_once("conn.php");

class Result {
	public $Status = 0;
	public $Message;
}
$myResult = new Result();

$valid_exts = array('jpeg', 'jpg', 'png'); // valid extensions
$max_size = 5000 * 1024; // max file size
$path = '../images/uploads/'; // upload directory

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if( ! empty($_FILES['image']) ) {
    // get uploaded file extension
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    // looking for format and size validity
    if (in_array($ext, $valid_exts) AND $_FILES['image']['size'] < $max_size) {
      //$path = $path . uniqid(). '.' .$ext;
	  $name = explode(".", $_FILES['image']['name']);
	  $path = $path.$name[0].'.'.$ext;
      // move uploaded file from temp to uploads directory
      if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
		$sql = "select * from image where path = '{$_FILES['image']['name']}'";
		$result = mysql_query($sql,$link);
		if(mysql_num_rows($result) > 0){
			$myResult->Message = 'You cannot have two images with the same name.';
		} else {
			$sql = "insert into image (path, location) values('{$_FILES['image']['name']}','{$_POST['selLocation']}')";
			$result = mysql_query($sql,$link);
			if (!$result) {
				$message  = 'Invalid query: ' . mysql_error() . "\n";
				$message .= 'Whole query: ' . $sql;
				$myResult->Message = $message;
			} else {
				$myResult->Status = 1;
				$myResult->Message = 'File uploaded!';
			}	
		}
      }
    } else {
      $myResult->Message = 'Invalid file!';
    }
  } else {
    $myResult->Message = 'File not uploaded!';
  }
} else {
  $myResult->Message = 'Bad request!';
}

echo(json_encode($myResult));
?>