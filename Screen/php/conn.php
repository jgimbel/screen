<?php

$link = mysql_connect("localhost", "mysql");
if (!$link) { die('Could not connect: ' . mysql_error()); }

$db_selected = mysql_select_db('screen', $link);
if (!$db_selected) { die ('Can\'t use screen database : ' . mysql_error()); }

?>
