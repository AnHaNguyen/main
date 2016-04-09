/*------------------------Drag and Drop Feature---------------------*/
$(function() {
	$("#sortable-1, #sortable-2, #sortable-3, #sortable-4").sortable({
		connectWith: "#sortable-1, #sortable-2, #sortable-3, #sortable-4"
	});
});

function showDegReq() {
	$("#page-title span").html("Degree Requirement");

	$("#deg-req-div").show();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").hide();
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

$("#get-started-btn").on("click", function() {
	$("#start-text-div").hide();
	$("#grad-cer-div").show();
});

$("#starter-confirm-btn").on("click", function() {
	$("#start-page").hide();
	$("#main-section").show();
	updateDegReq();
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
});

$("#deg-req-nav").on("click", function (){
	showDegReq();
});

$("#taken-mod-nav").on("click", function (){
	$("#page-title span").html("Modules Taken");

	$("#deg-req-div").hide();
	$("#taken-mod-div").show();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").hide();
});

$("#plan-mod-nav").on("click", function (){
	$("#page-title span").html("Modules Planner");

	$("#deg-req-div").hide();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").show();
	$("#exempt-mod-div").hide();
});

$("#exempt-mod-nav").on("click", function (){
	$("#page-title span").html("Modules Exemption");

	$("#deg-req-div").hide();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").show();
});