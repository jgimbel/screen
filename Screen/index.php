<?php

ini_set('max_execution_time', 300);

include_once("php/conn.php");

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

$MainImagesJson = json_encode($MainImages);
$RightImagesJson = json_encode($RightImages);

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
    extract($row);
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
$StocksJson = json_encode($Stocks);

// END OF STOCKS //


// NEWS //
$News = array();

// http://www.cnn.com/services/rss/
// http://www.gummy-stuff.org/Yahoo-data.htm

$xmlDoc = new DOMDocument();
//$xmlDoc->load("http://rss.cnn.com/rss/cnn_topstories.rss");
//$xmlDoc->load("http://articlefeeds.nasdaq.com/nasdaq/categories?category=Stocks");
//$xmlDoc->load("http://feeds.marketwatch.com/marketwatch/bulletins");
$xmlDoc->load("http://rss.cnn.com/rss/money_latest.rss");


//get elements from "<channel>"
$channel=$xmlDoc->getElementsByTagName('channel')->item(0);
$channel_title = $channel->getElementsByTagName('title')
->item(0)->childNodes->item(0)->nodeValue;
//$channel_link = $channel->getElementsByTagName('link')
//->item(0)->childNodes->item(0)->nodeValue;
//$channel_desc = $channel->getElementsByTagName('description')
//->item(0)->childNodes->item(0)->nodeValue;

//output elements from "<channel>"
//echo("<p><a href='" . $channel_link
//  . "'>" . $channel_title . "</a>");
//echo("<br>");
//echo($channel_desc . "</p>");

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
	
	//echo($item_title."<br>");
	//echo ("<p><a href='" . $item_link
	//. "'>" . $item_title . "</a>");
	//echo ("<br>");
	//echo ($item_desc . "</p>");
	$News[$i] = $item_title;
}

$NewsJson = json_encode($News);

// END OF NEWS //

// WEATHER //

require("php/SimplePie.compiled.php");
require("php/SimplePie.Weather.php");
 
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

?>

<html>
<head>
	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<link href="css/index.css" rel="stylesheet" type="text/css" />
	<script>
	
	// VARS FROM PHP //
	var NewsArray = <?php echo $NewsJson; ?>;
	var StocksArray = <?php echo $StocksJson; ?>;
	var MainImagesArray = <?php echo $MainImagesJson; ?>;
	var RightImagesArray = <?php echo $RightImagesJson; ?>;
	
	// CONFIG VARS //
	var ChangeToStocksAfter = <?php echo $news_count; ?>;
	var NewsTimer = <?php echo $news_timer; ?>;
	var StocksTimer = <?php echo $stocks_timer; ?>;
	var HeaderTimer = <?php echo $header_timer; ?>;
	var MainImageTimer = <?php echo $main_image_timer; ?>;
	var RightImageTimer = <?php echo $right_image_timer; ?>;
	
	// PROGRAM VARS //
	var ChangeToStocksPos = 1;
	
	var NewsToShow = 0;
	var NewsInterval = null;
	
	var StockToShow = 0;
	var StocksInterval = null;
	var BackToNews = 0;
	
	var HeaderInterval = null;
	var HeaderArray = new Array("dvLogo", "dvWeather");
	var HeaderToShow = 0;
	
	var MainImageInterval = null;
	var MainImageToShow = 0;
	
	var RightImageInterval = null;
	var RightImageToShow = 0;

	function Initialize(){
		ShowNews();
		ShowHeader();
		ShowMainImage();
		ShowRightImage();
	}
	
	function ShowMainImage(){
		$("#imgMain").attr("src","images/uploads/"+MainImagesArray[MainImageToShow]);
		$("#imgMain").fadeIn(1000);
		MainImageInterval = setInterval(function(){ SwitchMainImage(); }, 5000);
	}
	
	function SwitchMainImage(){
		$("#imgMain").fadeOut(1000, function() {
			MainImageToShow++;
			if(MainImageToShow == MainImagesArray.length){ MainImageToShow = 0; }
			$("#imgMain").attr("src","images/uploads/"+MainImagesArray[MainImageToShow]);
			$("#imgMain").fadeIn(1000);
		});
	}
	
	function ShowRightImage(){
		$("#imgRight").attr("src","images/uploads/"+RightImagesArray[RightImageToShow]);
		$("#imgRight").fadeIn(1000);
		RightImageInterval = setInterval(function(){ SwitchRightImage(); }, 3000);
	}
	
	function SwitchRightImage(){
		$("#imgRight").fadeOut(1000, function() {
			RightImageToShow++;
			if(RightImageToShow == RightImagesArray.length){ RightImageToShow = 0; }
			$("#imgRight").attr("src","images/uploads/"+RightImagesArray[RightImageToShow]);
			$("#imgRight").fadeIn(1000);
		});
	}
	
	function ShowHeader(){
		$("#"+HeaderArray[HeaderToShow]).fadeIn(1000);
		HeaderInterval = setInterval(function(){ SwitchHeader(); }, HeaderTimer);
	}
	
	function SwitchHeader(){
		$("#"+HeaderArray[HeaderToShow]).fadeOut(1000, function() {
			HeaderToShow++;
			if(HeaderToShow == HeaderArray.length){ HeaderToShow = 0; }
			$("#"+HeaderArray[HeaderToShow]).fadeIn(1000);
		});
	}
	
	function ShowNews(){
		NewsInterval = setInterval(function(){ SwitchNews(); }, NewsTimer);
		
		$("#dvNews").css("display","none");
		$("#dvNews").html(NewsArray[NewsToShow]);
		$("#dvNews").fadeIn(1000);
		if(NewsToShow == NewsArray.length-1){ NewsToShow = 0; } else { NewsToShow++; }
	}
	
	function SwitchNews(){
		$("#dvNews").fadeOut(1000, function() {
			$("#dvNews").html(NewsArray[NewsToShow]); 
			if(ChangeToStocksPos < ChangeToStocksAfter){
				$("#dvNews").fadeIn(1000, function(){
					ChangeToStocksPos++;
					if(NewsToShow == NewsArray.length-1){ NewsToShow = 0; } else { NewsToShow++; }
				}); 
			} else {
				clearInterval(NewsInterval);
				ChangeToStockPos = 1;
				ShowStocks();
			}
		});
	}
	
	function ShowStocks(){
		StocksInterval = setInterval(function(){ SwitchStocks(); }, StocksTimer);
		
		SetStockInfo(1,0);
		setTimeout(function(){ $("#dvStocks1").fadeIn(1000); }, 200);
		SetStockInfo(2,1);
		setTimeout(function(){ $("#dvStocks2").fadeIn(1000); }, 500);
		SetStockInfo(3,2);
		setTimeout(function(){ $("#dvStocks3").fadeIn(1000); }, 800);
		StockToShow = 3;
	}
	
	function SwitchStocks(){
		if(BackToNews || StockToShow == StocksArray.length+2){
			setTimeout(function(){ $("#dvStocks1").fadeOut(1000, function(){
				ShowNews();
			}); }, 200);
			setTimeout(function(){ $("#dvStocks2").fadeOut(1000); }, 200);
			setTimeout(function(){ $("#dvStocks3").fadeOut(1000); }, 200);
			clearInterval(StocksInterval);
			BackToNews = 0;
			StockToShow = 0;
			ChangeToStocksPos = 1;
			return;
		}
	
		setTimeout(function(){ $("#dvStocks1").fadeOut(1000, function(){
			if(SetStockInfo(1,StockToShow)){
				setTimeout(function(){ $("#dvStocks1").fadeIn(1000); }, 200);
				StockToShow++;
			}
		}); }, 200);
		setTimeout(function(){ $("#dvStocks2").fadeOut(1000, function(){
			if(SetStockInfo(2,StockToShow)){
				setTimeout(function(){ $("#dvStocks2").fadeIn(1000); }, 500);
				StockToShow++;
			}
		}); }, 200);
		setTimeout(function(){ $("#dvStocks3").fadeOut(1000, function(){
			if(SetStockInfo(3,StockToShow)){
				setTimeout(function(){ $("#dvStocks3").fadeIn(1000); }, 800);
				StockToShow++;
			}
		}); }, 200);
	}
	
	function SetStockInfo(dvnum, pos){
		var obj = StocksArray[pos];
		if(obj == null){ BackToNews = 1; return false; }
		if(obj.Change > 0){
			$("#dvStocks" + dvnum).attr("class","dvStockUp");
			$("#imgStockImg" + dvnum).attr("src","images/up.png");
		} else {
			$("#dvStocks" + dvnum).attr("class","dvStockDown");
			$("#imgStockImg" + dvnum).attr("src","images/down.png");
		}
		$("#lbStockNamePts" + dvnum).html(obj.Code + " " + obj.CurrentValue);
		$("#lbStockPct" + dvnum).html(obj.Change.replace("-","").replace("+","") + " (" + obj.PercentChange.replace("-","").replace("+","") + "%)");
		return true;
	}

	</script>
