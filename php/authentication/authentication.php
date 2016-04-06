<?php session_start()

	function register($username, $password) {
		// Connect to sql server
	/*	$db = new mysqli('localhost', 'e0008977', '12moreorless!!!', 'e0008977');

		if ($db->connect_errno) {
			return false;
		}

		// check if user exists;
		$query = "SELECT * FROM USER WHERE USER_ID=\"".$username."\";";
		$res = $db->query($query);
		$row = mysqli_fetch_row($res);
		if (count($row) > 0) return false;

		// Connect to sql server successfully
		$query = "INSERT INTO USER (USER_ID, HASHED_PASSWORD, ROLE) VALUES (\"".$username."\", \"".crypt($password)."\", 1);";
		$res = $db->query($query);
		return true; */
	}

	function authenticate($username, $password) {
		require('connectdatabase.php');

		// Find user by given username
		$query = "SELECT * FROM USER WHERE USERNAME=\"".$username."\";";
		$res = $db->query($query);
		$row = mysqli_fetch_row($res);

		// No user found = fail authentication
		if (count($row) <= 0) {
			return false;
		} else {
			// Compare hashed_password and password
			$hashed_password = $row[1];

			if ($hashed_password !== crypt($password, $hashed_password)) {
				return false;
			} else {
				// Login succesfully
				$result = array('username' => $username);
				return $result;
			}
		}
	}

	$COMMAND = $_REQUEST['CMD'];

	switch ($COMMAND) {
		case 'LOGIN':
			// Login 
			if ($_GET["username"] && $_GET["password"]) {
				$username = $_GET["username"];
				$password = $_GET["password"];

				$authentication = authenticate($username, $password);

				if (!$authentication) {
					echo "Wrong username or password";
				} else {
					$username = $authentication['username'];

					$_SESSION['username'] = $username;
					echo "Success";
				}
			} elseif ((!$_POST["username"]) && (!$_POST["password"])) {
				exit("username or password is missing");
			} 
			break;
		CASE 'LOGOUT':
			session_destroy();
			break;
		default:
	}
?>
