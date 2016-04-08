<html>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
User ID:  <input type="text" name="user_id"><br>
Password: <input type="password" name="password"><br>
Repeat Password: <input type="password" name="confirm"><br>
<input type="submit">
<?php session_start();
	require('authenticate.php');
	
	if ($_POST["username"] && $_POST["password"] && $_POST["confirm"]) {

		$username = $_POST["username"];
		$password = $_POST["password"];
		$confirm = $_POST["confirm"];

		if ($password !== $confirm) {
			echo "Password does not match the confirm password";
		} else {
			if (!register($username, $password)) {
				echo "User ID already exists";
			} else {

				$authentication = authenticate($username, $password);

				if (!$authentication) {
					echo "Wrong User ID or wrong password";
				} else {
					$username = $authentication['username'];
					$role = $authentication['role'];

					$_SESSION['username'] = $username;
					$_SESSION['role'] = $role;
					header('Location: '.cutURL($_SERVER['PHP_SELF']).'/admin.php');
				}
			}
		}
	} else {
		echo "Missing something ?";
	}
?>
</form>

</body>
</html>
