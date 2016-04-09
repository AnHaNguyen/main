<?php
require_once("library.php");

function verifyPRBZA($PRmod, $modulesMC, $prReq, $or){
	$major = "BZA";
	$elective_mod = getElectiveMod($major);
	$ListA = $elective_mod["ListA"];
	$ListB = $elective_mod["ListB"];

	$elective_listA = array();
	$elective_listB = array();
	$k =0;
	$m = 0;
	for ($i = 0; $i < count($PRmod);$i++){

		if (array_key_exists($PRmod[$i][0], $ListA)){		//mod names
			$elective_listA[$k] = $PRmod[$i][0];			
			$k++;
		}
		else if (array_key_exists($PRmod[$i][0], $ListB)){
			$elective_listB[$m] = $PRmod[$i][0];			
			$m++;
		}
	}

	$ElectiveLev4 = array();
	$j = 0;
	for ($i = 0; $i < count($elective_listA); $i++){
		if (isLev4($elective_listA[$i])){
			$ElectiveLev4[$j] = $elective_listA[$i];
			$j++;
		}
	}
	for ($i = 0; $i < count($elective_listB); $i++){
		if (isLev4($elective_listB[$i])){
			$ElectiveLev4[$j] = $elective_listB[$i];
			$j++;
		}
	}
	
	for ($i = 0; $i < count($PRmod); $i++){
		$modName = $PRmod[$i][0];
		$minus = $modulesMC[$modName];			//MCs

		if (array_key_exists($modName, $prReq)){		//handle Mods in PR
			$prReq[$modName] -= $minus;
		} 
		
	}

	if (array_key_exists("Elective", $prReq)){			//req has elective mod
		if (array_key_exists("ListA", $prReq)){
			for ($i = 0; $i < count($elective_listA); $i++){
				$modName = $elective_listA[$i];
				if ($prReq["ListA"] > 0){
					$prReq["ListA"] -= $modulesMC[$modName];
				}
				if ($prReq["Elective"] > 0){
					$prReq["Elective"] -= $modulesMC[$modName];
				}
			}
		}
		if (array_key_exists("ListB", $prReq)){
			for ($i = 0; $i < count($elective_listB); $i++){
				$modName = $elective_listB[$i];
				if ($prReq["ListB"] > 0){
					$prReq["ListB"] -= $modulesMC[$modName];
				}
				if ($prReq["Elective"] > 0){
					$prReq["Elective"] -= $modulesMC[$modName];
				}
			}
		}
		if (array_key_exists("Elective4", $prReq)){
			for ($i = 0; $i < count($ElectiveLev4); $i++){
				$modName = $ElectiveLev4[$i];
				if ($prReq["Elective4"] > 0){
					$prReq["Elective4"] -= $modulesMC[$modName];
				}
				if ($prReq["Elective"] > 0){
					$prReq["Elective"] -= $modulesMC[$modName];
				}
			}
		}

		if (array_key_exists("ListA", $prReq) && array_key_exists("ListB", $prReq) && array_key_exists("Elective4", $prReq)){
			$remain = max($prReq["Elective4"], $prReq["ListA"] + $prReq["ListB"]);
		}
		$prReq["Elective"] = max($prReq["Elective"], $remain);
	}

	//handle the or cases
	$left = array();
	for ($i = 0; $i < count($or); $i++){
		$case = $or[$i];
		$min = 120 ;
		for ($j = 0; $j < count($case); $j++){
			$satisfyMods = hasCompleteBZA($case[$j], $PRmod, $modulesMC, $elective_listA, $elective_listB, $ElectiveLev4);			//[0] = number of MCs not cleared, [1][2] ... list of mods used to clear 
			
			if ($satisfyMods < $min){
				$min = $satisfyMods;
			}
		}
		
		$left[$i] = $min;
	}
	
	$keys = array_keys($prReq);
	$PRsum = 0;
	for ($i = 0; $i < count($prReq); $i++){
		if ($keys[$i] != "Elective4" && $keys[$i] != "ListA" && $keys[$i] != "ListB"){
			$PRsum += $prReq[$keys[$i]];
		}
	}
	for ($i = 0; $i < count($left); $i++){
		$PRsum += $left[$i];
	}
	return $PRsum;
}

function hasCompleteBZA($group, $PRmod, $modulesMC, $elective_listA, $elective_listB, $Elective4){
	$mcArr = preg_split("/\;/", $group[1]);
	$total = intval($mcArr[0]);
	$mods = preg_split("/\,/", $group[0]);

	for ($i = 0; $i < count($mods); $i++){
		$modName = $mods[$i];
		if (array_key_exists($modName,$modulesMC)){
			$total -= $modulesMC[$modName];
		} else if (strpos($modName, "Elective" !== false)){
			$total = handleElective($modName,$total,$mcArr, $modulesMC, $elective_listA, $elective_listB, $Elective4);
		}
	}
	
	return $total;
}

function handleElective($string, $total,$mcArr, $modulesMC, $elective_listA, $elective_listB, $Elective4){
	$components = preg_split("/\;/", $string);
	$elective4 = 0;
	$listA = 0;
	$listB = 0;
	for ($i = 1; $i < count($components); $i++){
		if ($components[$i] == "Elective4"){
			$elective4 = intval($mcArr[$i]);
			for ($j = 0; $j < count($Elective4); $j++){
				$modName = $Elective4[$j];
				if ($elective4 > 0){
					$elective4 -= $modulesMC[$modName];
				}
				if ($total > 0){
					$total -= $modulesMC[$modName];
				}
			}	
		} else if ($components[$i] == "ListA"){
			$listA = intval($mcArr[$i]);
			for ($j = 0; $j < count($elective_listA); $j++){
				$modName = $elective_listA[$j];
				if ($listA > 0){
					$listA -= $modulesMC[$modName];
				}
				if ($total > 0){
					$total -= $modulesMC[$modName];
				}
			}
		} else if ($components[$i] == "ListB"){
			$listB = intval($mcArr[$i]);
			for ($j = 0; $j < count($elective_listB); $j++){
				$modName = $elective_listB[$j];
				if ($listB > 0){
					$listB -= $modulesMC[$modName];
				}
				if ($total > 0){
					$total -= $modulesMC[$modName];
				}
			}
		}
	}
	$remain = max($elective4, $listA + $listB);
	return max($total, $remain);
}

?>