</head>
<body onload="Initialize();">
    <div id="dvTopLeft">
        <div id="dvLogo" style="display:none;"><img src="images/logo.png" id="imgTopLeft" /></div>
		<div id="dvWeather" style="display:none;" class="weather">
			<div class="weather-icon-div"><img src="images/Weather/<?php print($icon); ?>.png" /></div>
			<center>
			<div class="weather-info-div">
				<span class="weather-info-header">Union College Weather</span>
				<span class="weather-info-current"><?php print($weather->get_temperature()); ?>&deg;F</span>
				<span class="weather-info-highs">High: <?php print($fore[0]->get_high()); ?>&deg;F | Low: <?php print($fore[0]->get_low()); ?>&deg;F</span>
				<span class="weather-info-forecast"><?php print($next_day); ?> Low: <?php print($fore[1]->get_low()); ?>&deg;F
						High: <?php print($fore[1]->get_high()); ?>&deg;F</span>
				<span class="weather-info-forecast"><?php print($next_day2); ?> Low: <?php print($fore[2]->get_low()); ?>&deg;F
				High: <?php print($fore[2]->get_high()); ?>&deg;F</span>
			</div>
			</center>
		</div>
    </div>
    <div id="dvMiddleLeft">
		<img id="imgMain" style="max-width:100%; display:none;"></img>
    </div>
    <div id="dvBottom">
		<div id="dvNews"></div>
		
			<div id="dvStocks1" style="display:none;">
			<img id="imgStockImg1" style="height:30%;">
			<label id="lbStockNamePts1" style="font-weight:bold;"></label>
			<label id="lbStockPct1" style="font-size:60%;"></label>
			</div>
			
			<div id="dvStocks2" style="display:none;">
			<img id="imgStockImg2" style="height:30%;">
			<label id="lbStockNamePts2" style="font-weight:bold;"></label>
			<label id="lbStockPct2" style="font-size:60%;"></label>
			</div>
			
			<div id="dvStocks3" style="display:none;">
			<img id="imgStockImg3" style="height:30%;">
			<label id="lbStockNamePts3" style="font-weight:bold;"></label>
			<label id="lbStockPct3" style="font-size:60%;"></label>
			</div>
		</div>
    </div>
    <div id="dvRight">
		<img id="imgRight" style="max-height:100%; display:none;"></img>
    </div>
</body>
</html>
