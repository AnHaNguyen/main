<?php
require_once("library.php");

function verifyPRIS($PRmod, $modulesMC, $prReq, $or, $specialMCs){
	$major = "IS";
	$elective_mod = getElectiveMod($major);

	$prefix = array("CS","IS");
	
	$k = 0;
	for ($i = 0; $i < count($PRmod); $i++){		//popularize elective mods
		if (array_key_exists($PRmod[$i][0], $elective_mod)){		//mod names
			$elective_list[$k] = $PRmod[$i][0];			
			$k++;
		}
	}

	
	for ($i = 0; $i < count($PRmod); $i++){
		$modName = $PRmod[$i][0];
		$minus = $modulesMC[$modName];			//MCs
		

		$key = isInList($modName, $prReq);
		if ($key != ""){        //handle Mods in PR
			$prReq[$key] -= $minus;
			$count[$modName]++;
		} 
	}

	$usedList = array();

	if (array_key_exists("Elective", $prReq)){			//req has elective mod
		for ($j = 0; $j < count($elective_list);$j++){
			$modName = $elective_list[$j];
			if (isLev4($modName) && array_key_exists("Elective4", $prReq) && $prReq["Elective4"] > 0){		//handle elective lev 4 req
				$prReq["Elective4"] -= $modulesMC[$modName];
				$prReq["Elective"] -= $modulesMC[$modName];
				array_push($usedList, $modName);
			}
		}
		for ($j = 0; $j < count($elective_list);$j++){
			$modName = $elective_list[$j];
			if (!in_array($modName, $usedList) && $prReq["Elective"] > $prReq["Elective4"]){
				$prReq["Elective"] -= $modulesMC[$modName];
				array_push($usedList, $modName);
			}
		}
	}


	//handle the or cases
	$left = array();
	for ($i = 0; $i < count($or); $i++){
		$case = $or[$i];
		$min = 120 ;
		for ($j = 0; $j < count($case); $j++){
			$satisfyMods = hasCompleteIS($case[$j], $PRmod, $modulesMC, $usedList, $elective_list);			//[0] = number of MCs not cleared, [1][2] ... list of mods used to clear 
			
			if ($satisfyMods < $min){
				$min = $satisfyMods;
			}
		}
		
		$left[$i] = $min;
	}
	
	//handle overlapping mods

	$keys = array_keys($prReq);
	$PRsum = $specialMCs;
	for ($i = 0; $i < count($prReq); $i++){
		if ($keys[$i] != "Elective4"){
			$PRsum += $prReq[$keys[$i]];
		}
	}
	for ($i = 0; $i < count($left); $i++){
		$PRsum += $left[$i];
	}
	return $PRsum;
}

function hasCompleteIS($group, $PRmod, $modulesMC, $usedList, $elective_list){
	$mcArr = preg_split("/\;/", $group[1]);
	$total = intval($mcArr[0]);
	$mods = preg_split("/\,/", $group[0]);

	for ($i = 0; $i < count($mods); $i++){
		$modName = $mods[$i];
		if (array_key_exists($modName,$modulesMC)){
			$total -= $modulesMC[$modName];
		} else if ($modName == "Lev4"){			//LEv 4 from Elective List
			$total = handleElectiveIS($elective_list, $usedList, $modulesMC, $total, $total);
		} else if (strpos($modName,"Elective")){
			if (strpos($modName, "Elective4") !== false){
				$Lev4MC = intval($mcArr[1]);
				$total = handleElectiveIS($elective_list, $usedList, $modulesMC,$total, $Lev4MC);
			} else{
				$total = handleElectiveIS($elective_list, $usedList, $modulesMC,$total, 0);
			}
		}
	}		
	return $total;
}

function handleElectiveIS($elective_list, $usedList,$modulesMC, $totalMC, $Lev4MC){
	if($Lev4MC > 0){
		$currentList = array();
		for ($i = 0; $i < count($elective_list); $i++){
			$modName = $elective_list[$i];
			if (!in_array($modName, $usedList) && isLev4($modName) && $Lev4MC > 0){
				$totalMC -= $modulesMC[$modName];
				$Lev4MC -= $modulesMC[$modName];
				array_push($currentList, $modName);
			}
		}

		for ($j = 0; $j < count($elective_list);$j++){
			$modName = $elective_list[$j];
			if (!in_array($modName, $currentList) && !in_array($modName, $usedList) && $totalMC > $Lev4MC){
				$totalMC -= $modulesMC[$modName];
			}
		}

	} else{
		for ($i = 0; $i < count($elective_list); $i++){
			if (!in_array($modName, $usedList) && $totalMC > 0){
				$totalMC -= $modulesMC[$modName];
			}
		}
	}
	return $totalMC;
}
?>
