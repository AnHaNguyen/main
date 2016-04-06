<?php
require_once("verifyCS.php");
require_once("verifyCEG.php");
require_once("library.php");

if (isset($_REQUEST["adm_year"])){
	$adm_year = $_REQUEST["adm_year"];
}else{
	$adm_year = "1011";	
}

if (isset($_REQUEST["major"])){
	$major = $_REQUEST["major"];
} else{
	$major = "CEG";
}


$file = "/var/www/html/main/req/".$major."/".$adm_year.".json";
$data = file_get_contents($file);

if (isset($_REQUEST["cmd"])){
	if ($_REQUEST["cmd"] == "getreq"){
		echo $data;
	} else if ($_REQUEST["cmd"] == "verify"){
		$modules = json_decode($_REQUEST["modules"],true);		//type = nil for exempted mods
		$req = json_decode($data,true);

		switch ($major) {
			case 'CS':
				verifyReqCS($modules, $adm_year, $req);
				break;
			case 'IS':
				verifyReqIS($modules, $adm_year, $req);
				break;
			case 'CEG':
				verifyReqCEG($modules, $adm_year, $req);
				break;
			case 'BZA':
				verifyReqBZA($modules, $adm_year, $req);	
				break;
			default:
				
				break;
		}
		
	}
} else{
	$modulesIS = array(array("CS1010","PR", "4"),array("CS1231","PR", "4"),array("GEK1517","ULR","4"),array("MA1101R","PR","4"),array("MA1521","PR","4"),array("CS1020","PR","4"),array("ES1102","UE","0"),array("MA2101","PR","4"),array("MA2213","PR","4"),array("SSA1202","ULR","4"),array("ST2334","PR","4"),array("CS2010","PR","4"),array("CS2100","PR","4"),array("GEM2900","ULR","4"),array("LAC1201","ULR","4"),array("MA2214","PR","4"),array("CS2101","PR","4"),array("CS2102","PR","4"),array("CS2103","PR","4"),array("CS2105","PR","4"),array("IS1103","PR","4"),array("CS2106","PR","4"),array("CS3201","PR","4"),array("CS3202","PR","4"),array("CS3230","PR","4"),array("CS4211","PR","4"),array("CS3240","PR","4"),array("CS3241","UE","4"),array("CS3223","UE","4"),array("CS3243","UE","4"),array("CS3226","UE","4"),array("GEK1544","ULR","4"),array("PC1221","nil","4"),array("MA1301","nil","4"));
	$modulesCS = array(array("CS1010","PR", "4"),array("CS1231","PR", "4"),array("GEK1517","ULR","4"),array("MA1101R","PR","4"),array("MA1521","PR","4"),array("CS1020","PR","4"),array("ES1102","UE","0"),array("MA2101","PR","4"),array("MA2213","PR","4"),array("SSA1202","ULR","4"),array("ST2334","PR","4"),array("CS2010","PR","4"),array("CS2100","PR","4"),array("GEM2900","ULR","4"),array("LAC1201","ULR","4"),array("MA2214","PR","4"),array("CS2101","PR","4"),array("CS2102","UE","4"),array("CS2103","PR","4"),array("CS2105","PR","4"),array("IS1103","PR","4"),array("CS2106","PR","4"),array("CS3201","PR","4"),array("CS3202","PR","4"),array("CS3230","PR","4"),array("CS4211","PR","4"),array("CS3240","UE","4"),array("CS3241","UE","4"),array("CS3223","UE","4"),array("CS3243","UE","4"),array("CS3226","UE","4"),array("GEK1544","ULR","4"),array("PC1221","nil","4"),array("MA1301","nil","4"));
	$modulesCEG = array(array("CS1010","PR", "4"),array("CS1231","PR", "4"),array("GEK1517","ULR","4"),array("CG1101","PR","4"),array("CG1108","PR","4"),array("CS1020","PR","4"),array("ES1102","UE","0"),array("CG2023","PR","4"),array("CG2271","PR","4"),array("SSA1202","ULR","4"),array("ST2334","PR","4"),array("CS2010","PR","4"),array("CG3207","PR","4"),array("GEM2900","ULR","4"),array("LAC1201","ULR","4"),array("EE2020","PR","4"),array("CS2101","PR","4"),array("EE2021","PR","4"),array("CS2103","PR","4"),array("CG3002","PR","6"),array("EE3031","PR","4"),array("CS2107","PR","4"),array("CS3103","PR","4"),array("CS3223","PR","4"),array("CS3240","PR","4"),array("CS3241","PR","4"),array("CS3235","PR","4"),array("CS3243","UE","4"),array("CS3226","UE","4"),array("GEK1544","ULR","4"));
	
	$req = json_decode($data,true);

	//verifyReqIS($modulesIS,$adm_year,$req);
	//verifyReqCS($modulesCS, $adm_year, $req);
	verifyReqCEG($modulesCEG, $adm_year, $req);
}


