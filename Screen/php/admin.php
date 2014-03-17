<?php

include_once("conn.php");

/*
var username 	
var password 	
var rss_url 	
var stocks 		
var header_timer
var stocks_timer
var news_timer 	
var news_count
*/

extract($_POST,EXTR_PREFIX_ALL,"v");

if($v_action == "SaveSettings"){

	if(!isset($v_rss_url) || empty($v_rss_url)){ die("News RSS Url cannot be blank."); }

	if(!isset($v_stocks) || empty($v_stocks)){ die("Stocks cannot be blank."); }
	if(preg_match('/^[a-z,]+$/i', $v_stocks) == 0){ die("Invalid input for Stocks!\n\nPlease use letters only and comma as the delimeter."); }
	$v_stocks = strtoupper($v_stocks);

	if(!isset($v_header_timer) || empty($v_header_timer)){ die("Header timer cannot be blank."); }
	if(!is_numeric($v_header_timer)){ die("Header timer must be a number."); }
	if($v_header_timer == 0){ die("Header timer cannot be 0 (zero)."); }
	if($v_header_timer < 1000){ die("Header timer must be at least 1000 ms."); }

	if(!isset($v_stocks_timer) || empty($v_stocks_timer)){ die("Stocks timer cannot be blank."); }
	if(!is_numeric($v_stocks_timer)){ die("Stocks timer must be a number."); }
	if($v_stocks_timer == 0){ die("Stocks timer cannot be 0 (zero)."); }
	if($v_stocks_timer < 1000){ die("Stocks timer must be at least 1000 ms."); }

	if(!isset($v_news_timer) || empty($v_news_timer)){ die("News timer cannot be blank."); }
	if(!is_numeric($v_news_timer)){ die("News timer must be a number."); }
	if($v_news_timer == 0){ die("News timer cannot be 0 (zero)."); }
	if($v_news_timer < 1000){ die("News timer must be at least 1000 ms."); }
	
	if(!isset($v_main_image_timer) || empty($v_main_image_timer)){ die("Main Image timer cannot be blank."); }
	if(!is_numeric($v_main_image_timer)){ die("Main Image timer must be a number."); }
	if($v_main_image_timer == 0){ die("Main Image timer cannot be 0 (zero)."); }
	if($v_main_image_timer < 1000){ die("Main Image timer must be at least 1000 ms."); }
	
	if(!isset($v_right_image_timer) || empty($v_right_image_timer)){ die("Right Image timer cannot be blank."); }
	if(!is_numeric($v_right_image_timer)){ die("Right Image timer must be a number."); }
	if($v_right_image_timer == 0){ die("Right Image timer cannot be 0 (zero)."); }
	if($v_right_image_timer < 1000){ die("Right Image timer must be at least 1000 ms."); }

	if(!isset($v_news_count) || empty($v_news_count)){ die("News before Stocks cannot be blank."); }
	if(!is_numeric($v_news_count)){ die("News before Stocks must be a number."); }
	if($v_news_count == 0){ die("News before Stocks cannot be 0 (zero)."); }

	if(isset($v_password) && !empty($v_password)){
		if(strlen($v_password) < 6){ die("Password must be at least 6 characters long."); }
		
		$sql = "update settings set
					username = '{$v_username}',
					password = '{$v_password}',
					news_rss = '{$v_rss_url}',
					stocks = '{$v_stocks}',
					header_timer = '{$v_header_timer}',
					stocks_timer = '{$v_stocks_timer}',
					news_timer = '{$v_news_timer}',
					main_image_timer = '{$v_main_image_timer}',
					right_image_timer = '{$v_right_image_timer}',
					news_count = '{$v_news_count}'";
	} else {
		$sql = "update settings set
					username = '{$v_username}',
					news_rss = '{$v_rss_url}',
					stocks = '{$v_stocks}',
					header_timer = '{$v_header_timer}',
					stocks_timer = '{$v_stocks_timer}',
					news_timer = '{$v_news_timer}',
					main_image_timer = '{$v_main_image_timer}',
					right_image_timer = '{$v_right_image_timer}',
					news_count = '{$v_news_count}'";
	}
	$result = mysql_query($sql,$link);

	if (!$result) {
		$message  = 'Invalid query: ' . mysql_error() . "\n";
		$message .= 'Whole query: ' . $sql;
		die($message);
	}

}

if($v_action == "DeleteImage"){
	if(!isset($v_image_id) || empty($v_image_id)){ die("Error deleting image."); }
	
	$sql = "select path from image where image_id = {$v_image_id}";
	$result = mysql_query($sql,$link);

	if (!$result) {
		$message  = 'Invalid query: ' . mysql_error() . "\n";
		$message .= 'Whole query: ' . $query;
		die($message);
	}
	
	while ($row = mysql_fetch_assoc($result)) {	$v_path = $row["path"]; }
	$path_to_delete = realpath("../images/uploads");
	$path_to_delete .= "\\".$v_path;
	if(!is_readable($path_to_delete)){ die("File doesn't exist."); }
	if(unlink($path_to_delete)){	
		$sql = "delete from image where image_id = {$v_image_id}";
		$result = mysql_query($sql,$link);

		if (!$result) {
			$message  = 'Invalid query: ' . mysql_error() . "\n";
			$message .= 'Whole query: ' . $sql;
			die($message);
		}
	}
}

mysql_close($link);

echo("Information Successfully Saved!");

?>