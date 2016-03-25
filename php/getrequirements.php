<?php
require("data.php");
$data = array_merge($ULR, $PR, $UE);
//echo json_encode($data);

/*		//$_GET
$adm_year = $_GET("admission");
$req = getRequirements($adm_year);
*/

$adm_year = 3;

$and1 = array("ULR","20");
$and2 = array("PR","120","CS1010","4", "CS1020","4","CS2010","4","CS1231","4","CS2100","4","CS2103","4","CS2105","4","CS2106","4","CS3230","4","Focus","12","FocusLev4","4","Lev4","12","IS1103","4","CS2101","4","MA1301","4","MA1521","4","MA1101R","4","Scie","4","Scie","4","Scie","4", "ATAP","12");
$and3 = array("UE","20");
$and = array($and1,$and2,$and3);
$group1 = array("CS3201","CS3202","8","CS3216","CS3217","8","CS3281","CS3282","8","CS3283","CS3284","8");  //st2334 + sci or st2131 + st2132
//$group2 = array("ST2334","Scie","8","ST2131","ST2132","8");
$group3 = array("PC1221","4","PC1222","4");
$or = array($group1, $group3);
$req = array($and, $or);

//$_GET
$focus_area = "SE";
$focus_mod = array("CS2103","CS3213","CS3219","CS4211","CS4218");

//$_GET
$modules = array("CS1010","PR", "4" ,"CS1231","PR", "4","GEK1517","ULR","4","MA1101R","PR","4","MA1521","PR","4","CS1020","PR","4","ES1102","UE","0","MA2101","PR","4","MA2213","PR","4","SSA1202","ULR","4","ST2334","PR","4","CS2010","PR","4","CS2100","PR","4","GEM2900","ULR","4","LAC1201","ULR","4","MA2214","PR","4","CS2101","PR","4","CS2102","UE","4","CS2103","PR","4","CS2105","PR","4","IS1103","PR","4","CS2106","PR","4","CS3201","PR","4","CS3202","PR","4","CS3230","PR","4","CS4211","PR","4","CS3240","UE","4","CS3241","UE","4","CS3223","UE","4","CS3243","UE","4","CS3226","UE","4","GEK1544","ULR","4","PC1221","nil","4","MA1301","nil","4");

//science mods (check last by remaining mods?)
$sci_mod = array("CM1121","CM1131","CS1417","LSM1301","LSM1302","PC1141","PC1142","PC1143","PC1144","PC1221","PC1222","PC1432","MA2213","MA2214","CM1101","CM1111","CM1161","CM1191","CM1401","CM1402","CM1501","CM1502","LSM1303","PC1421","PC1431","PC1433","MA1104","MA2101","MA2108","MA2501","ST2132","ST2137");

verifyReq($modules,$req, $focus_mod, $sci_mod);
/*
$sci = getScienceMod();
$focus_area = $_GET["focus_area"];
$focus_mod = getFocusMod($focus_area);
$modules = $_GET["modules"];
$modList = json_decode($modules);
*/


function verifyReq($modules, $req, $focus_mod, $sci_mod){

	$ULR = $req[0][0][1];
	$PR = $req[0][1][1];
	$UE = $req[0][2][1];
	$and;
	$or;
	for ($i = 0; $i < 2; $i++){
		$and = $req[0];
		$or = $req[1]; 
	}

	$sci_list = array("0");
	$focus_list = array("0");
	$k = 0;
	$m = 0;
	for ($i = 0; $i < count($modules);){
		for ($j = 0; $j < count($sci_mod); $j++){
			if ($modules[$i] == $sci_mod[$j] && $modules[$i+1] == "PR"){
				$sci_list[$k] = $modules[$i];
				$k++;
			} 
		}
		for ($j = 0; $j < count($focus_mod); $j++){
			if ($modules[$i] == $focus_mod[$j] && $modules[$i+1] == "PR"){
				$focus_list[$m] = $modules[$i];
				$m++;
			}
		}
		$i += 3;
	}
	//echo json_encode($focus_list);
	//echo json_encode($or[0]);
	
	$returnOr = verifyOr($modules, $or, $sci_list);
	$sci_list = $returnOr[count($returnOr)-1];
	unset($returnOr[count($returnOr)-1]);

	$returnAnd = verifyAnd($modules, $and, $focus_list, $sci_list);

	$returnArr = array_merge($returnAnd, $returnOr);
	echo json_encode($returnArr);
}