function verifyReqCS($modules, $adm_year, $req){
	$major = "CS";
	//$focus_area = $_REQUEST["focus_area"];
	$focus_area = "SE";		//fake

	$modulesMC = array();

	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		$modulesMC[$modName] = intval($modules[$i][2]);
	}

	$and = $req["and"];
	$or = $req["or"]; 
	
	$ULRmod = getModules($modules, "ULR");
	$PRmod = getModules($modules, "PR");
	$UEmod = getModules($modules, "UE");
	
	//handle exempted modules => convert to UE
	for ($i = 0; $i < count($modules); $i++){
		if ($modules[$i][1] == "nil"){			//index 1 for mod type
			$and["UE"]["MC"] += intval($modules[$i][2]);		//index 2 for MCs
		} 
	}

	$ulrReq = $and["ULR"]["mod"];
	$ULR = verifyULR($ULRmod, $modulesMC, $adm_year, $ulrReq);
	
	$prReq = $and["PR"]["mod"];
	$PR = verifyPRCS($PRmod, $modulesMC, $prReq, $focus_area, $or);

	//handle multi categories mod
/*	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		if ($count[$modName] > 1){			//modules that can be served for multi categories will result in another add for UE
			$and["UE"]["MC"] += ($count[$modName] - 1)*($modulesMC[$modName]);
		}
	}*/

	$ueReq = $and["UE"]["MC"];
	$UE = verifyUE($UEmod, $ueReq, $modulesMC);

	echo json_encode(array("ULR"=>$ULR,"PR"=>$PR,"UE"=>$UE));
}

function verifyReqCEG($modules, $adm_year, $req){
	$and = $req["and"];
	$or = $req["or"];

	$modulesMC = array();

	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		$modulesMC[$modName] = intval($modules[$i][2]);
	}
	
	$ULRmod = getModules($modules, "ULR");
	$PRmod = getModules($modules, "PR");
	$UEmod = getModules($modules, "UE");
	

	//handle exempted modules => convert to UE
	for ($i = 0; $i < count($modules); $i++){
		if ($modules[$i][1] == "nil"){			//index 1 for mod type
			$and["UE"]["MC"] += intval($modules[$i][2]);		//index 2 for MCs
		} 
	}

	$ulrReq = $and["ULR"]["mod"];
	$ULR = verifyULR($ULRmod, $modulesMC, $adm_year, $ulrReq);
	
	$prReq = $and["PR"]["mod"];
	$PR = verifyPRCEG($PRmod, $modulesMC, $prReq, $or);

	$ueReq = $and["UE"]["MC"];
	$UE = verifyUE($UEmod, $ueReq, $modulesMC);

	echo json_encode(array("ULR"=>$ULR,"PR"=>$PR,"UE"=>$UE));
}

