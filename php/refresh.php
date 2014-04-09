<?php

ini_set('max_execution_time', 300);

include_once("conn.php");

// IMAGES //
$MainImages = array();
$RightImages = array();

$sql = "select path, location from image";
$result = mysql_query($sql,$link);

if (!$result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}

while ($row = mysql_fetch_assoc($result)) {
    if($row["location"] == "MAIN"){
		array_push($MainImages, $row["path"]);
	} else if($row["location"] == "RIGHT"){
		array_push($RightImages, $row["path"]);
	}
}

$MainImagesJson = $MainImages;
$RightImagesJson = $RightImages;

// END OF IMAGES //


$sql = "select stocks,
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
	// variables: stocks, news_rss, header_timer, stocks_timer, news_timer, main_image_timer, right_image_timer, news_count
    ($row);
}
mysql_close($link);

// STOCKS //
class Stock
{
	public $Code;
    	public $CurrentValue;
	public $PrevDayValue;
	public $Change;
	public $PercentChange;
}

$stock_list = explode(",",$stocks);

$Stocks = array();

for($i=0;$i<count($stock_list);$i++){
	try {

		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,"http://download.finance.yahoo.com/d/quotes.csv?s=".$stock_list[$i]."&f=sl1d1t1c1ohgv&e=.csv");
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Screen');
		$csv = curl_exec($curl_handle);
		curl_close($curl_handle);

		if(!empty($csv)){
			$stock_values = explode(",",$csv);

			$last_close = $stock_values[1]-$stock_values[4];
			if($last_close > 0){ $pct = number_format((($stock_values[1]*100)/$last_close)-100,2); } else { throw new Exception("Division by zero."); }

			$myStock = new Stock();
			$myStock->Code = str_replace('"', "", $stock_values[0]);
			$myStock->CurrentValue = $stock_values[1];
			$myStock->PrevDayValue = $last_close;
			$myStock->Change = $stock_values[4];
			$myStock->PercentChange = $pct;
			array_push($Stocks, $myStock);
		}
	} catch(Exception $ex){ }
}
$StocksJson = $Stocks;

// END OF STOCKS //


// NEWS //
$News = array();

$xmlDoc = new DOMDocument();
$xmlDoc->load("http://rss.cnn.com/rss/money_latest.rss");


//get elements from "<channel>"
$channel=$xmlDoc->getElementsByTagName('channel')->item(0);
$channel_title = $channel->getElementsByTagName('title')
->item(0)->childNodes->item(0)->nodeValue;

//get and output "<item>" elements
$x=$xmlDoc->getElementsByTagName('item');
for ($i=0; $i<=10; $i++)
{
	$item_title=$x->item($i)->getElementsByTagName('title')
	->item(0)->childNodes->item(0)->nodeValue;
	$item_link=$x->item($i)->getElementsByTagName('link')
	->item(0)->childNodes->item(0)->nodeValue;
	$item_desc=$x->item($i)->getElementsByTagName('description')
	->item(0)->childNodes->item(0)->nodeValue;

	$News[$i] = $item_title;
}

$NewsJson = $News;

// END OF NEWS //

// WEATHER //

require("SimplePie.compiled.php");
require("SimplePie.Weather.php");

$code = "68506";
$path = "http://weather.yahooapis.com/forecastrss?u=f&p=";
$feed = new SimplePie();
$feed->set_feed_url($path.$code);
$feed->set_item_class('SimplePie_Item_YWeather');
$feed->init();

function time2minuts($time) {
    $minuts = 0;
    $atime = explode(" ", $time);
    if (strtolower($atime[1]) == "pm") {
        $minuts = 12*60;
    }
    $ttime = explode(":", $atime[0]);
    $minuts = $minuts + (int)$ttime[0]*60 + (int)$ttime[1];
    return $minuts;
}

$weather = $feed->get_item(0);
$fore = $weather->get_forecasts();
$unit = $weather->get_units_temp();
$icon = $weather->get_condition_code();
$curday = 2*60 + time2minuts(date("g:i a"));
$iniday = time2minuts($weather->get_sunrise());
$endday = time2minuts($weather->get_sunset());

$next_day = (date('l', strtotime('+1 day', strtotime(date('Y-m-d')))));
$next_day2 = (date('l', strtotime('+2 day', strtotime(date('Y-m-d')))));

// END OF WEATHER //
//Creating JSON//
$Weather=array();
$Weather[]=$weather->get_temperature();
$Weather[]=$fore[0]->get_high();
$Weather[]=$fore[0]->get_low();
$Weather[]=$next_day;
$Weather[]=$fore[1]->get_high();
$Weather[]=$fore[1]->get_low();
$Weather[]=$next_day2;
$Weather[]=$fore[2]->get_high();
$Weather[]=$fore[2]->get_low();
$JSONArray = json_encode(array('news' => $NewsJson, 'stocks' => $StocksJson, 'main' => $MainImagesJson, 'right' => $RightImagesJson,
'newsCount' => $news_count, 'newsTimer' => $news_timer, 'stocksTimer' => $stocks_timer,
'headerTimer' => $header_timer, 'mainTimer' => $main_image_timer, 'rightTimer' => $right_image_timer,
'icon'=>$icon));

//End JSON//
echo $JSONArray;
?>
