<?php
require_once("verifyCS.php");
require_once("verifyCEG.php");
require_once("verifyIS.php");
require_once("verifyBZA.php");
require_once("library.php");

if (isset($_REQUEST["adm_year"])){
	$adm_year = $_REQUEST["adm_year"];
}else{
	$adm_year = "1314";	
}

if (isset($_REQUEST["major"])){
	$major = $_REQUEST["major"];
} else{
	$major = "BZA";
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
	//verifyReqCEG($modulesCEG, $adm_year, $req);
	verifyReqBZA($modulesIS, $adm_year, $req);
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
	$PR = verifyPRIS($PRmod, $modulesMC, $prReq, $or);

	$ueReq = $and["UE"]["MC"];
	$UE = verifyUE($UEmod, $ueReq, $modulesMC);

	echo json_encode(array("ULR"=>$ULR,"PR"=>$PR,"UE"=>$UE));
}

function verifyReqBZA($modules, $adm_year, $req){
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
	$PR = verifyPRBZA($PRmod, $modulesMC, $prReq, $or);

	$ueReq = $and["UE"]["MC"];
	$UE = verifyUE($UEmod, $ueReq, $modulesMC);

	echo json_encode(array("ULR"=>$ULR,"PR"=>$PR,"UE"=>$UE));

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