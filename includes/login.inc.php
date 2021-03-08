<?php 
require_once 'dbh.inc.php';
require_once 'cookieFunctions.inc.php';

session_start();

$function = $_POST['function'];

if ($function == 'checkEmail') {
	$email = $_POST['email'];
	$sql = "SELECT * FROM users WHERE email = ?;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		header("location: ../login.php");
		exit();
	}
	
	mysqli_stmt_bind_param($stmt, "s", $email);
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
	echo mysqli_num_rows($result);
	exit();
} elseif ($function == 'loginUser') {
	
	$email = $_POST['email'];
	$pwd = $_POST['password'];
	
	$sql = "SELECT pwd, userId FROM users WHERE email = ? LIMIT 1;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		header("location: ../login.php");
		exit();
	}
	
	mysqli_stmt_bind_param($stmt, "s", $email);
	mysqli_stmt_execute($stmt);
	
	$resultData = mysqli_stmt_get_result($stmt);
	
	if ($row = mysqli_fetch_assoc($resultData)) {
		$pwdHashed = $row['pwd'];
		$userId = $row['userId'];
	} 
	mysqli_stmt_close($stmt);
	
	$checkPwd = password_verify($pwd, $pwdHashed);
	
	if ($checkPwd == 1) {
		$_SESSION["userId"] = $userId;
		
		// Check if remember me has been clicked
		if (!empty($_POST['rmbMe'])) {
			createRememberMeCookie($conn, $userId);
		}
		
		echo "Success";
	} else {
		echo "Wrong Password";
	}
	exit();
}

?>