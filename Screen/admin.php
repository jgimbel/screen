<?php

session_start();
if(!isset($_SESSION["ScreenSession"])){ header("location:login.php"); exit; }

include_once("php/conn.php");

$sql = "select
			username,
			stocks,
			news_rss,
			header_timer,
			stocks_timer,
			news_timer,
			main_image_timer,
			right_image_timer,
			news_count
		from settings";
$result = mysql_query($sql,$link);

if (!$result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}

while ($row = mysql_fetch_assoc($result)) {
	// variables: username, stocks, news_rss, header_timer, stocks_timer, news_timer, news_count
    extract($row,EXTR_PREFIX_ALL,"v");
}

$sql = "select image_id, path, location from image order by location, path";
$result = mysql_query($sql,$link);

if (!$result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}

?>

<html>
<head>
<link href="css/admin.css" rel="stylesheet" type="text/css" />
</head>
<body>

<h1>Settings</h1>
<div class="admin-line"></div>
<div class="admin-box ">
	<div class="admin-field-box">Username:</div> <div style="float:left;"><input id="edUsername" type="text" style="width:250px;" disabled="disabled" value="<?php print($v_username); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">Password:</div> <div style="float:left;"><input id="edPassword" type="password" style="width:250px;"  /></div><br style="clear:both;" />
	<div class="admin-field-box">News RSS URL:</div> <div style="float:left;"><input id="edRssUrl" type="text" style="width:250px;" value="<?php print($v_news_rss); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">Stocks:</div> <div style="float:left;"><input id="edStocks" type="text" style="width:250px;" value="<?php print($v_stocks); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">Header Timer (ms):</div> <div style="float:left;"><input id="edHeaderTimer" type="text" style="width:250px;" value="<?php print($v_header_timer); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">Stocks Timer (ms):</div> <div style="float:left;"><input id="edStocksTimer" type="text" style="width:250px;" value="<?php print($v_stocks_timer); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">News Timer (ms):</div> <div style="float:left;"><input id="edNewsTimer" type="text" style="width:250px;" value="<?php print($v_news_timer); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">Main Image Timer (ms):</div> <div style="float:left;"><input id="edMainImageTimer" type="text" style="width:250px;" value="<?php print($v_main_image_timer); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">Right Image Timer (ms):</div> <div style="float:left;"><input id="edRightImageTimer" type="text" style="width:250px;" value="<?php print($v_right_image_timer); ?>" /></div><br style="clear:both;" />
	<div class="admin-field-box">News before Stocks:</div> <div style="float:left;"><input id="edNewsCount" type="text" style="width:250px;" value="<?php print($v_news_count); ?>" /></div><br style="clear:both;" />
	<input type="button" value="Save" style="float:left;" onclick="Save();" />
</div>

<h1>Images</h1>
<!-- preview action or error msgs -->
<div id="preview" style="display:none; color:#FF0000;"></div>

<div style="height:1px; width:100%; background-color:#181818; margin-bottom:10px;"></div>
<!-- loader.gif -->
<img style="display:none" id="loader" height="100" src="images/loader.gif" alt="Loading...." title="Loading...." />
<!-- simple file uploading form -->
<form id="form" action="php/file_upload.php" method="post" enctype="multipart/form-data">
  Location:
  <select id="selLocation" name="selLocation">
	<option value="MAIN"> Main Box </option>
	<option value="RIGHT"> Right </option>
  </select>
  <input id="uploadImage" type="file" accept="image/*" name="image" />
  <input id="button" type="submit" value="Upload">
</form>

<div style="float:left; width:300px; font-weight:bold; padding:5px;"> Image </div>
<div style="float:left; width:100px; font-weight:bold; padding:5px;">Location </div>


<?php
while ($row = mysql_fetch_assoc($result)) {
    extract($row,EXTR_PREFIX_ALL,"v");	
?>

<div style="float:left; width:300px; clear:both; padding:5px;"> <?php print($v_path); ?></div>
<div style="float:left; width:100px; padding:5px;"><?php print($v_location); ?></div>
<div title="Delete image" style="float:left; width:100px; padding:5px; cursor:pointer;" onclick="DeleteImage(<?php print($v_image_id); ?>);"><img src="images/x.png" /> </div>

<?php
}
?>

</body>
</html>

<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/jquery.form.min.js"></script>

<script>
$(document).ready(function() {
  var f = $('form');
  var l = $('#loader'); // loder.gif image
  var b = $('#button'); // upload button
  var p = $('#preview'); // preview area

  b.click(function(){
    // implement with ajaxForm Plugin
    f.ajaxForm({
      beforeSend: function(){
        l.show();
        b.attr('disabled', 'disabled');
        p.fadeOut();
      },
      success: function(e){
		var data = JSON.parse(e);
		if(data.Status == 1){
			location.href = "admin.php";
		} else {
			l.hide();
			f.resetForm();
			b.removeAttr('disabled');
			p.html(data.Message).fadeIn();
		}
      },
      error: function(e){
        b.removeAttr('disabled');
        p.html(e).fadeIn();
      }
    });
  });
});

function Save(){
	var username 			= document.getElementById("edUsername").value;
	var password 			= document.getElementById("edPassword").value;
	var rss_url 			= document.getElementById("edRssUrl").value;
	var stocks 				= document.getElementById("edStocks").value;
	var header_timer		= document.getElementById("edHeaderTimer").value;
	var stocks_timer 		= document.getElementById("edStocksTimer").value;
	var news_timer 			= document.getElementById("edNewsTimer").value;
	var main_image_timer 	= document.getElementById("edMainImageTimer").value;
	var right_image_timer	= document.getElementById("edRightImageTimer").value;
	var news_count 			= document.getElementById("edNewsCount").value;
	
	var data_obj = { 
		username: 			username,
		password: 			password,
		rss_url: 			rss_url,
		stocks: 			stocks,
		header_timer: 		header_timer,
		stocks_timer: 		stocks_timer,
		news_timer: 		news_timer,
		main_image_timer: 	main_image_timer,
		right_image_timer: 	right_image_timer,
		news_count: 		news_count,
		action:				"SaveSettings"
	}

	$.ajax({
		url: 'php/admin.php',
		data: data_obj,
		type: 'post',
		success: function(output) {
					alert(output);
				}
	});
}

function DeleteImage(image_id){
	var data_obj = { 
		image_id: 	image_id,
		action:		"DeleteImage"
	}

	$.ajax({
		url: 'php/admin.php',
		data: data_obj,
		type: 'post',
		success: function(output) {
					alert(output);
					location.href = "admin.php";
				}
	});
}
</script>

<?php mysql_close($link); ?>