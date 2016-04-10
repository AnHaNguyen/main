function updateDegReq() {
	orReplacement = {"Lev4": "3 level-4 modules"};

	descriptionOf ={"GEMA": "GEM Module(s) in Mathematics and Science", 
					"GEMB": "GEM Module(s) in Social Science", 
					"SS": "Singapore Studies Module(s)", 
					"Breadth": "Breadth Module(s)", 
					"GEH": "GEM Module(s) in Human Cultures", 
					"GEQ": "GEM Module(s) in Asking Questions", 
					"GER": "GEM Module(s) in Quantitive Reasoning", 
					"GES": "GEM Module(s) in Singapore Studies", 
					"GET": "GEM Module(s) in Thinking and Expression"
				};	

	longForm = {"ULR": "University Level Requirements", "PR": "Program Requirements", "UE": "Unrestricted Electives",
				"Lev4": "level-4 module(s)", 
				"Focus": "Focus-area module(s)", 
				"Focus4": "Level-4 module(s) in focus area", 
				"Scie": "Science module(s)",
				"Elective": "Elective Module(s)",
				"ElectiveDepth": "Technical Elective Depth Module(s)",
				"Elective4": "Elective Module(s) of Level 4",
				"ListA": "Elective Module(s) in List A",
				"ListB": "Elective Module(s) in List B"
			};

	getIdOf = {"ULR": "ulr-deg-req", "PR": "prog-req-deg-req", "UE": "ue-deg-req"};
	hoverType = {"ULR": "ulr", "PR": "pr", "UE": "ue", "OR": "pr"};

	if (isIVLELogin()){
		major = getMajorLogin();
		year = getAdmissionYearLogin();
	} else{
		major = $('#major').val(), year = $('#admission_year').val();
	}
	
	//alert(major + " " + year);
	jsonFile = "req/" + major + '/' + year + ".json";
	moduleJsonFile = "data/simplified.json";

	console.log(jsonFile);
	$.getJSON(jsonFile, function(jsonContent){
	$.getJSON(moduleJsonFile, function(moduleTable) {
		console.log(moduleTable);
		newHtml = `<div class="page-title row no-margin"> \
							<div class="col s12 large-text">Degree Requirement for {{major}} degree in year {{year}}</div> \
						</div> \

						<div class="container-customize"> \
							<div class="main-table col s12"> \
								<div class="row mod-type-table"> \
									<div class="col s12 content-row">  \
										<div class="col s10 header item"> Requirement Type</div> \ 
										<div class="col s2 header item"> MC </div> \						
									</div>
								</div>`.replace("{{major}}", major).replace("{{year}}", year);

		console.log(jsonContent);

		$.each(jsonContent.and, function (reqType, details) {
			//put or-table before UE

			if (reqType=="UE") {
				$.each(jsonContent.or, function(index, orTable) {
					newHtml += `<div class="row mod-type-table">`;

					newHtml += `<div class="col s12 content-row"> \
									<div class="col s12 header item"> Choose one of the following options </div> \
								</div>`;

					$.each(orTable, function(index, option) {
						newHtml += `<div class="col s12 content-row {{hoverType}}-hover"> `.replace("{{hoverType}}", hoverType["OR"]);
						optionTemplate = `<div class="col s10 item">{{option}} </div> \
											<div class="col s2 item">{{MC}} </div> `;

						function orParse(codeList) {
							if (orReplacement[codeList]!=undefined) return orReplacement[codeList];
							splitCode = codeList.split(',');

							description = codeList.replace(",", " and ");

							for (i=0; i<splitCode.length; i++) {
								moduleCode = splitCode[i];
								title = "";
								if (moduleTable[moduleCode]!=undefined) title = moduleTable[moduleCode].ModuleTitle;
								description = description.replace(moduleCode, moduleCode + " " + title);
							}
							
							return description;
						}

						newHtml += optionTemplate.replace("{{option}}", orParse(option[0])).replace("{{MC}}", option[1]);
						newHtml += `</div>`;
					})

					newHtml +=`</div>`;
				});				
			}


			newHtml += '<div id="{{id}}" class="row mod-type-table">'.replace("{{id}}", getIdOf[reqType]);

			template = `<div class="col s12 content-row"> \
					<div class="col s10 header item">{{reqType}}</div> \ 
					<div class="col s2 header item">{{totalMC}}</div> \
					</div>`;

			newHtml += template.replace("{{reqType}}", longForm[reqType]).replace("{{totalMC}}", details.MC);

			if (details.mod!="undefined") {
				$.each(details.mod, function(moduleCode, MC) {
					newHtml += `<div class="col s12 content-row {{hover-type}}-hover"> `.replace("{{hover-type}}", hoverType[reqType]);

					if (longForm[moduleCode]==undefined) {
						moduleTemplate = `<div class="col s2 item">{{moduleCode}} </div> \
								<div class="col s8 item"> {{title}} </div> \
								<div class="col s2 item"> {{MC}} </div>`;

						//console.log(moduleCode);

						if (moduleTable[moduleCode]!=undefined) moduleTemplate = moduleTemplate.replace("{{title}}", moduleTable[moduleCode].ModuleTitle);
						else {
							if (descriptionOf[moduleCode]!=undefined) moduleTemplate = moduleTemplate.replace("{{title}}", MC/4 + " " + descriptionOf[moduleCode]);
							moduleTemplate = moduleTemplate.replace("{{title}}", "");
						}
						newHtml += moduleTemplate.replace("{{moduleCode}}", moduleCode).replace("{{MC}}", MC);
					} else {
						moduleTemplate = `<div class="col s10 item">{{description}} </div> \
											<div class="col s2 item"> {{MC}} </div>`;

						newHtml += moduleTemplate.replace("{{description}}", MC/4 + " " + longForm[moduleCode]).replace("{{MC}}", MC);
					}


					newHtml += `</div>`;					
				});
			}

			newHtml += `</div>`;
		}); 

		newHtml += "</div></div>";
		$("#deg-req-div").html(newHtml);
	});
	});
}
