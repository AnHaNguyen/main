$(function() {
	$("#sortable-1, #sortable-2, #sortable-3, #sortable-4").sortable({
		connectWith: "#sortable-1, #sortable-2, #sortable-3, #sortable-4"
	});
});

$(document).ready(function() {
	$("#deg-req-div").hide();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").hide();

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
});

$("#deg-req-nav").on("click", function (){
	$("#deg-req-div").show();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").hide();

});

$("#taken-mod-nav").on("click", function (){
	$("#deg-req-div").hide();
	$("#taken-mod-div").show();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").hide();
});

$("#plan-mod-nav").on("click", function (){
	$("#deg-req-div").hide();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").show();
	$("#exempt-mod-div").hide();
});

$("#exempt-mod-nav").on("click", function (){
	$("#deg-req-div").hide();
	$("#taken-mod-div").hide();
	$("#plan-mod-div").hide();
	$("#exempt-mod-div").show();
});