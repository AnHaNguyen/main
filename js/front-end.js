function showStartPage () {
	$("#main-section").hide();
	$("#grad-cer-div").hide();
	$("#grad-cer-div").hide();
	$("#back-div").css("display", "none");
	$("#forward-div").css("display", "none");
	$(".logout-div").css("display", "none");

	$(".start-page").show();
	$("#start-text-div").show();
}

function showCertiPage () {
	$("#main-section").hide();
	$("#grad-cer-div").hide();
	$("#start-text-div").hide();

	$(".start-page").show();
	$("#grad-cer-div").show();
	$("#back-div").css("display", "block");
	$("#forward-div").css("display", "block");
}

function showDegReq() {
	updateDegReq();
	$(".start-page").hide();
	$("#deg-req-div").show();
	$("#all-mod-div").hide();
	$("#plan-mod-div").hide();
}

function showMainPage() {
	$(".start-page").hide();
	$("#main-section").show();
	showDegReq();

	/*--------------Transitions for navigation bar----------------*/
	Materialize.showStaggeredList("#sidebar-all-items");
	Materialize.fadeInImage("#nav-mobile");
	Materialize.fadeInImage(".page-title");
	Materialize.fadeInImage(".main-table");
}

function loginWithIVLE() {
	showCertiPage();
	$(".logout-div").css("display", "block");

	var token = getIVLEToken();
	initializeUser(token, function(user){
		major = getMajor(user).toString();
		year = getAdmissionYear(user).toString();
		alert("hello ivle");

		if(major == "CS") {
			$("#major_value").val("Computer Science");
		}
		else if(major == "IS") {
			$("#major_value").val("Information System");		
		}
		else if(major == "BZA") {
			$("#major_value").val("Business Analytics");		
		}
		else if(major == "CEG") {
			$("#major_value").val("Computer Engineering");		
		}

		var AY = "20" + year.substr(0, 2) + "/20" + year.substr(2, 2);
		$("#admission_year_value").val(AY);

		$("#focus_area_value").val('');
		$("#focus-area-tip.tip span").css("display", "block");
	});
}

function certiInputChecking () {
	/*-------------Input Checking----------------*/
	var major = $("#major_value");
	var focus = $("#focus_area_value");
	var admission_year = $("#admission_year_value");

	if(!major.val()){
		$("#major-tip.tip span").css("display", "block");
		major.addClass("missing-input");
	}

	if(!focus.val()) {
		$("#focus-area-tip.tip span").css("display", "block");
		focus.addClass("missing-input");
	}

	if(!admission_year.val()) {
		$("#ay-tip.tip span").css("display", "block");
		admission_year.addClass("missing-input");
	}
	
	if(major.val() && focus.val() && admission_year.val()) {
		showMainPage();
	}

	/*---------------Input on focus---------------*/
	major.focus(function(){
		$("#major-tip.tip span").css("display", "none");
		major.removeClass("missing-input");
	});
	focus.focus(function(){
		$("#focus-area-tip.tip span").css("display", "none");
		focus.removeClass("missing-input");
	});
	admission_year.focus(function(){
		$("#ay-tip.tip span").css("display", "none");
		admission_year.removeClass("missing-input");
	});
}

/*-----------------------Start-page--------------------------*/
$("#get-started-btn").on("click", function() {
	showCertiPage();
	$(".logout-div").css("display", "none");
});

$("#starter-confirm-btn").on("click", function() {
	certiInputChecking();
});


/*----------Pre-loader-----------*/
$(window).load(function(){
	$(".preloader").delay(500).fadeOut("slow");
});

