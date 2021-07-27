<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

if ($function == 'showUsers') {
	
	if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
		$groupFilter = $_SESSION['groupId'];
	} else {
		$groupFilter = $_POST['groupId'];
	}

	$searchString = $_POST['searchString'];
	
	$roleSearch = '';
	if (isset($_POST['roles'])) {
		if (is_array($_POST['roles'])) {
			for ($i = 0; $i < count($_POST['roles']); $i++) {
				if ($i == count($_POST['roles'])-1) {
					$roleSearch .= "users.roleId = {$_POST['roles'][$i]}";
				} else {
					$roleSearch .= "users.roleId = {$_POST['roles'][$i]} OR ";
				}
			}
		} else {
			$roleSearch = "users.roleId = {$_POST['roles']}";
		}
	} else {
		$roleSearch = "users.roleId = '-1'";
	}
	
	$sendingTypesSearch = '';
	if (isset($_POST['sendingTypes'])) {
		if (is_array($_POST['sendingTypes'])) {
			for ($i = 0; $i < count($_POST['sendingTypes']); $i++) {
				if ($i == count($_POST['sendingTypes'])-1) {
					$sendingTypesSearch .= "users.sendingId = {$_POST['sendingTypes'][$i]}";
				} else {
					$sendingTypesSearch .= "users.sendingId = {$_POST['sendingTypes'][$i]} OR ";
				}
			}
		} else {
			$sendingTypesSearch = "users.sendingId = {$_POST['sendingTypes']}";
		}
	} else {
		$sendingTypesSearch = "users.sendingId = '-1'";
	}
	
	$devicesPerPage = $_POST['usersPerPage'];
	$offset = $_POST['offset'];
	
	$sql = "
	SELECT users.userId, users.fullName, `groups`.groupName as groupName, users.email, users.phoneNumber, users.roleId, users.groupId, users.sendingId, roles.roleName as role, sendingType.sendingType as sendingType
	FROM users
	LEFT JOIN `groups` ON users.groupId = `groups`.groupId
	LEFT JOIN sendingType ON users.sendingId = sendingType.sendingId
	LEFT JOIN roles ON users.roleId = roles.roleId
	WHERE ($roleSearch) AND ($sendingTypesSearch) AND (users.groupId = $groupFilter) AND (users.fullname LIKE '%$searchString%' OR users.email LIKE '%$searchString%' OR `groups`.groupName LIKE '%$searchString%')
	ORDER BY roleId ASC, groupName ASC, fullName ASC
	LIMIT $devicesPerPage OFFSET $offset
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$resultArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	
	$sqlTotal = "
	SELECT COUNT(*) as totalRows FROM users 
	LEFT JOIN `groups` ON users.groupId = `groups`.groupId
	WHERE ($roleSearch) AND ($sendingTypesSearch) AND (users.groupId = $groupFilter) AND (users.fullname LIKE '%$searchString%' OR users.email LIKE '%$searchString%' OR `groups`.groupName LIKE '%$searchString%')
	";
	
	$result = mysqli_query($conn, $sqlTotal);
	$resultCheck = mysqli_num_rows($result);
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'roleCount') {
	$data = array();

	// Display global data to super admins
	if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
		$groupFilter = $_SESSION['groupId'];
	} else {
		$groupFilter = 'users.groupId';
	}

	// Count total users
	$sql1 = "
	SELECT COUNT(*) as totalRows FROM users WHERE users.groupId = $groupFilter
	";
	$result = mysqli_query($conn, $sql1);
	$resultCheck = mysqli_num_rows($result);	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$data['totalUsers'] = $row['totalRows'];
		}
	}

	// Count group admins
	$sql2 = "
	SELECT COUNT(*) as totalRows FROM users WHERE users.groupId = $groupFilter AND roleId = 3
	";
	$result2 = mysqli_query($conn, $sql2);
	$resultCheck2 = mysqli_num_rows($result2);	
	if ($resultCheck2 > 0) {
		while ($row = mysqli_fetch_assoc($result2)) {
			$data['totalGroupAdmins'] = $row['totalRows'];
		}
	}
	
	echo json_encode($data);
	exit();
} elseif ($function == 'showLatestUsers') {
	// Display global data to super admins
	if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
		$groupFilter = $_SESSION['groupId'];
	} else {
		$groupFilter = 'users.groupId';
	}

	// Count total users
	$sql1 = "
	SELECT users.fullName, users.createdAt FROM users WHERE users.groupId = $groupFilter ORDER BY users.createdAt DESC LIMIT 3
	";
	$result = mysqli_query($conn, $sql1);
	$resultCheck = mysqli_num_rows($result);	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = $row;
		}
	}
	
	echo json_encode($data);
	exit();
} elseif ($function == 'findUser') {
	$userId = $_POST['userId'];
	$response = array();

	$sql = "
	SELECT users.userId, users.fullName, `groups`.groupName, `groups`.groupId, users.roleId, users.sendingId, roles.roleName, users.email, users.phoneNumber, sendingType.sendingType, users.createdAt
	FROM users
	LEFT JOIN `groups` ON users.groupId = `groups`.groupId
	LEFT JOIN roles ON users.roleId = roles.roleId
	LEFT JOIN sendingType ON users.sendingId = sendingType.sendingId
	WHERE users.userId = $userId
	LIMIT 1
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			echo json_encode($row);
		}
	}
	exit();
} elseif ($function == 'deleteUser') {
	$userId = $_POST['userId'];
	// Check the role and group id of the user that needs to be deleted
	$sql = "
	SELECT roleId, groupId FROM users WHERE userId = $userId
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			$roleId = $row['roleId'];
			$groupId = $row['groupId'];
		}
	}

	if ($_SESSION['userId'] == $userId) {
		echo 'Cannot delete own profile!';
		exit();
	}
	
	if ($_SESSION['roleId'] <= $roleId) {
		if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
			$sql = "DELETE FROM users WHERE userId = $userId;";
		}
	
		// Group admin can only delete users in the same group
		if ($_SESSION['roleId'] == 3 && $_SESSION['groupId'] == $groupId) {
			$sql = "DELETE FROM users WHERE userId = $userId AND groupId = $groupId;";
		}
		mysqli_query($conn, $sql);
		echo 'User has been deleted!';
	}
	exit();
} elseif ($function == 'saveUser') {
	$userId = $_POST['userId'];
	$fullName = $_POST['fullName'];
	$groupId = $_POST['groupId'];
	$roleId = $_POST['roleId'];
	$email = $_POST['email'];
	$phoneNumber = $_POST['phoneNumber'];
	$sendingId = $_POST['sendingId'];

	if ( empty($fullName) || empty($groupId) || empty($roleId) || empty($email) || empty($sendingId)  ) {
		echo 'Required fields must be filled in!';
		exit();
	}

	// Find current email of the user 
	$sql = "
		SELECT email, phoneNumber FROM users WHERE userId = $userId
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			$oldEmail = $row['email'];
			$oldPhoneNumber = $row['phoneNumber'];
		}
	}

	// Check for uniqe email
	if ($email != $oldEmail) {
		$sql2 = "
			SELECT COUNT(userId) as count FROM users WHERE email = '$email'
		";
		$result2 = mysqli_query($conn, $sql2);
		if ( mysqli_num_rows($result2) > 0 ) {
			while ($row = mysqli_fetch_assoc($result2)) {
				if ($row['count'] > 0 ) {
					echo 'This email already exists!';
					exit();
				}
			}
		}
	}
	
	// Check for unique phone number
	if ($phoneNumber == '') {
		$phoneNumber = null;
	} else {
		if ($phoneNumber != $oldPhoneNumber) {
			$sql3 = "
			SELECT COUNT(userId) as count FROM users WHERE phoneNumber = '$phoneNumber'
			";
			$result3 = mysqli_query($conn, $sql3);
			if ( mysqli_num_rows($result3) > 0 ) {
				while ($row = mysqli_fetch_assoc($result3)) {
					if ($row['count'] > 0 ) {
						echo 'This phone number already exists!';
						exit();
					}
				}
			}
		}
	}

	// Save user info
	$sql = "UPDATE users SET fullName=?, groupId=?, roleId=?, email=?, phoneNumber=?, sendingId=? WHERE userId=?";
	$stmt = mysqli_stmt_init($conn);
	mysqli_stmt_prepare($stmt, $sql);
	mysqli_stmt_bind_param($stmt, "sssssss", $fullName, $groupId, $roleId, $email, $phoneNumber, $sendingId, $userId);
	mysqli_stmt_execute($stmt);
	if (mysqli_stmt_error($stmt)) {
		echo 'Something went wrong!';
	} else {
		echo 'User successfully updated!';
	}
	mysqli_stmt_close($stmt);

	exit();
} elseif ($function == 'resetPassword') {
	$userId = $_POST['userId'];
	$encryptedPwd = password_hash('Passw0rd', PASSWORD_DEFAULT);

	$sql = "
		UPDATE users SET pwd = '$encryptedPwd' WHERE userId = $userId;
	";
	if (mysqli_query($conn, $sql)) {
		echo "Password has been reset to: 'Passw0rd'";
	} else {
		echo "Something went wrong!";
	}
	
	exit();
}

?>