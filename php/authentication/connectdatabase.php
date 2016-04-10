<?php
// Connect to sql server
define('DB_HOSTNAME', 'localhost');
define('DB_USERID', 'root');
define('DB_PASSWORD', '12moreorless');
define('DB_NAME', 'DATA');
define('table', 'USER');
define('totalSem', 8);

if ($_REQUEST['cmd'] == "getModules"){
	//Connect to database
	$db = new mysqli(DB_HOSTNAME, DB_USERID, DB_PASSWORD, DB_NAME);

	if ($db->connect_errno) {
		exit('Fail to connect to mysql server');
	}	

	$user_id = $db->escape_string($_REQUEST['matric']);

	$query = "SELECT * FROM USER WHERE user_id = '".$user_id."'";
	$res = $db->query($query);
	if (!res) exit('Error retrieving');
	if (mysql_num_rows($res) == 0){
		echo "";
	}else{
		$r = mysql_fetch_row($res);	//matric, sem1, sem2, sem3, ..., sem8
		$returnList = array();
		for ($i = 1; $i < totalSem; $i++){
			$moduleList = parseModules($r[$i]);
			array_push($returnList, $moduleList);
		}	
		echo json_encode($returnList);
	}
	$db->close();
}

if ($_REQUEST['cmd'] == "storeModules"){
	$db = new mysqli(DB_HOSTNAME, DB_USERID, DB_PASSWORD, DB_NAME);

	if ($db->connect_errno) {
		exit('Fail to connect to mysql server');
	}	

	$user_id = $db->escape_string($_REQUEST['matric']);
	$givenModules = $db->escape_string($_REQUEST['modules']);
	$modules = json_decode($givenModules, true);
	if (count($modules) != totalSem){
		exit('Invalid input');
	}

	$query = "SELECT * FROM USER WHERE user_id = '".$user_id."'";
	$res = $db->query($query);
	if (!res) exit("Error retrieving");
	if (mysql_num_rows($res) == 0){
		//insert
		$query = "INSERT INTO USER VALUES('".$user_id."','".stringifyModules($modules[0])."','".stringifyModules($modules[1])."','".stringifyModules($modules[2])."','".stringifyModules($modules[3])."','".stringifyModules($modules[4])."','".stringifyModules($modules[5])."','".stringifyModules($modules[6])."','".stringifyModules($modules[7])."')";
	} else {
		//update
		$query = "UPDATE USER SET sem1='".stringifyModules($modules[0])."',sem2='".stringifyModules($modules[1])."',sem3='".stringifyModules($modules[2])."',sem4='".stringifyModules($modules[3])."',sem5='".stringifyModules($modules[4])."',sem6='".stringifyModules($modules[5])."',sem7='".stringifyModules($modules[6])."',sem8='".stringifyModules($modules[7])."' WHERE user_id = '".$user_id."'";
	}
	$res = $db->query($query);
	if (!res) exit("Error updating");
	$db->close();
	echo ("Sucess");
}

function parseModules($list){
	return preg_split("/\,/", $list);
}

function stringifyModules($list){
	$returnStr = "";
	for ($i = 0; $i < count($list); $i++){
		$returnStr .= $list[$i];
		if ($i != count($list) - 1){
			$returnStr .= ",";
		}
	}
	return $returnStr;
}


?>