$(document).ready(function() {
	showStartPage();

	$('body').on('click','img#logo-img-cust',function(){
		showStartPage();	
	});

	if(getIVLEToken() != null) {
		loginWithIVLE();
		$(".logout-div").css("display", "block");
	}

	// the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
	$(".modal-trigger").leanModal();

	// Highlight selected li item
	var selector = ".collapsible-body ul li";

	$(selector).on("click", function(){
		$(selector).removeClass("active");
		$(this).addClass("active");
	});


	/*-------------Add smooth scrolling to all links---------------*/
	$(".smooth-scroll").on('click', function(event) {

	    // Prevent default anchor click behavior
	    event.preventDefault();

	    // Store hash
	    var hash = this.hash;

	    // Using jQuery's animate() method to add smooth page scroll
	    // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
	    $('html, body').animate({
	    	scrollTop: $(hash).offset().top - 80
	    }, 400, function(){
	    	
	      // Add hash (#) to URL when done scrolling (default click behavior)
	      window.location.hash = hash;
	  });
	});

	/*----------------Lighten - darken color----------------*/
	/*var darkenDragDropColor = LightenDarkenColor("#b3c100", -40);
	$(".drag-drop-item").on("mouseover", function() {
		$(this).css("background-color", "" + darkenDragDropColor);
	});

	$(".drag-drop-item").on("mouseout", function() {
		$(this).css("background-color", "#b3c100");
	});*/

	var darkenModal = LightenDarkenColor("#4cb5f5", -40);
	$(".modal-footer div").on("mouseover", function(){
		$(this).css("background-color", "" + darkenModal);
	});

	$(".modal-footer div").on("mouseout", function(){
		$(this).css("background-color", "#4cb5f5");
	});

	/*------------------Input in Advanced Search Box--------------------*/
	$(".input-div").on("click", function(){
		$(this).css("background-color", "#f0f0f0");
	});

	$(".input-div").on("focusout", function(){
		$(this).css("background-color", "white");
	});

	/*-----------------------Drag-and-drop-item-display----------------------*/
	$(".drag-drop-mc").hide();
	$("#code-btn").on("click", function(){
		if($(this).hasClass("selected-toggle-btn")){
			$(this).removeClass("selected-toggle-btn");
			$(".drag-drop-code").hide();
		} else {
			$(this).addClass("selected-toggle-btn");
			$(".drag-drop-code").show();
		}
	});

	$("#title-btn").on("click", function(){
		if($(this).hasClass("selected-toggle-btn")){
			$(this).removeClass("selected-toggle-btn");
			$(".drag-drop-title").hide();
		} else {
			$(this).addClass("selected-toggle-btn");
			$(".drag-drop-title").show();
		}
	});

	$("#mc-btn").on("click", function(){
		if($(this).hasClass("selected-toggle-btn")){
			$(this).removeClass("selected-toggle-btn");
			$(".drag-drop-mc").hide();
		} else {
			$(this).addClass("selected-toggle-btn");
			$(".drag-drop-mc").show();
		}
	});


	/*--------------------Trong Hiep code - jQueryScript-----------------------
	$(document).ready(function($) {
		var $banner = $('.banner'), $window = $(window);
		var $topDefault = parseFloat( $banner.css('top'), 10 );
			$window.on('scroll', function() {
				var $top = $(this).scrollTop();
				$banner.stop().animate( { top: $top + $topDefault }, 500, 'easeOutCirc' );
			});

		var $wiBanner = $banner.outerWidth() * 2;
		zindex( $('#wrapper').outerWidth() );
		$window.on('resize', function() {
			zindex( $('#wrapper').outerWidth() );
		});
		function zindex(maxWidth){
			if( $window.width() <= maxWidth + $wiBanner ) {
				$banner.addClass('zindex');
			} else {
				$banner.removeClass('zindex');
			}
		}
	});*/

	// Initialize collapse button
  	$(".button-collapse").sideNav();


  	/*----------------------Temporary hide-----------------------*/
  	$("#reset-btn").hide();
  	$(".filter-div").hide();


  	/*-----------Navigation---------------*/
  	$("#start-page-nav").on("click", function(){
  		showStartPage();
  	});

  	$("#certi-page-nav").on("click", function(){
  		showCertiPage();

  		if(getIVLEToken() == null) {
  			$(".logout-div").css("display", "none");
  		} else {
  			$(".logout-div").css("display", "block");
  		}
  	});

  	$("#forward-div").on("click", function(){
  		certiInputChecking();
  	});

	$("#back-div").on("click", function(){
  		showStartPage();
  	});  	

  	$("#deg-req-nav").on("click", function (){
  		showDegReq();
  	});

  	$("#all-mod-nav").on("click", function (){
  		$("#deg-req-div").hide();
  		$("#all-mod-div").show();
  		$("#plan-mod-div").hide();
  	});

  	$("#plan-mod-nav").on("click", function (){
  		$("#deg-req-div").hide();
  		$("#all-mod-div").hide();
  		$("#plan-mod-div").show();
  	});

});


