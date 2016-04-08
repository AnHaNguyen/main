<?php
require_once("library.php");

function verifyPRIS($PRmod, $modulesMC, $prReq, $or){
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

		if (array_key_exists($modName, $prReq)){		//handle Mods in PR
			$prReq[$modName] -= $minus;
		} 
		
	}

//	$usedList = array();

	if (array_key_exists("Elective", $prReq)){			//req has elective mod
		for ($j = 0; $j < count($elective_list);$j++){
			$modName = $elective_list[$j];
			if (isLev4($modName) && array_key_exists("Elective4", $prReq) && $prReq["Elective4"] > 0){		//handle elective lev 4 req
				$prReq["Elective4"] -= $modulesMC[$modName];
			}
			if ($prReq["Elective"] > 0){
				$prReq["Elective"] -= $modulesMC[$modName];	
			}
		}
		if (array_key_exists("Elective4", $prReq)){
			$prReq["Elective"] = max($prReq["Elective"], $prReq["Elective4"]);
		}
	}

	//handle the or cases
	$left = array();
	for ($i = 0; $i < count($or); $i++){
		$case = $or[$i];
		$min = 120 ;
		for ($j = 0; $j < count($case); $j++){
			$satisfyMods = hasCompleteIS($case[$j], $PRmod, $modulesMC);			//[0] = number of MCs not cleared, [1][2] ... list of mods used to clear 
			
			if ($satisfyMods < $min){
				$min = $satisfyMods;
			}
		}
		
		$left[$i] = $min;
	}
	
	//handle overlapping mods

	$keys = array_keys($prReq);
	$PRsum = 0;
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

function hasCompleteIS($group, $PRmod, $modulesMC){
	$total = $group[1];
	$mods = preg_split("/\,/", $group[0]);

	for ($i = 0; $i < count($mods); $i++){
		$modName = $mods[$i];
		if (array_key_exists($modName,$modulesMC)){
			$total -= $modulesMC[$modName];
		} 
	}
	
	return $total;
}
?>