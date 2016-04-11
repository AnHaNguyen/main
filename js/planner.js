function displayPlannerTable() {
	var token = getIVLEToken();
	var admissionYear, nextSem;
	if (token != null){
		admissionYear = parseInt(getAdmissionYearLogin(token).toString().substr(2, 2));
	} else{
		admissionYear = parseInt(getYear($('#admission_year_value').val()).toString().substr(0, 2));
	}

	var date = new Date();
	var month = parseInt(date.getMonth().toString()) + 1;
	var fullYear = date.getFullYear().toString();
	var year = parseInt(fullYear.substr(2, 2));
	//alert("month current = " + month + ", year = " + year + ", admission year = " + admissionYear);

	var distYear = year - admissionYear;

	if(distYear < 0) {
		displayTable(1);
	} else if(distYear == 0) {
		if(month > 10) {
			displayTable(2);
		} else {
			displayTable(1);
		}
	} else if(distYear > 6) {
		return;
	} else if(distYear <= 6 && distYear > 0) {			// 0 to 6 years
		// Jan - Feb: can't plan for past sem 1, but can plan for sem 2.
		// March - Sep: can't plan for sem 2, but can next sem 1.
		// Oct - Dec: can't plan for sem 1, but can next sem 2.
		if(month >= 1 && month <= 2) {
			nextSem = 1 + 2 * distYear - 1;
			displayTable(nextSem);
		}
		else if(month >=3 && month <= 9) {
			nextSem = 1 + 2 * distYear;
			displayTable(nextSem);
		} else if(month >= 10 && month <= 12) {
			nextSem = 1 + 2 * distYear + 1;
			displayTable(nextSem);
		}
	}
}

function displayTable(startSem) {
	angular.module('core', []);
	var curId;

	for(var i = 0; startSem <= 12; i++) {
		if(i % 4 == 0) {
			$("#plan-mod-main-tbl").append('<div id="plan-mod-wrapper-' + i +'" class="row"></div>');
			curId = "#plan-mod-wrapper-" + i;
		}

		var tempt = $('<div class="wrapper-div"><div class="col s12 semester-div waves-effect waves-light" onClick="semPlanner()">Semester ' 
			+ startSem +'</div><div class="col s12 drag-n-drop-div"><div ui-sortable="sortableOptions" class="semester" ng-model="semester[' 
			+ (i % 4) + ']"><div ng-repeat="mod in semester['
			+ (i % 4) + ']" class="row no-margin"><div class="drag-drop-item"><span class="bold">{{mod.code}}</span><br>{{mod.title}}<br>{{mod.mc}} MC</div></div></div></div><div class="col s12 mc-div">{{plannedMC[' 
			+ (i % 4) + ']}}</div></div>');

		$(curId).append(tempt);
		startSem++;


		angular.element(curId).injector().invoke(function($compile) {
	        var scope = angular.element(tempt).scope();
	        $compile(tempt)(scope);
    	});
	}
}