	// VARS FROM PHP //
var NewsArray = null;
var StocksArray = null;
var MainImagesArray = null;
var RightImagesArray = null;

// CONFIG VARS //
var ChangeToStocksAfter = null;
var NewsTimer = null;
var StocksTimer = null;
var HeaderTimer = null;
var MainImageTimer = null;
var RightImageTimer = null;
$(document).ready(function(){setTimeout(init(), 3600000)});/*every hour update*/ //do when document is ready.

function init(){
    $.ajax({
        url: "../php/refresh.php",
        type: "GET",
        timeout: 5000,
        success: function (result) {
            NewsArray = result.news;
            StocksArray = result.stocks;
            MainImagesArray = result.main;
            RightImagesArray = result.right;
            ChangeToStocksAfter = result.newsCount;
            NewsTimer = result.newsTimer;
        }
    });
}



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
