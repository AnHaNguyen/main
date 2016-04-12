<?php

/* serve to find module that doesn't exist in current list*/
//api.nusmods.com/2013-2014/1/bulletinModulesRaw.json
if (isset($_REQUEST['code'])){
	$code = $_REQUEST['code'];
}else{
	$code = "GEM2900";		//test sem1
	//$code = "CS3226";		//test sem2
}
$list = ["http://api.nusmods.com/2015-2016/1/modules/".$code.".json",
		"http://api.nusmods.com/2015-2016/2/modules/".$code.".json"];

$moduleInfo = findModule($list);
echo json_encode($moduleInfo);

function findModule($list){
	for ($i = 0; $i < count($list); $i++){
		$file = $list[$i];
		$content = file_get_contents($file);
		if ($content){
			$data = json_decode($content,true);
			$code = $data["ModuleCode"];
			$title = $data["ModuleTitle"];
			$MC = $data["ModuleCredit"];
			return array("code"=>$code, "title"=>$title, "MC"=>$MC);
		}
	}
	return false;
}
?>
