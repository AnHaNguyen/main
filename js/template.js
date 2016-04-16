majorConvert = {"Computer Science":"CS","Information System":"IS","Business Analytics": "BZA", "Computer Engineering":"CEG"};
var templateCS = ["CS1010", "CS1020", "CS2010", "CS1231", "CS2100", "CS2103T", "CS2105", "CS2106","CS3230", 
					"CP3880", "IS1103", "CS2101", "MA1301", "MA1521", "MA1101R", "ST2334", "PC1221", "CS3201", "CS3202"];
var templateIS = ["CS1010","CS1020", "CS1231", 	"IS1103", "IS1105", "CS2100", "CS2102", "CS2105", "IS2101", "IS2102", "IS2103",
				"IS2104", "IS3101","IS3102","IS4100","ACC1002X","MA1301","MA1312","ST2334"];
var templateCEG = ["CG1101", "CG1108","CG2023", "CG2271","CG3207","CS1010", "CS1020","CS1231","CS2103T","EE2020","EE2021","EE2024",
				"EE3204","ES1531","EG2401","HR2002","MA1505","MA1506","PC1432","ST2334","CG3002","EE3031"];
var templateBZA = ["ACC1002X","BT1101","MKT1003X","EC1301","MA1311","MA1521","CS1010","CS1020","IS1103","IS1105","BT2101","BT2102",
				"IE2110","IS2101","ST2334","BT3101","BT3102","BT3103","ST3131"];
$("#template").on("click", function(){
	var major = majorConvert[$('#major_value').val()]; 
});

function getTemplatesMods(major){
	switch(major){
		case 'CS':
			var modules = templateCS;
			break;
		case 'IS':
			var modules = templateIS;
			break;
		case 'BZA':
			var modules = templateBZA;
			break;
		case 'CEG':
			var modules = templateCEG;
			break;
		default:
			break;
	}
	return modules;


}
