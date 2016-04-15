function showDegReq() {
	updateDegReq();
	$("#deg-req-div").show();
	$("#all-mod-div").hide();
	$("#plan-mod-div").hide();
}

function showMainPage() {
	$("#start-page").hide();
	$("#main-section").show();
	showDegReq();

	/*--------------Transitions for navigation bar----------------*/
	Materialize.fadeInImage("#nav-mobile");
	Materialize.showStaggeredList("#sidebar-all-items");
	Materialize.fadeInImage(".page-title");
	Materialize.fadeInImage(".main-table");
}

function loginWithIVLE() {
	$("#start-text-div").hide();
	$("#grad-cer-div").show();

	var token = getIVLEToken();
	initializeUser(token, function(user){
		major = getMajor(user).toString();
		year = getAdmissionYear(user).toString();

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

/*-----------------------Start-page--------------------------*/
$("#get-started-btn").on("click", function() {
	$("#start-text-div").hide();
	$("#grad-cer-div").show();
});

$("#starter-confirm-btn").on("click", function() {
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
});

/*----------Pre-loader-----------*/
$(window).load(function(){
	$("#preloader").delay(500).fadeOut("slow");
});

$(document).ready(function() {
	$("#main-section").hide();
	$("#grad-cer-div").hide();

	if(ivle.getToken(window.location.href) != null) {
		loginWithIVLE();
	}

	// the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
	$(".modal-trigger").leanModal();

	$(".search-input").focus(function(){
	 	$(".search-input").keypress(function(e) {
	 		if(e.which == 13) {		// pressing enter button
	 			Materialize.toast('New module is added into ', 2000);
	 		}
	 	});
	 });

	 $("#homepage-btn").on("click", function() {
	 	$("#home-page-div").hide();
	 	$("#deg-req-div").show();
	 });

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

		/*------------Multiple-choice------------*/
	$(".dropdown-content a").on("click", function() {
		console.log("hey, printed111111111111 !!!")
		$(this).find(".taken-choice").on("click", function(){
			var parent = $(this).parent().parent().find(".dropbtn").text("Taken");
			console.log("hey, printed !!!")
		});

		$(this).find(".plan-choice").on("click", function(){
			var parent = $(this).parent().parent().find(".dropbtn").text("Plan");
			console.log("hey, printed 222222222!!!")
		});

		$(this).find(".waived-choice").on("click", function(){
			var parent = $(this).parent().parent().find(".dropbtn").text("Waived");
			console.log("hey, printed 33333333!!!")
		});
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
