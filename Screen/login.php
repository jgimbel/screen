<?php

include_once("php/conn.php");

$error = "";
if(isset($_POST["edPassword"])){
	$sql = "select password	from settings";
	$result = mysql_query($sql,$link);

	if (!$result) {
		$message  = 'Invalid query: ' . mysql_error() . "\n";
		$message .= 'Whole query: ' . $query;
		die($message);
	}

	while ($row = mysql_fetch_assoc($result)) {
		$pass = $row["password"];
		if($pass != $_POST["edPassword"]){
			$error = "Invalid Password";
		} else {
			session_start();
			$_SESSION["ScreenSession"] = "Authenticated";
			header("location:admin.php");
		}
	}
	mysql_close($link);
}

?>

<html>
<head>
<link href="css/login.css" rel="stylesheet" type="text/css" />
</head>
<body>
<form action="login.php" method="post">
	<span class="login-error"><?php print($error); ?></span>
	<div class="login-box">
		<span class="login-header"> Screen Program </span>
		<div class="login-line"></div>
		<div class="login-fields-box">
			Password:
			<input type="password" name="edPassword" id="edPassword" />
			<input type="submit" value="Login" />
		</div>
	</div>
</form>
</body>
</html>