<?php
require_once("library.php");

function verifyPRCS($PRmod, $modulesMC, $prReq, $focus_area, $or, $BandD){
	$prefix = array("CS");
	
	$count = array();
	for ($i = 0; $i < count($PRmod); $i++){
		$count[$PRmod[$i][0]] = 0;			//use a count to count how many times a mod is used to fulfill req
	}

	$focus_mod = getFocusMod($focus_area);
	
	$sci_mod = getScienceMod();
	$sci_list = array();
	$focus_list = array();
	
	$k = 0;
	$m = 0;

	for ($i = 0; $i < count($PRmod); $i++){		//popularize science and focus area mods

		if (array_key_exists($PRmod[$i][0], $sci_mod)){		//mod names
			$sci_list[$k] = $PRmod[$i][0];			
			$k++;
		}
		if (array_key_exists($PRmod[$i][0], $focus_mod)){		//mod names
			$focus_list[$m] = $PRmod[$i][0];			
			$m++;
		}
	}

	
	for ($i = 0; $i < count($PRmod); $i++){
		$modName = $PRmod[$i][0];
		$minus = $modulesMC[$modName];			//MCs

		if (array_key_exists($modName, $prReq)){		//handle Mods in PR
			$prReq[$modName] -= $minus;
			$count[$modName]++;
		} 
		
	}

	//echo json_encode($count);
	//handle scie + focus area
	if (array_key_exists("Focus", $prReq)){			//req has focus mod
		for ($j = 0; $j < count($focus_list);$j++){
			$modName = $focus_list[$j];
			if (isLev4($modName) && array_key_exists("Focus4", $prReq) && $prReq["Focus4"] > 0){		//handle focus lev 4 req
				$prReq["Focus4"] -= $modulesMC[$modName];
				$prReq["Focus"] -= $modulesMC[$modName];
				$BandD -= $modulesMC[$modName];
				$count[$modName]++;
			}
		}
		for ($j = 0; $j < count($focus_list);$j++){
			$modName = $focus_list[$j];
			if (!isLev4($modName) && $prReq["Focus"] > $prReq["Focus4"]){
				$prReq["Focus"] -= $modulesMC[$modName];	
				$BandD -= $modulesMC[$modName];
				$count[$modName]++;
			}
		}
	}
	
	if (array_key_exists("Scie", $prReq)){
		for ($j = 0; $j < count($sci_list); $j++){
			$modName = $sci_list[$j];
			if ($prReq["Scie"] > 0){
				$prReq["Scie"] -= $modulesMC[$modName];
				$count[$modName]++;
			}
		}
	}

	if (array_key_exists("Lev4", $prReq)){			//handle case of MCS of lev 4 mod
		for ($i = 0; $i < count($PRmod); $i++){
			$modName = $PRmod[$i][0];
			if (isLev4Prefix($modName,$prefix)){
				if ($prReq["Lev4"] >0){
					if ($count[$modName] == 0){
						$BandD -= $modName[$modName];
					}
					$prReq["Lev4"] -= $modulesMC[$modName];
					$count[$modName]++;
				}
			}
		}
	}

	//handle the or cases
	$left = array();
	for ($i = 0; $i < count($or); $i++){
		$case = $or[$i];
		$min = 120 ;
		$minMods = array();
		for ($j = 0; $j < count($case); $j++){
			$satisfyMods = hasCompleteCS($case[$j], $PRmod, $count, $modulesMC, $sci_list);			//[0] = number of MCs not cleared, [1][2] ... list of mods used to clear 
			
			if ($satisfyMods[count($satisfyMods)-1] < $min){
				$min = $satisfyMods[count($satisfyMods)-1];
				$minMods = array_slice($satisfyMods,0,count($satisfyMods)-1);
			}
		}
		

		for ($j = 0; $j < count($minMods); $j++){
			$count[$minMods[$j]]++;
		}
		$left[$i] = $min;
	}	

	$keys = array_keys($prReq);
	$PRsum = 0;
	for ($i = 0; $i < count($prReq); $i++){
		if ($keys[$i] != "Focus4"){
			$PRsum += $prReq[$keys[$i]];
		}
	}
	for ($i = 0; $i < count($left); $i++){
		$PRsum += $left[$i];
	}
	//handle B&D + overlapping mods
	for ($i =0; $i < count($PRmod); $i++){
		$modName = $PRmod[$i][0];
		if ($count[$modName] > 1){
			$PRsum += $modulesMC[$modName];
		}
	}

	for ($i =0; $i < count($PRmod); $i++){
		if ($BandD > 0){
			$modName = $PRmod[$i][0];
			if ($count[$modName] == 0 && isCSMod($modName)){
				$BandD -= $modulesMC[$modName];
				$PRsum -= $modulesMC[$modName];
				$count[$modName]++;
			}
		}
	}
	return $PRsum;
}

function hasCompleteCS($group, $PRmod, $count, $modulesMC, $sci_list){
	$total = $group[1];
	$satisfyMods =array();
	$index = 0;
	$mods = preg_split("/\,/", $group[0]);

	for ($i = 0; $i < count($mods); $i++){
		$modName = $mods[$i];
		if (array_key_exists($modName,$modulesMC)){
			$total -= $modulesMC[$modName];
			$satisfyMods[$index] = $modName;
			$index++;
		} else if ($mods == "Lev4"){
			for ($j = 0; $j < count($PRmod); $j++){
				$modName = $PRmod[$j][0];
				if (isLev4Prefix($modName,$prefix) && $count[$modName] == 0 && $modulesMC[$modName] == 4){
					$total -= $modulesMC[$modName];
					$satisfyMods[$index] = $modName;
					$index++;
				}
			}
		} else if ($mods == "Scie"){
			for ($j = 0; $j < count($sci_list); $j++){
				$modName = $sci_list[$j];
				if ($count[$modName] == 0){
					$total -= $modulesMC[$modName];
					$satisfyMods[$index] = $modName;
					$index++;
				}
			}
		}
	}
	
	$satisfyMods[count($satisfyMods)] = $total;
	return $satisfyMods;
}

function getFocusMod($focus_area){
	$file = "/var/www/html/main/req/CS/fa.json";
	$data = file_get_contents($file);
	return json_decode($data, true)[$focus_area];
}

function getScienceMod(){
	$file = "/var/www/html/main/req/CS/science.json";
	$data = file_get_contents($file);
	return json_decode($data, true);
}

function isCSMod($modName){
	$substr = substr($modName, 0,2);
	$lev = substr($modName, 2,1);
	return ($substr == "CS" && intval($lev) != 0);
}
?>