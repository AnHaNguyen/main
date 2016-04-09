function showDegReq() {
	updateDegReq();	
	$("#deg-req-div").show();
	$("#all-mod-div").hide();
	$("#plan-mod-div").hide();
}

/*-----------------------Start-page--------------------------*/
$("#get-started-btn").on("click", function() {
	$("#start-text-div").hide();
	$("#grad-cer-div").show();
});

$("#starter-confirm-btn").on("click", function() {
	$("#start-page").hide();
	$("#main-section").show();
	showDegReq();
});

$(document).ready(function() {
	$("#main-section").hide();
	$("#grad-cer-div").hide();

	 // the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
	 $(".modal-trigger").leanModal();

	 $("#taken-search-box").focus(function(){
	 	$("#taken-search-box").keypress(function(e) {
	 		if(e.which == 13) {		// pressing enter button
	 			$("#search-modal").openModal();

	 			$(".close-btn").on("click", function() {
	 				$("#search-modal").closeModal();
	 			});

	 			Materialize.toast(' is added into ', 4000);
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
	$("a").on('click', function(event) {

	    // Prevent default anchor click behavior
	    event.preventDefault();

	    // Store hash
	    var hash = this.hash;

	    // Using jQuery's animate() method to add smooth page scroll
	    // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
	    $('html, body').animate({
	    	scrollTop: $(hash).offset().top - 80
	    }, 800, function(){
	    	
	      // Add hash (#) to URL when done scrolling (default click behavior)
	      window.location.hash = hash;
	  });
	});

	/*----------------Lighten - darken color----------------*/
	var darkenDragDropColor = LightenDarkenColor("#b3c100", -40);
	$(".drag-drop-item").on("mouseover", function() {
		$(this).css("background-color", "" + darkenDragDropColor);
	});

	$(".drag-drop-item").on("mouseout", function() {
		$(this).css("background-color", "#b3c100");
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

function semPlanner() {
	$(".semester-div").on("click", function(){
		$(this).addClass("selected-sem-planner");
		$(this).parent().find(".mc-div").addClass("selected-sem-planner");
		$(this).parent().find(".drag-n-drop").css("background-color", "#EEEEEE");
	});
}

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

function updateDegReq() {
	var jsonFile = "req/" + $('#major').val() + '/' + $('#admission_year').val() + ".json";
	console.log(jsonFile);
	$.getJSON(jsonFile, function(jsonContent){
		var newHtml = '<div class="main-table col s12">';
		$.each(jsonContent.and, function (reqType, value) {
			console.log(reqType + ": " + value);

			if ($.type(value)==="number") {
				template = `<div class="col s12 content-row"> \
						<div class="col s10 header item">{{reqType}}</div> \ 
						<div class="col s2 header item">{{value}}</div> \
					</div>`;
				newHtml += template.replace("{{reqType}}", reqType).replace("{{value}}", value);
			} else {
				newHtml += '<div class="col s12 content-row"> <div class="col s12 header item">{{reqType}}</div></div>'.replace("{{reqType}}", reqType) ;

				$.each(value, function(module, MC) {
					template = `<div class="col s12 content-row"> \
							<div class="col s10 item">{{module}}</div> \ 
							<div class="col s2 item">{{MC}}</div> \
						</div>`;

					newHtml += template.replace("{{module}}", module).replace("{{MC}}", MC);
				})

				newHtml += '</div>';
			} 
		}); 

		$.each(jsonContent.or, function(index, value) {
			console.log(index + ": " + value);
		});

		newHtml += "</div>";
		$("#deg-req-div").html(newHtml);
	});
}