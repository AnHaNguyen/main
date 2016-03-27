<?php
// Connect to sql server
define('DB_HOSTNAME', 'localhost');
define('DB_USERID', 'root');
define('DB_PASSWORD', '12moreorless');
define('DB_NAME', 'DATA');

//Connect to database
$db = new mysqli(DB_HOSTNAME, DB_USERID, DB_PASSWORD, DB_NAME);

if ($db->connect_errno) {
	exit('Fail to connect to mysql server');
}

/*	$query = 'INSERT INTO USER (USERNAME, HASHED_PASSWORD, FIRSTNAME, LASTNAME, EMAIL, ADMISSION_YEAR';
	$query = $query.", TAKEN_MODULES, FOCUS_AREA) VALUES (\"admin\", \"".crypt('1')."\", \"John\", \"Doe\", \"admin@gmail.com\"";
$query = $query.", 2015, \"[]\", \"AI\");"; */
?>
