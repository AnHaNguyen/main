<?php
require_once("library.php");

function verifyPRCEG($PRmod, $modulesMC, $prReq , $or, $specialMCs){
	$major = "CEG";
	$elective = getElectiveMod($major);
	$breadth = $elective["breadth"];
	$depth = $elective["depth"];

	//$prefix = array("CS");
	
	$count = array();
	for ($i = 0; $i < count($PRmod); $i++){
		$count[$PRmod[$i][0]] = 0;			//use a count to count how many times a mod is used to fulfill req
	}

	
	$breadth_list = array();
	$depth_list = array();
	$k =0;
	$m = 0;
	for ($i = 0; $i < count($PRmod);$i++){

		if (array_key_exists($PRmod[$i][0], $breadth)){		//mod names
			$breadth_list[$k] = $PRmod[$i][0];			
			$k++;
		}
		if (array_key_exists($PRmod[$i][0], $depth)){		//mod names
			$depth_list[$m] = $PRmod[$i][0];			
			$m++;
		}
	}	

	for ($i = 0; $i < count($PRmod); $i++){
		$modName = $PRmod[$i][0];
		$minus = $modulesMC[$modName];			//MCs

		if (isInList($modName, $prReq)){		//handle Mods in PR
			$prReq[$modName] -= $minus;
			$count[$modName]++;
		} 
		
	}

	if (array_key_exists("Elective", $prReq)){
		if (array_key_exists("ElectiveDepth", $prReq)){
			for ($i = 0; $i < count($depth_list); $i++){
				if ($prReq["ElectiveDepth"] > 0){
					$prReq["ElectiveDepth"] -= $modulesMC[$depth_list[$i]];
				}
			
				if ($prReq["Elective"] > 0){
					$prReq["Elective"] -=  $modulesMC[$depth_list[$i]];
				}
				
			}
		}
		for ($i = 0; $i < count($breadth_list); $i++){
			if ($prReq["Elective"] > 0){
				$prReq["Elective"] -=  $modulesMC[$breadth_list[$i]];
			}
		}
	}
	$prReq["Elective"] = max($prReq["Elective"], $prReq["ElectiveDepth"]);

	//handle the or cases
	$left = array();
	for ($i = 0; $i < count($or); $i++){
		$case = $or[$i];
		$min = 120 ;
		$minMods = array();
		for ($j = 0; $j < count($case); $j++){
			$satisfyMods = hasCompleteCEG($case[$j], $PRmod, $count, $modulesMC);			//[0] = number of MCs not cleared, [1][2] ... list of mods used to clear 
			
			if ($satisfyMods[0] < $min){
				$min = $satisfyMods[0];
				$minMods = array_slice($satisfyMods,0,count($satisfyMods)-1);
			}
		}
		

		for ($j = 0; $j < count($minMods); $j++){
			$count[$minMods[$j]]++;
		}
		$left[$i] = $min;
	}
	

	$keys = array_keys($prReq);
	$PRsum = $specialMCs;
	for ($i = 0; $i < count($prReq); $i++){
		if ($keys[$i] != "ElectiveDepth"){
			$PRsum += $prReq[$keys[$i]];
		}
	}
	for ($i = 0; $i < count($left); $i++){
		$PRsum += $left[$i];
	}
	return $PRsum;
}

function hasCompleteCEG($group, $PRmod, $count, $modulesMC){
	$total = $group[1];
	$satisfyMods =array();
	$index = 1;
	$mods = preg_split("/\,/", $group[0]);
	for ($i = 0; $i < count($mods); $i++){
		$modName = $mods[$i];
		if (array_key_exists($modName,$modulesMC)){
			$total -= $modulesMC[$modName];
			$satisfyMods[$index] = $modName;
			$index++;
		} 
	}
	$satisfyMods[0] = $total;
	return $satisfyMods;
}
?>