<?php
require("data.php");
$data = array_merge($ULR, $PR, $UE);
//echo json_encode($data);
/*		//$_GET
$adm_year = $_GET("admission");
$req = getRequirements($adm_year);
*/
$adm_year = 3;
$and1 = array("ULR"=>20);
$and2 = array("PR"=>array("CS1010"=>4, "CS1020"=>4,"CS2010"=>4,"CS1231"=>4,"CS2100"=>4,"CS2103"=>4,"CS2105"=>4,"CS2106"=>4,"CS3230"=>4,"Focus"=>12,"Focus4"=>4,"Lev4"=>12,"IS1103"=>4,"CS2101"=>4,"MA1301"=>4,"ST2334"=>4,"MA1521"=>4,"MA1101R"=>4,"Scie"=>12));
$and3 = array("UE"=>20);
$and = array_merge($and1,$and2,$and3);
$group1 = array(array("CS3201,CS3202","8"),array("CS3216,CS3217","8"),array("CS3281,CS3282","8"),array("CS3283,CS3284","8"));
$group3 = array(array("CP4101","12"),array("Lev4","12"),array("ATAP","12"));			//not handle yet case Lev4 in or
$group2 = array(array("PC1221","4"),array("PC1222","4"));
$or = array($group1, $group2, $group3);
$req = array($and, $or);
//$_GET
$focus_area = "SE";
$focus_mod = array("CS2103"=>4,"CS3213"=>4,"CS3219"=>4,"CS4211"=>4,"CS4218"=>4);
//$_GET
$modules = array(array("CS1010","PR", "4"),array("CS1231","PR", "4"),array("GEK1517","ULR","4"),array("MA1101R","PR","4"),array("MA1521","PR","4"),array("CS1020","PR","4"),array("ES1102","UE","0"),array("MA2101","PR","4"),array("MA2213","PR","4"),array("SSA1202","ULR","4"),array("ST2334","PR","4"),array("CS2010","PR","4"),array("CS2100","PR","4"),array("GEM2900","ULR","4"),array("LAC1201","ULR","4"),array("MA2214","PR","4"),array("CS2101","PR","4"),array("CS2102","UE","4"),array("CS2103","PR","4"),array("CS2105","PR","4"),array("IS1103","PR","4"),array("CS2106","PR","4"),array("CS3201","PR","4"),array("CS3202","PR","4"),array("CS3230","PR","4"),array("CS4211","PR","4"),array("CS3240","UE","4"),array("CS3241","UE","4"),array("CS3223","UE","4"),array("CS3243","UE","4"),array("CS3226","UE","4"),array("GEK1544","ULR","4"),array("PC1221","nil","4"),array("MA1301","nil","4"));
//science mods (check last by remaining mods?)
$sci_mod = array("CM1121"=>4,"CM1131"=>4,"CS1417"=>4,"LSM1301"=>4,"LSM1302"=>4,"PC1141"=>4,"PC1142"=>4,"PC1143"=>4,"PC1144"=>4,"PC1221"=>4,"PC1222"=>4,"PC1432"=>4,"MA2213"=>4,"MA2214"=>4,"CM1101"=>4,"CM1111"=>4,"CM1161"=>4,"CM1191"=>4,"CM1401"=>4,"CM1402"=>4,"CM1501"=>4,"CM1502"=>4,"LSM1303"=>4,"PC1421"=>4,"PC1431"=>4,"PC1433"=>4,"MA1104"=>4,"MA2101"=>4,"MA2108"=>4,"MA2501"=>4,"ST2132"=>4,"ST2137"=>4);
verifyReq($modules,$req, $focus_mod, $sci_mod);
/*
$sci = getScienceMod();
$focus_area = $_GET["focus_area"];
$focus_mod = getFocusMod($focus_area);
$modules = $_GET["modules"];
$modList = json_decode($modules);
*/



function verifyReq($modules, $req, $focus_mod, $sci_mod){
	$CSLev4 = "CS4";
	$CSFocusLev4 = "CS4";
	$modulesMC = array();

	for ($i = 0; $i < count($modules); $i++){
		$modName = $modules[$i][0];
		$modulesMC[$modName] = intval($modules[$i][2]);
	}

	$and;
	$or;
	for ($i = 0; $i < 2; $i++){
		$and = $req[0];
		$or = $req[1]; 
	}

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

	for ($i = 0; $i < count($modules); $i++){
		$minus = intval($modules[$i][2]);			//MCs
		if ($modules[$i][1] == "ULR"){			//handle ULR
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
			if (strpos($modName,$CSFocusLev4) !== false && array_key_exists("Focus4", $and["PR"])){		//handle focus lev 4 req
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
			if (strpos($modules[$i][0], $CSLev4) !== false){
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
				}
			}
		}
		for ($j = 0; $j < count($case); $j++){
			if (intval($or[$i][$j][1]) <= 0){			//satisfy or cond
				$group = preg_split("/\,/", $case[$j][0]);
				for ($k = 0; $k < count($group); $k++){
					$count[$group[$k]]++;
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
	//print result
	$returnAnd = $and;


	echo json_encode($returnAnd);
}
?>
