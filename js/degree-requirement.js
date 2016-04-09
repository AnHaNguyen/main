function updateDegReq() {
	var longForm = {"ULR": "University Level Requirements", "PR": "Program Requirements", "UE": "Unrestricted Electives"};

	var jsonFile = "req/" + $('#major').val() + '/' + $('#admission_year').val() + ".json";
	console.log(jsonFile);
	$.getJSON(jsonFile, function(jsonContent){
		var newHtml =	`<div class="page-title row no-margin"> \
							<div class="col s6 large-text">Degree Requirement</div> \
						</div> \

						<div class="container-customize"> \
							<div class="main-table col s12"> \
								<div class="row mod-type-table"> \
									<div class="col s12 content-row">  \
										<div class="col s10 header item"> Requirement Type</div> \ 
										<div class="col s2 header item"> MC </div> \						
									</div>
								</div>`;

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

					moduleTemplate = `<div class="col s2 item">{{moduleCode}} </div> \
							<div class="col s8 item"> {{name}} </div> \
							<div class="col s2 item"> {{MC}} </div>`;

					newHtml += moduleTemplate.replace("{{moduleCode}}", moduleCode).replace("{{MC}}", MC);

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

				newHtml += optionTemplate.replace("{{option}}", option[0]).replace("{{MC}}", option[1]);
				newHtml += `</div>`;

			})

			newHtml +=`</div>`;
		});

		newHtml += "</div></div>";
		$("#deg-req-div").html(newHtml);
	});
}
