<?php
require("data.php");

if (isset($_REQUEST["adm_year"])){
	$adm_year = $_REQUEST["adm_year"];
}else{
	$adm_year = "1314";	
}

if (isset($_REQUEST["major"])){
	$major = $_REQUEST["major"];
} else{
	$major = "CS";
}


$file = "/var/www/html/main/req/".$major."/".$adm_year.".json";
$data = file_get_contents($file);

if (isset($_REQUEST["cmd"])){
	if ($_REQUEST["cmd"] == "getreq"){
		echo $data;
	} else if ($_REQUEST["cmd"] == "verify"){
		$focus_area = $_REQUEST["focus_area"];
		//$focus_area = "SE";		//fake
		//$focus_mod = getFocusMod($focus_area);
		$focus_mod = array("CS2103"=>4,"CS3213"=>4,"CS3219"=>4,"CS4211"=>4,"CS4218"=>4);

		//$sci_mod = getScienceMod("$major");
		$sci_mod = array("CM1121"=>4,"CM1131"=>4,"CS1417"=>4,"LSM1301"=>4,"LSM1302"=>4,"PC1141"=>4,"PC1142"=>4,"PC1143"=>4,"PC1144"=>4,"PC1221"=>4,"PC1222"=>4,"PC1432"=>4,"MA2213"=>4,"MA2214"=>4,"CM1101"=>4,"CM1111"=>4,"CM1161"=>4,"CM1191"=>4,"CM1401"=>4,"CM1402"=>4,"CM1501"=>4,"CM1502"=>4,"LSM1303"=>4,"PC1421"=>4,"PC1431"=>4,"PC1433"=>4,"MA1104"=>4,"MA2101"=>4,"MA2108"=>4,"MA2501"=>4,"ST2132"=>4,"ST2137"=>4);

		$modules = json_decode($_REQUEST["modules"],true);		//type = nil for exempted mods
		//$modules = array(array("CS1010","PR", "4"),array("CS1231","PR", "4"),array("GEK1517","ULR","4"),array("MA1101R","PR","4"),array("MA1521","PR","4"),array("CS1020","PR","4"),array("ES1102","UE","0"),array("MA2101","PR","4"),array("MA2213","PR","4"),array("SSA1202","ULR","4"),array("ST2334","PR","4"),array("CS2010","PR","4"),array("CS2100","PR","4"),array("GEM2900","ULR","4"),array("LAC1201","ULR","4"),array("MA2214","PR","4"),array("CS2101","PR","4"),array("CS2102","UE","4"),array("CS2103","PR","4"),array("CS2105","PR","4"),array("IS1103","PR","4"),array("CS2106","PR","4"),array("CS3201","PR","4"),array("CS3202","PR","4"),array("CS3230","PR","4"),array("CS4211","PR","4"),array("CS3240","UE","4"),array("CS3241","UE","4"),array("CS3223","UE","4"),array("CS3243","UE","4"),array("CS3226","UE","4"),array("GEK1544","ULR","4"),array("PC1221","nil","4"),array("MA1301","nil","4"));
		$req = json_decode($data,true);
		verifyReq($modules,$req, $focus_mod, $sci_mod, $major, $adm_year);
	}
} else{
	$focus_area = "SE";
	$focus_mod = array("CS2103"=>4,"CS3213"=>4,"CS3219"=>4,"CS4211"=>4,"CS4218"=>4);
	$sci_mod = array("CM1121"=>4,"CM1131"=>4,"CS1417"=>4,"LSM1301"=>4,"LSM1302"=>4,"PC1141"=>4,"PC1142"=>4,"PC1143"=>4,"PC1144"=>4,"PC1221"=>4,"PC1222"=>4,"PC1432"=>4,"MA2213"=>4,"MA2214"=>4,"CM1101"=>4,"CM1111"=>4,"CM1161"=>4,"CM1191"=>4,"CM1401"=>4,"CM1402"=>4,"CM1501"=>4,"CM1502"=>4,"LSM1303"=>4,"PC1421"=>4,"PC1431"=>4,"PC1433"=>4,"MA1104"=>4,"MA2101"=>4,"MA2108"=>4,"MA2501"=>4,"ST2132"=>4,"ST2137"=>4);
	$modules = array(array("CS1010","PR", "4"),array("CS1231","PR", "4"),array("GEK1517","ULR","4"),array("MA1101R","PR","4"),array("MA1521","PR","4"),array("CS1020","PR","4"),array("ES1102","UE","0"),array("MA2101","PR","4"),array("MA2213","PR","4"),array("SSA1202","ULR","4"),array("ST2334","PR","4"),array("CS2010","PR","4"),array("CS2100","PR","4"),array("GEM2900","ULR","4"),array("LAC1201","ULR","4"),array("MA2214","PR","4"),array("CS2101","PR","4"),array("CS2102","UE","4"),array("CS2103","PR","4"),array("CS2105","PR","4"),array("IS1103","PR","4"),array("CS2106","PR","4"),array("CS3201","PR","4"),array("CS3202","PR","4"),array("CS3230","PR","4"),array("CS4211","PR","4"),array("CS3240","UE","4"),array("CS3241","UE","4"),array("CS3223","UE","4"),array("CS3243","UE","4"),array("CS3226","UE","4"),array("GEK1544","ULR","4"),array("PC1221","nil","4"),array("MA1301","nil","4"));

	$req = json_decode($data,true);
	verifyReq($modules,$req, $focus_mod, $sci_mod, $major, $adm_year);
}