//verify IS requirements
function verifyReqIS($modules, $adm_year, $req){
	$major = "IS";

	$elective_mod = getElectiveMod($major);
	//$elective_mod = array("CS2106"=>4,"CS2107"=>4,"CS3235"=>4,"CS3240"=>4,"IS3150"=>4,"IS3220"=>4,"IS3221"=>4,"IS3222"=>4,"IS3223"=>4,"IS3230"=>4, "IS3240"=>4, "IS3241"=>4, "IS3242"=>4, "IS3243"=>4, "IS3250"=>4, "IS3251"=>4, "IS3260"=>4, "IS3261"=>4, "CS4880"=>4, "IS4150"=>4, "IS4202"=>4, "IS4203"=>4, "IS4204"=>4, "IS4224"=>4, "IS4225"=>4,"IS4226"=>4, "IS4227"=>4, "IS4228"=>4, "IS4231"=>4, "IS4232"=>4, "IS4233"=>4, "IS4234"=>4, "IS4240"=>4, "IS4241"=>4, "IS4243"=>4, "IS4250"=>4, "IS4260"=>4); 

//	$req = array("and"=>$and,"or"=>$or);

	$Lev4 = $major."4";
	$ElectiveLev4[0] = "IS4";
	$ElectiveLev4[1] = "CS4";

	$modulesMC = array();

	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		$modulesMC[$modName] = intval($modules[$i][2]);
	}

	$and = $req["and"];
	$or = $req["or"]; 
	

	$count = array();
	for ($i = 0; $i < count($modules); $i++){
		$count[$modules[$i][0]] = 0;			//use a count to count how many times a mod is used to fulfill req
	}

	$ULRmod = array();
	//handle exempted modules => convert to UE
	for ($i = 0; $i < count($modules); $i++){
		if ($modules[$i][1] == "nil"){			//index 1 for mod type
			$and["UE"] += intval($modules[$i][2]);		//index 2 for MCs
		} else if ($modules[$i][1] == "ULR"){
			$ULRmod[$modules[$i][0]] = "ULR";
		}
	}

	$ulrCheck = verifyULR($ULRmod, $count, $modulesMC, $adm_year);
	$ULR = $ulrCheck[0];
	$count = $ulrCheck[1];

	$elective_list = array();
	$k =0;
	for ($i = 0; $i < count($modules);$i++){

		if (array_key_exists($modules[$i][0], $elective_mod)){		//mod names
			$elective_list[$k] = $modules[$i][0];			
			$k++;
		}
	}


	for ($i = 0; $i < count($modules); $i++){
		$minus = intval($modules[$i][2]);			//MCs

		if (array_key_exists($modules[$i][0], $and["PR"])){		//handle Mods in PR
			$and["PR"][$modules[$i][0]] -= $minus;
			$count[$modules[$i][0]]++;
		}
	}

	if (array_key_exists("Elective", $and["PR"])){			//handle Elective mods
		for ($j = 0; $j < count($elective_list);$j++){
			$modName = $elective_list[$j];
			if (array_key_exists("Elective4", $and["PR"]) && $and["PR"]["Elective4"] > 0){		//handle focus lev 4 req
				for ($k = 0; $k < count($ElectiveLev4); $k++){
					if (strpos($modName, $ElectiveLev4[$k]) !== false){
						$and["PR"]["Focus4"] -= $modulesMC[$modName];
					}
				}
			}
			if ($and["PR"]["Elective"] > $and["PR"]["Elective4"]){		//Elective also includes Elective lev 4 so we need to save MCs for lev 4
				$and["PR"]["Elective"] -= $modulesMC[$modName];	
				$count[$modName]++;
			}
		}
	}

	//handle the or cases
	$done = array();
	for ($i = 0; $i < count($or); $i++){
		$done[$i] = false;
		$case = $or[$i];
		for ($j = 0; $j < count($case); $j++){
			$group = preg_split("/\,/", $case[$j][0]);			//mod group
			for ($k = 0; $k < count($group); $k++){
				if (array_key_exists($group[$k],$modulesMC)){
					$or[$i][$j][1] = strval(intval($or[$i][$j][1] - $modulesMC[$group[$k]]));		//adjust MCS after removing modules in list
				} else if ($group[$k] == "Lev4"){
					for ($m = 0; $m < count($modules); $m++){
						if ($count[$modules[$m][0]] == 0 && strpos($modules[$m][0], $Lev4) !== false){
							$group[$k] -= $modulesMC[$modules[$m][0]];
							$group[$k] .= ",".$module[$m][0];
						} 
					}
				} 
			}
		}
		for ($j = 0; $j < count($case); $j++){
			if (intval($or[$i][$j][1]) <= 0){			//satisfy or cond
				$group = preg_split("/\,/", $case[$j][0]);
				for ($k = 0; $k < count($group); $k++){
					if ($group[$k] != "Lev4"){
						$count[$group[$k]]++;	
					}
				}
				$done[$i] = true;
				break;
			}
		}
	}

	$returnOr = array();			//find or groups that are not satisfied yet
	$index = 0;
	for ($i = 0; $i < count($or); $i++){
		if (!$done[$i]){
			$returnOr[$index] = $or[$i];
			$index++;
		}
	}

	//handle multi categories mod
	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		if ($count[$modName] > 1){			//modules that can be served for multi categories will result in another add for UE
			$and["UE"] += ($count[$modName] - 1)*($modulesMC[$modName]);
		}
	}

	//handle unused mod => UE and UE mods
	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		if ($modules[$i][1] == "UE"){
			$and["UE"] -= $modulesMC[$modName];
			$count[$modName]++;
		}
		else if ($count[$modName] == 0){
			$and["UE"] -= $modulesMC[$modName];
		}
	} 


	$keys = array_keys($and["PR"]);
	$PRsum = 0;
	for ($i = 0; $i < count($and["PR"]); $i++){
		if ($keys[$i] != "Elective4" && $and["PR"][$keys[$i]] > 0){
			$PRsum += $and["PR"][$keys[$i]];
		}
		
	}

	for ($i = 0; $i < count($returnOr); $i++){
		$PRsum += intval($returnOr[$i][0][1]);
	}

	$and["PR"]["Either"] = $returnOr;
	$and["PR"]["PR"] = $PRsum;
	$returnAnd = $and;

	$PR = $PRsum;
	$UE = $and["UE"];
	
	$returnArr  = array("ULR"=>$ULR,"PR"=>$PR,"UE"=>$UE);
	//print result
		echo json_encode($and["PR"]);

	echo json_encode($returnArr);
}

