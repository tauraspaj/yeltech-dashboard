<?php 
require_once 'dbh.inc.php';
require_once 'cookieFunctions.inc.php';

session_start();

$email = $_POST['email'];
$password = $_POST['password'];

if (!empty($_POST['rmbMe'])) {
	// echo 'ticked';
} else {
	// echo 'not ticked';
}

$response = array();

// Check for empty fields
if (empty($email) || empty($password)) {
	$response['status'] = 'Error';
	$response['fields'] = null;
	$response['message'] = 'All fields must be filled';

	echo json_encode($response);
	exit();
}

// Check if email exists
$sql = "
	SELECT users.userId, users.pwd, groups.dashAccess 
	FROM users
	LEFT JOIN `groups` ON users.groupId = `groups`.groupId
	WHERE users.email = ?
";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
	header("location: ../login.php");
	exit();
}
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ( mysqli_num_rows($result) == 0 ) {
	$response['status'] = 'Error';
	$response['fields'] = 'login_email';
	$response['message'] = 'This email does not exist';

	echo json_encode($response);
	exit();
}

if ( $row = mysqli_fetch_assoc($result) ) {
	$pwdHashed = $row['pwd'];
	$dashAccess = $row['dashAccess'];
	$userId = $row['userId'];
}

mysqli_stmt_close($stmt);

// Verify password
$checkPwd = password_verify($password, $pwdHashed);
if ( password_verify($password, $pwdHashed) == 1 ) {
	// Make sure user's group has access to the dashboard
	if ($dashAccess == 1) {
		$_SESSION["userId"] = $userId;
	
		// Check if remember me has been clicked
		if (!empty($_POST['rmbMe'])) {
			createRememberMeCookie($conn, $userId);
		}
		
		$response['status'] = 'OK';
		$response['fields'] = null;
		$response['message'] = null;

		echo json_encode($response);
		exit();

	} else {
		// In case group does not have access to the dashboard
		$response['status'] = 'Error';
		$response['fields'] = null;
		$response['message'] = 'Your group does not have access to YelCloud dashboard';

		echo json_encode($response);
		exit();
	}

	echo $dashAccess;
	exit();
} else {
	$response['status'] = 'Error';
	$response['fields'] = 'login_pwd';
	$response['message'] = 'Password is incorrect';

	echo json_encode($response);
	exit();
}

?>