/*$adm_year = 3;
$and1 = array("ULR"=>20);
$and2 = array("PR"=>array("CS1010"=>4, "CS1020"=>4,"CS2010"=>4,"CS1231"=>4,"CS2100"=>4,"CS2103"=>4,"CS2105"=>4,"CS2106"=>4,"CS3230"=>4,"Focus"=>12,"Focus4"=>4,"Lev4"=>12,"IS1103"=>4,"CS2101"=>4,"MA1301"=>4,"ST2334"=>4,"MA1521"=>4,"MA1101R"=>4,"Scie"=>12));
$and3 = array("UE"=>20);
$and = array_merge($and1,$and2,$and3);
$group1 = array(array("CS3201,CS3202","8"),array("CS3216,CS3217","8"),array("CS3281,CS3282","8"),array("CS3283,CS3284","8"));
$group3 = array(array("CP4101","12"),array("Lev4","12"),array("ATAP","12"));			//not handle yet case Lev4 in or
$group2 = array(array("PC1221","4"),array("PC1222","4"));
$or = array($group1, $group2, $group3);
$req = array("and"=>$and,"or"=>$or);*/




function verifyReq($modules, $req, $focus_mod, $sci_mod, $major, $adm_year){
	$Lev4 = $major."4";
	$FocusLev4 = $major."4";
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

	//handle exempted modules => convert to UE
	for ($i = 0; $i < count($modules); $i++){
		if ($modules[$i][1] == "nil"){			//index 1 for mod type
			$and["UE"] += intval($modules[$i][2]);		//index 2 for MCs
		}
	}


	$sci_list = array();
	$focus_list = array();
	$k = 0;
	$m = 0;
	for ($i = 0; $i < count($modules);$i++){

		if (array_key_exists($modules[$i][0], $sci_mod)){		//mod names
			$sci_list[$k] = $modules[$i][0];			
			$k++;
		}
		if (array_key_exists($modules[$i][0], $focus_mod)){		//mod names
			$focus_list[$m] = $modules[$i][0];			
			$m++;
		}
	}

	if ($adm_year < "1516"){
		$ulrReq = array("GEMA"=>4,"GEMB"=>4,"SS"=>4, "Breath"=>8);
	}else{
		$ulrReq = array("GEH"=>4, "GEQ"=>4,"GER"=> 4, "GES"=>4,"GET"=>4);
	}

	$GEMC = 0;																		///check ULR
	for ($i = 0; $i < count($modules); $i++){
		if ($modules[$i][1] == "ULR"){
			$modName = $modules[$i][0];
			$code = substr($modName, 0, 3);
			if ($code == "GEK" || $code == "GEM"){
				$typeNum = substr($modName, 3,2);
				if ($typeNum == "15"){
					$type = "GEMA";
				} else if ($typeNum == "10"){
					$type = "GEMB";
				} else {
					$type = "GEMC";
					$GEMC += $modulesMC[$modName];
				}
			}else if (strpos($code, "SS") !== false){
				$type = "SS";
			}else if (strpos($code, "GE") !== false){
				$type = $code;
			} else{
				$type = "Breath";
			}
			if ($type != "GEMC"){
				if ($ulrReq[$type] > 0){
					$ulrReq[$type] -= $modulesMC[$modName];
				} else if (array_key_exists("Breath", $ulrReq) && $ulrReq["Breath"] > 0){
					$ulrReq["Breath"] -= $modulesMC[$modName];
				}
			}
		}
	}

	$ULR = array();
	$key = array_keys($ulrReq);
	for ($i = 0; $i < count($ulrReq); $i++){
		if ($ulrReq[$key[$i]] > 0){
			while ($GEMC > 0 && $ulrReq[$key[$i]] > 0){
				$ulrReq[$key[$i]] -= 4;				//can take 2 gem C for both A and B?
				$GEMC -= 4;
			}
		}
		if ($ulrReq[$key[$i]] > 0){
			$ULR[$key[$i]] = $ulrReq[$key[$i]];
		}
	}


	for ($i = 0; $i < count($modules); $i++){
		$minus = intval($modules[$i][2]);			//MCs
		if ($modules[$i][1] == "ULR"){			
			$and["ULR"] -= $minus;
			$count[$modules[$i][0]]++;
		} else if (array_key_exists($modules[$i][0], $and["PR"])){		//handle Mods in PR
			$and["PR"][$modules[$i][0]] -= $minus;
			$count[$modules[$i][0]]++;
		} 
		
	}

	//echo json_encode($count);
	//handle scie + focus area
	if (array_key_exists("Focus", $and["PR"])){			//req has focus mod
		for ($j = 0; $j < count($focus_list);$j++){
			$modName = $focus_list[$j];
			if ($and["PR"]["Focus"] > 0){
				$and["PR"]["Focus"] -= $modulesMC[$modName];	
				$count[$modName]++;
			}
			if (strpos($modName,$FocusLev4) !== false && array_key_exists("Focus4", $and["PR"])){		//handle focus lev 4 req
				if ($and["PR"]["Focus4"] > 0){
					$and["PR"]["Focus4"] -= $modulesMC[$modName];
				}
			}
		}
	}
	if (array_key_exists("Scie", $and["PR"])){
		for ($j = 0; $j < count($sci_list); $j++){
			$modName = $sci_list[$j];
			if ($and["PR"]["Scie"] > 0){
				$and["PR"]["Scie"] -= $modulesMC[$modName];
				$count[$modName]++;
			}
		}
	}

	if (array_key_exists("Lev4", $and["PR"])){			//handle case of MCS of lev 4 mod
		for ($i = 0; $i < count($modules); $i++){
			if (strpos($modules[$i][0], $Lev4) !== false){
				$modName = $modules[$i][0];
				if ($and["PR"]["Lev4"] >0){
					$and["PR"]["Lev4"] -= $modulesMC[$modName];
					$count[$modName]++;
				}
			}
		}
	}

	//handle multi categories mod
	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		if ($count[$modName] > 1){			//modules that can be served for multi categories will result in another add for UE
			$and["UE"] += ($count[$modName] - 1)*($modulesMC[$modName]);
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
						if ($count[$modules[$i][0]] == 0 && strpos($modules[$i][0], $Lev4) !== false){
							$group[$k] -= $modulesMC[$modules[$i][0]];
							$group[$k] .= ",".$module[$i][0];
						} 
					}
				} else if($group[$k] == "Scie"){
					for ($m = 0; $m < count($sci_list); $m++){
						if ($count[$sci_list[$m]] == 0){
							$group[$k] -= $modulesMC[$sci_list[$m]];
							$group[$k] .= ",".$sci_list[$m];
						}
					}
				}
			}
		}
		for ($j = 0; $j < count($case); $j++){
			if (intval($or[$i][$j][1]) <= 0){			//satisfy or cond
				$group = preg_split("/\,/", $case[$j][0]);
				for ($k = 0; $k < count($group); $k++){
					if ($group[$k] != "Lev4" && $group[$k] != "Scie"){
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
		$PRsum += $and["PR"][$keys[$i]];
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
	


	echo json_encode($returnArr);
}
?>