function verifyReqBZA($modules, $adm_year, $req){
	$major = "BZA";
	$and = array("ULR"=>20, "PR"=>array("ACC1002X"=>4,"MKT1003X"=>4,"EC1301"=>4,"CS1010"=>4,"CS1020"=>4,"IS1103"=>4,"IS1105"=>4,"IS2101"=>4,"BT1101"=>4,"BT2101"=>4,"DSC3215"=>4,"ST3131"=>4,"BT3101"=>4,"BT3102"=>4,"BT4101"=>12,"Elective"=>24,"Elective4"=>20,"ListA"=>8, "ListB"=>8), "UE"=>20);
	$or = [[["MA1311","4"],["MA1101R","4"]],[["MA1521","4"],["MA1102R","4"]],[["IS1112","4"],["BT2102","4"]],[["IS2110","4"],["DSC3214","4"]],[["ST2131,ST2132","8"],["ST2334,CS2010","8"]],[["BT3103","4"],["IS4240","4"]]];
	$req = array("and"=>$and,"or"=>$or);

	$ListA = array("BT4211"=>4,"BT4212"=>4,"IS3240"=>4,"IS4250"=>4,"DSC3224"=>4,"DSC4213"=>4,"IE3120"=>4,"MKT4415C"=>4);
	$ListB = array("CS3244"=>4, "BT4221"=>4,"BT4222"=>4,"IS4241"=>4,"BSP4513"=>4,"DSC3216"=>4,"IE4210"=>4,"ST4240"=>4,"ST4245"=>4);

	$and = $req["and"];
	$or = $req["or"];

	$Lev4 = $major."4";
	

	$modulesMC = array();

	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		$modulesMC[$modName] = intval($modules[$i][2]);
	}

	$and = $req["and"];
	$or = $req["or"]; 
	

	$count = array();
	for ($i = 0; $i < count($modules); $i++){
		$count[$modules[$i][0]] = 0;			//use a count to count how many times a mod is used to fulfill req
	}

	$ULRmod = array();
	//handle exempted modules => convert to UE
	for ($i = 0; $i < count($modules); $i++){
		if ($modules[$i][1] == "nil"){			//index 1 for mod type
			$and["UE"] += intval($modules[$i][2]);		//index 2 for MCs
		} else if ($modules[$i][1] == "ULR"){
			$ULRmod[$modules[$i][0]] = "ULR";
		}
	}

	$ulrCheck = verifyULR($ULRmod, $count, $modulesMC, $adm_year);
	$ULR = $ulrCheck[0];
	$count = $ulrCheck[1];


	
	for ($i = 0; $i < count($modules); $i++){
		$minus = intval($modules[$i][2]);			//MCs

		if (array_key_exists($modules[$i][0], $and["PR"])){		//handle Mods in PR
			$and["PR"][$modules[$i][0]] -= $minus;
			$count[$modules[$i][0]]++;
		}
	}

	//check elective_list(add lev4A->A= 0,lev4B->B=0 -> lev4=0 -> elective = 0)
	$elective_listA = array();
	$elective_listB = array();
	$k =0;
	$m = 0;
	for ($i = 0; $i < count($modules);$i++){

		if (array_key_exists($modules[$i][0], $ListA)){		//mod names
			$elective_listA[$k] = $modules[$i][0];			
			$k++;
		}
		else if (array_key_exists($modules[$i][0], $ListB)){
			$elective_listB[$m] = $modules[$i][0];			
			$m++;
		}
	}

	$ElectiveLev4 = array();
	$j = 0;
	for ($i = 0; $i < count($elective_listA); $i++){
		if (isLev4($elective_listA[$i])){
			$ElectiveLev4["A"][$j] = $elective_listA[$i];
			$j++;
		}
	}
	$j = 0;
	for ($i = 0; $i < count($elective_listB); $i++){
		if (isLev4($elective_listB[$i])){
			$ElectiveLev4["B"][$j] = $elective_listB[$i];
			$j++;
		}
	}



	$elective_list = array();
	for ($i = 0; $i < count($ElectiveLev4["A"]); $i++){
		if ($and["PR"]["ListA"] > 0){
			$modName = $ElectiveLev4["A"][$i];
			$and["PR"]["ListA"] -= $modulesMC[$modName];
			$and["PR"]["Elective"] -= $modulesMC[$modName];;
			if ($and["PR"]["Elective4"] > 0){
				$and["PR"]["Elective4"] -= $modulesMC[$modName];
			}
			$count[$modName]++;
		}
	}
	for ($i = 0; $i < count($ElectiveLev4["B"]); $i++){
		if ($and["PR"]["ListB"] > 0){
			$modName = $ElectiveLev4["B"][$i];
			$and["PR"]["ListB"] -= $modulesMC[$modName];
			$and["PR"]["Elective"] -= $modulesMC[$modName];;
			if ($and["PR"]["Elective4"] > 0){
				$and["PR"]["Elective4"] -= $modulesMC[$modName];
			}
			$count[$modName]++;
		}
	}

	for ($i = 0; $i < count($elective_listA); $i++){
		if ($and["PR"]["ListA"] > 0){
			$modName = $elective_listA[$i];
			$and["PR"]["ListA"] -= $modulesMC[$modName];
			$and["PR"]["Elective"] -= $modulesMC[$modName];
			$count[$modName]++;
		}
	}
	for ($i = 0; $i < count($elective_listB); $i++){
		if ($and["PR"]["ListB"] > 0){
			$modName = $elective_listB[$i];
			$and["PR"]["ListB"] -= $modulesMC[$modName];
			$and["PR"]["Elective"] -= $modulesMC[$modName];
			$count[$modName]++;
		}
	}
	if ($and["PR"]["ListA"] > 0){

	}

}