/*-------------Toggle button taken/plan---------------*/
function toggleFunction() {
	$(".left-div").on("click", function() {
		var rightItem = $(this).parent().find(".right-div");
		var middleItem = $(this).parent().find(".middle-div");
		var isRight = rightItem.hasClass("selected-toggle-btn");
		var isMiddle = middleItem.hasClass("selected-toggle-btn");

		if(isRight && isMiddle){
			rightItem.removeClass("selected-toggle-btn");
			middleItem.removeClass("selected-toggle-btn");
		} else if(isRight) {
			rightItem.removeClass("selected-toggle-btn");
		} else if(isMiddle){
			middleItem.removeClass("selected-toggle-btn");
		}

		$(this).addClass("selected-toggle-btn");
	});

	$(".middle-div").on("click", function() {
		var leftItem = $(this).parent().find(".left-div");
		var rightItem = $(this).parent().find(".right-div");
		var isLeft = leftItem.hasClass("selected-toggle-btn");
		var isRight = rightItem.hasClass("selected-toggle-btn");

		if(isRight && isLeft){
			rightItem.removeClass("selected-toggle-btn");
			leftItem.removeClass("selected-toggle-btn");
		} else if(isRight) {
			rightItem.removeClass("selected-toggle-btn");
		} else if(isLeft){
			leftItem.removeClass("selected-toggle-btn");
		}

		$(this).addClass("selected-toggle-btn");
	});

	$(".right-div").on("click", function() {
		var leftItem = $(this).parent().find(".left-div");
		var middleItem = $(this).parent().find(".middle-div");
		var isLeft = leftItem.hasClass("selected-toggle-btn");
		var isMiddle = middleItem.hasClass("selected-toggle-btn");

		if(isLeft && isMiddle){
			leftItem.removeClass("selected-toggle-btn");
			middleItem.removeClass("selected-toggle-btn");
		} else if(isLeft) {
			leftItem.removeClass("selected-toggle-btn");
		} else if(isMiddle){
			middleItem.removeClass("selected-toggle-btn");
		}

		$(this).addClass("selected-toggle-btn");
	});
}

/*function semPlanner() {
	$(".semester-div").on("click", function(){
		$(this).addClass("selected-sem-planner");
		$(this).parent().find(".mc-div").addClass("selected-sem-planner");
		$(this).parent().find(".drag-n-drop").css("background-color", "#EEEEEE");
	});
}*/

		/*---------Code from CSS-tricks----------*/
function LightenDarkenColor(col, amt) { 
    var usePound = false;
    if (col[0] == "#") {
        col = col.slice(1);
        usePound = true;
    }
 
    var num = parseInt(col,16);
    var r = (num >> 16) + amt;
 
    if (r > 255) r = 255;
    else if  (r < 0) r = 0;
 
    var b = ((num >> 8) & 0x00FF) + amt;
 
    if (b > 255) b = 255;
    else if  (b < 0) b = 0;
 
    var g = (num & 0x0000FF) + amt;
 
    if (g > 255) g = 255;
    else if (g < 0) g = 0;
 
    return (usePound?"#":"") + (g | (b << 8) | (r << 16)).toString(16);
}
