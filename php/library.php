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
?>