function getModules($modules, $type){
	$returnList = array();
	$j = 0;
	for ($i = 0; $i < count($modules); $i++){
		if ($type != "PR"){
			if ($modules[$i][1] == $type){
				$returnList[$j] = $modules[$i];
				$j++;
			}
		} else{
			if ($modules[$i][1] == $type || $type == "nil"){
				$returnList[$j] = $modules[$i];
				$j++;
			}
		}
	}
	return $returnList;
}

function verifyULR($ULRmod, $modulesMC, $adm_year, $ulrReq){
	if ($adm_year < "1516"){			//ulrReq = 1 gemA, 1gemB, 1 SS, 2 Breadth																	///check ULR
		$gemC = array();
		$index = 0;
		for ($i = 0; $i < count($ULRmod); $i++){
			$modName = $ULRmod[$i][0];
			$type = getULRType($modName);
			
			if ($type != "GEMC"){
				if ($ulrReq[$type] > 0){
					$ulrReq[$type] -= $modulesMC[$modName];
				} else if ($ulrReq["Breadth"] > 0){
					$ulrReq["Breadth"] -= $modulesMC[$modName];				//extra gemA or B or SS can be counted as Breadth
				} 
			} else {
				$gemC[$index] = $modName;
				$index++;
			}
		}

		for ($i = 0; $i < count($gemC); $i++){
			$modName = $gemC[$i];
						
			if ($ulrReq["GEMA"] > 0){
				$ulrReq["GEMA"] -= $modulesMC[$modName];
			} else if ($ulrReq["GEMB"] > 0){
				$ulrReq["GEMB"] -= $modulesMC[$modName];
			} else if ($ulrReq["Breadth"] > 0){
				$ulrReq["Breadth"] -= $modulesMC[$modName];
			} 
		}

	} else{				
		$ulrReq = array("GEH"=>4, "GEQ"=>4,"GER"=> 4, "GES"=>4,"GET"=>4);
		$key = array_keys($ULRmod);	
		for ($i = 0; $i < count($ULRmod); $i++){
			$modName = $ULRmod[$i][0];
			$code = getULRType($modName);
			if (array_key_exists($code, $ulrReq) && $ulrReq[$code] > 0){
				$ulrReq[$code] -= $modulesMC[$modName];
			}
		}

	}
	$sum = 0;
	$keys = array_keys($ulrReq);
	for ($i = 0; $i < count($ulrReq); $i++){
		$sum += $ulrReq[$keys[$i]];
	}
	return $sum;
}

function getULRType($modName){
	$code = substr($modName, 0, 3);
	if ($code == "GEH" || $code == "GEQ" || $code == "GER" || $code == "GES" || $code == "GET"){
		return $code;
	}
	if ($code == "GEK" || $code == "GEM"){
		$typeNum = substr($modName, 3,2);
		if ($typeNum == "15"){
			return "GEMA";
		} else if ($typeNum == "10"){
			return "GEMB";
		} else {
			return "GEMC";
		}
	}else if (strpos($code, "SS") !== false){
		return "SS";
	} else{
		return "Breadth";
	}
}

function verifyUE($UEmod, $ueReq, $modulesMC){
	for ($i = 0; $i < count($UEmod); $i++){
		$modName = $UEmod[$i][0];
		$ueReq -= $modulesMC[$modName];
	}
	return $ueReq;
}
?>