//verify or
function verifyOr($modules, $or, $sci_list){
	for ($i = 0; $i < count($modules)-1;){
		$minus = intval($modules[$i+2]);
		for ($j = 0; $j < count($or); $j++){
			for ($k = 0; $k < count($or[$j]); $k++){
				if ($or[$j][$k] == $modules[$i]){
					$or[$j][$k] = "";
					if (strlen($or[$j][$k+1]) < 4 && strlen($or[$j][$k+1]) > 0){		//is mcs
						$newval = intval($or[$j][$k+1]) - $minus;
						$or[$j][$k+1] = strval($newval);
					}else{
						$newval = intval($or[$j][$k+2]) - $minus;
						$or[$j][$k+2] = strval($newval);
					}
				} else if ($or[$j][$k] == "Scie" && count($sci_list) > 0){				//assume that each OR statement only has 1 scie mods at most
					$or[$j][$k] = "";
					unset($sci_list[count($sci_list)-1]);
					$minus = 4;			//sci mod has mcs = 4
					if (strlen($or[$j][$k+1]) < 4 && strlen($or[$j][$k+1]) > 0){		//is mcs
						$newval = intval($or[$j][$k+1]) - $minus;
						$or[$j][$k+1] = strval($newval);
					}else{
						$newval = intval($or[$j][$k+2]) - $minus;
						$or[$j][$k+2] = strval($newval);
					}
				}
			}
		}				
		$i = $i+3;
	}

	
	$sat = array("1");

	for ($i = 0; $i < count($or); $i++){
		$sat[$i] = false;
		for ($j = 0; $j < count($or[$i]); $j++){
			if ($or[$i][$j] == "0"){
				$sat[$i] = true;
			}
		}
	}
	$returnArr = array("0");
	$k = 0;
	for ($i = 0; $i < count($or); $i++){
		if (!$sat[$i]){
			$returnArr[$k] = $or[$i]; 
			$k++;
		}
	}

	$returnArr[count($returnArr)] = $sci_list;
	return $returnArr;
}

function verifyAnd($modules, $and, $focus_list, $sci_list){
	$and1 = array("ULR","20");
	$and2 = array("PR","120","CS1010","4", "CS1020","4","CS2010","4","CS1231","4","CS2100","4","CS2103","4","CS2105","4","CS2106","4","CS3230","4","Focus","12","FocusLev4","4","Lev4","12","IS1103","4","CS2101","4","ES2660","4","MA1301","4","MA1521","4","MA1101R","4","Scie","4", "ATAP","12");
	$and3 = array("UE","20");
	$ULR;
	$PR;
	$UE;
	for ($i = 0; $i < count($and); $i++){
		if ($and[$i][0] == "ULR"){
			$ULR = $and[$i];
		} else if ($and[$i][0] == "PR"){
			$PR = $and[$i];
		} else {
			$UE = $and[$i];
		}
	}

	//preprocess sci and foc mod
	$focus_list_4 = array("0");							//assume we have focus area that requires lev 4 modules
	$k = 0;
	for ($i = 0; $i < count($focus_list); $i++){
		if (strpos($focus_list[$i], "CS4") !== false){			//assume focus area mod has name start with CS
			$focus_list_4[$k] = $focus_list[$i];
			$k++;
		}
	}

	for ($j = 2; $j < count($PR);){
		if ($PR[$j] == "Scie"){
			while (count($sci_list) > 0 && intval($PR[$j+1]) > 0){
				$newval = intval($PR[$j+1]) - 4;    	//assume sci mods has mc = 4
				$PR[$j+1] = strval($newval);				 
				unset($sci_list[count($sci_list)-1]);
			}
		} else if ($PR[$j] == "Focus" ){
			while (count($focus_list) > 0 && intval($PR[$j+1]) > 0){
				$newval = intval($PR[$j+1]) - 4;    	//assume foc area mods has mc = 4
				$PR[$j+1] = strval($newval);				 
				unset($focus_list[count($focus_list)-1]);
			}
		} else if ($PR[$j] == "FocusLev4"){
			while (count($focus_list_4) > 0 && intval($PR[$j+1]) > 0){
				$newval = intval($PR[$j+1]) - 4;    	//assume foc area mods has mc = 4
				$PR[$j+1] = strval($newval);				 
				unset($focus_list_4[count($focus_list_4)-1]);
			}
		}
		$j += 2;	
	}

	for ($i = 0; $i < count($modules);){
		if ($modules[$i+1] == "ULR"){
			$ULR[1] = strval(intval($ULR[1]) - intval($modules[$i+2]));
		} else if ($modules[$i+1] == "UE"){
			$UE[1] = strval(intval($UE[1]) - intval($modules[$i+2]));
		}else if ($modules[$i+1] == "nil"){
			$UE[1] = strval(intval($UE[1]) + intval($modules[$i+2]));			//assume that exempted mods are converted to UE
			for ($j = 2; $j < count($PR);){
				if ($modules[$i] == $PR[$j]){
					$PR[$j+1] = "0";
				}
				$j += 2;
			}
		} else {
			for ($j = 2; $j < count($PR);){
				if ($modules[$i] == $PR[$j]){			//case mod name
					$PR[$j+1] = "0";
					$PR[1] = strval(intval($PR[1]) - intval($modules[$i+2]));	
				} 
				if (strpos($modules[$i], "CS4") !== false && $PR[$j] == "Lev4"){			//assume that all lev 4 mods have to be CS
					$PR[$j+1] = strval(intval($PR[$j+1]) - intval($modules[$i+2]));
					$PR[1] = strval(intval($PR[1]) - intval($modules[$i+2]));			
				}
				$j += 2;	
			}
			
		}

		$i += 3;
	}


	$returnArr = array($ULR,$PR,$UE);			
	return $returnArr;
}
?>
