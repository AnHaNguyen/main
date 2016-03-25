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
$and2 = array("PR","120","CS1010","4", "CS1020","4","CS2010","4","CS1231","4","CS2100","4","CS2103","4","CS2105","4","CS2106","4","CS3230","4","Focus","12","FocusLev4","4","Lev4","12","IS1103","4","CS2101","4","ES2660","4","MA1301","4","MA1521","4","MA1101R","4","Sci","4", "ATAP","12");
$and3 = array("UE","20");
$and = array($and1,$and2,$and3);
$group1 = array("CS3201","CS3202","8","CS3216","CS3217","8","CS3281","CS3282","8","CS3283","CS3284","8");  //st2334 + sci or st2131 + st2132
$group2 = array("ST2334","Sci","8","ST2131","ST2132","8");
$group3 = array("PC1221","4","PC1222","4");
$or = array($group1, $group2, $group3);
$req = array($and, $or);

//$_GET
$focus_area = "SE";
$focus_mod = array("CS2103","CS3213","CS3219","CS4211","CS4218");

//$_GET
$modules = array("CS1010","PR","CS1231","PR","GEK1517","ULR","MA1101R","PR","MA1521","PR","CS1020","PR","ES1102","nil","MA2101","PR","MA2213","PR","SSA1202","ULR","ST2334","PR","CS2010","PR","CS2100","PR","GEM2900","ULR","LAC1201","ULR","MA2214","PR","CS2101","PR","CS2102","PR","CS2103","PR","CS2105","PR","IS1103","PR","CS2106","PR","CS3201","PR","CS3202","PR","CS3230","PR","CS4211","PR","CS3240","PR","CS3241","PR","CS3223","PR","CS3243","PR","CS3226","PR","GEK1544","ULR","PC1221","nil","MA1301","nil");

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
	$ULR;
	$PR;
	$UE;
	$and;
	$or;
	for ($i = 0; $i < 2; $i++){
		$and = $req[0];
		$or = $req[1]; 
	}

	//verify or
	for ($i = 0; $i < count($modules)-1;){
		for ($j = 0; $j < count($or); $j++){
			for ($k = 0; $k < count($or[$j]); $k++){
				if ($or[$j][$k] == $modules[$i]){
					$mod = $or[$j][$k];
					if (intval($or[$j][$k+1]) != 0){
						$newval = intval($or[$j][$k+1]) - 4;
						$or[$j][$k+1] = strval($newval);
					}else{
						$newval = intval($or[$j][$k+2]) - 4;
						$or[$j][$k+2] = strval($newval);
					}
				}
			}
		}				
		$i = $i+2;
	}

	$sat = array();

	for ($i = 0; $i < count($or); $i++){
		$sat[$i] = false;
		for ($j = 0; $j < count($or[$i]); $j++){
			if ($or[$i][$j] == "0"){
				$sat[$i] = true;
			}
		}
	}
	$returnStr = "";
	for ($i = 0; $i < count($or); $i++){
		if (!$sat[$i]){
			$returnStr = $returnString + $or[$i][0] + " "; 
		}
	}
	echo $returnStr;
}

?>
