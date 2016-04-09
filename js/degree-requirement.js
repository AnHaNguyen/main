var orReplacement = {"Lev4": "3 level-4 modules"};

function orParse(code) {
	if (orReplacement[code]!=undefined) return orReplacement[code];
	return code;
}

function updateDegReq() {
	var longForm = {"ULR": "University Level Requirements", "PR": "Program Requirements", "UE": "Unrestricted Electives",
					"Lev4": "3 level-4 modules", "Focus": "3 focus-area modules", "Focus4": "Level-4 module in focus area", 
					"Scie": "Science modules"};

	var major = $('#major').val(), year = $('#admission_year').val();
	var jsonFile = "req/" + major + '/' + year + ".json";

	console.log(jsonFile);
	$.getJSON(jsonFile, function(jsonContent){
		var newHtml =	`<div class="page-title row no-margin"> \
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
			newHtml += '<div class="row mod-type-table">';

			template = `<div class="col s12 content-row"> \
					<div class="col s10 header item">{{reqType}}</div> \ 
					<div class="col s2 header item">{{totalMC}}</div> \
					</div>`;

			newHtml += template.replace("{{reqType}}", longForm[reqType]).replace("{{totalMC}}", details.MC);

			if (details.mod!="undefined") {
				$.each(details.mod, function(moduleCode, MC) {
					newHtml += `<div class="col s12 content-row ulr-hover"> `;

					if (longForm[moduleCode]==undefined) {
						moduleTemplate = `<div class="col s2 item">{{moduleCode}} </div> \
								<div class="col s8 item"> {{name}} </div> \
								<div class="col s2 item"> {{MC}} </div>`;

						newHtml += moduleTemplate.replace("{{moduleCode}}", moduleCode).replace("{{MC}}", MC);								
					} else {
						moduleTemplate = `<div class="col s10 item">{{description}} </div> \
											<div class="col s2 item"> {{MC}} </div>`;

						newHtml += moduleTemplate.replace("{{description}}", longForm[moduleCode]).replace("{{MC}}", MC);
					}


					newHtml += `</div>`;					
				});
			}

			newHtml += `</div>`;
		}); 

		$.each(jsonContent.or, function(index, orTable) {
			newHtml += `<div class="row mod-type-table">`;

			newHtml += `<div class="col s12 content-row"> \
							<div class="col s12 header item"> Choose one of the following options </div> \
						</div>`;

			$.each(orTable, function(index, option) {
				newHtml += `<div class="col s12 content-row ulr-hover"> `;
				optionTemplate = `<div class="col s10 item">{{option}} </div> \
									<div class="col s2 item">{{MC}} </div> `;

				newHtml += optionTemplate.replace("{{option}}", orParse(option[0])).replace("{{MC}}", option[1]);
				newHtml += `</div>`;

			})

			newHtml +=`</div>`;
		});

		newHtml += "</div></div>";
		$("#deg-req-div").html(newHtml);
	});
}
