<?php
function isLev4($modName){		//check a mod is lev4 or higher
	for ($i = 0; $i < strlen($modName) -1;$i++){
		$char = substr($modName, $i, $i+1);
		if (intval($char) != 0){
			return (intval($char) >= 4);
		}
	}
	return false;
}

function isLev4Prefix($modName, $prefix){
	for ($i = 0; $i < count($prefix); $i++){
		if (strpos($modName, $prefix[$i]) !== false){
			$lev = substr($modName, strlen($prefix[$i]));
			if (intval($lev) >= 4){
				return true;
			}
		}
	}
	return false;
}

function getElectiveMod($major){
	$file = "/var/www/html/main/req/".$major."/elective.json";
	$data = file_get_contents($file);
	return json_decode($data, true);
}

function isInList($mod, $list){
	$modName = extractMod($mod);
	$key = array_keys($list);
	for ($i = 0; $i < count($key);$i++){
		$pos = strpos($key[$i],$modName);
		if ($pos !== false && $pos == 0){
			return $key[$i];
		}
	}
	return "";
}

function extractMod($mod){
	for ($i = strlen($mod) -1; $i >= 0; $i--){
		if (is_numeric(substr($mod, $i, 1))){
			return substr($mod, 0, $i+1);
		}
	}
}

function handleSpecialCases($PRmod){
	$cases =array(array("mod"=>array("CS2020"=>6),"replace"=>array("CS1020"=>4,"CS2010"=>4),"MC"=>-2));		//so far 1 case 2020 = 1020 + 2010
	$type = "PR";
	$specialMCs = 0;
	for ($i = 0; $i < count($cases); $i++){
		$case = $cases[$i];
		$mod = $case["mod"];
		$replace = $case["replace"];
		$replaceArr = array();
		$key = array_keys($replace);

		for ($i = 0; $i < count($replace); $i++){
			$arr = array($key[$i], $type, $replace[$key[$i]]);
			array_push($replaceArr, $arr);
		}

		$key = array_keys($mod);
		$indices = array();
		for ($j = 0; $j < count($key); $j++){
			$modName = $key[$j];
			for ($k = 0; $k < count($PRmod); $k++){
				if ($PRmod[$k][0] == $modName && ($PRmod[$k][1] == $type || $PRmod[$k][1] == "")){
					array_push($indices, $k);
				}
			}
		}
		if (count($indices) == count($mod)){
			$specialMCs -= $case["MC"];
			for ($j = 0; $j < count($indices); $j++){
				unset($PRmod[$indices[$j]]);
			}

			$key = array_keys($replace);
			for ($j = 0; $j < count($replace); $j++){
				$arr = array($key[$j], $type, $replace[$key[$j]]);
				array_push($PRmod, $arr);
			}
			$PRmod = removeDuplicate($PRmod);
		}
	}
	return array($PRmod, $specialMCs);
}

function removeDuplicate($modules){
	$modules = array_unique($modules, SORT_REGULAR);
	$array = array();
	$key = array_keys($modules);
	for ($i = 0; $i < count($modules); $i++){
		array_push($array, $modules[$key[$i]]);
	}
	return $array;
}
?>
