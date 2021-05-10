<?php
require_once 'dbh.inc.php';
session_start();

if ($_POST['function'] == 'showGroups') {
	if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
		$groupFilter = $_SESSION['groupId'];
	} else {
		$groupFilter = $_POST['groupId'];
	}
	
	$groupsPerPage = $_POST['groupsPerPage'];
	$offset = $_POST['offset'];
	$searchString = $_POST['pageSearchString'];
	
	$sql = "
	SELECT `groups`.groupId, `groups`.groupName, `groups`.latitude, `groups`.longitude
	FROM `groups`
	WHERE (`groups`.groupId = $groupFilter) AND (`groups`.groupName LIKE '%$searchString%')
	ORDER BY `groups`.groupName ASC
	LIMIT $groupsPerPage OFFSET $offset
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$returnArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$currentGroup = $row['groupId'];
	
			$sqlUsersCount = "
			SELECT COUNT(*) as totalUsersCount
			FROM users
			WHERE users.groupId = $currentGroup
			";
		
			$resultTemp = mysqli_query($conn, $sqlUsersCount);
			$resultCheck = mysqli_num_rows($resultTemp);
			
			if ($resultCheck > 0) {
				while ($rowThis = mysqli_fetch_assoc($resultTemp)) {
					$row += $rowThis;
				}
			}
		
			$sqlDevicesCount = "
			SELECT COUNT(*) as totalDevicesCount
			FROM devices
			WHERE devices.groupId = $currentGroup
			";
			
			$resultTemp = mysqli_query($conn, $sqlDevicesCount);
			$resultCheck = mysqli_num_rows($resultTemp);
			
			if ($resultCheck > 0) {
				while ($rowThis = mysqli_fetch_assoc($resultTemp)) {
					$row += $rowThis;
				}
			}

			$resultArray[] = $row;

		}
	}
	
	$sqlTotal = "
	SELECT COUNT(*) as totalRows FROM `groups` WHERE (`groups`.groupId = $groupFilter) AND (`groups`.groupName LIKE '%$searchString%')
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

} elseif ($_POST['function'] == 'checkGroupName') {
	$groupName = $_POST['groupName'];
	$result = mysqli_query($conn, "SELECT 1 FROM `groups` WHERE groupName LIKE '$groupName'");
	echo mysqli_num_rows($result);

	exit();
} elseif ($_POST['function'] == 'register') {
	$groupName = $_POST['groupName'];
	$createdBy = $_SESSION['fullName'];
	$dashAccess = $_POST['dashAccess'];
	$appAccess = $_POST['appAccess'];
	
	// Register group
	$sql = "INSERT INTO `groups` (groupName, createdBy, dashAccess, appAccess) VALUES (?, ?, ?, ?);";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		echo("Error");
		exit();
	}
	if(!mysqli_stmt_bind_param($stmt, "ssss", $groupName, $createdBy, $dashAccess, $appAccess)){
		echo("Error");
		exit();
	}
	if(!mysqli_stmt_execute($stmt)) {
		echo("Error");
		exit();
	}
	mysqli_stmt_close($stmt);

	echo("Success");
	exit();
} elseif ($_POST['function'] == 'findGroup') {
	$groupId = $_POST['groupId'];
	$sql = "
	SELECT `groups`.groupId, `groups`.groupName, `groups`.latitude, `groups`.longitude, `groups`.dashAccess, `groups`.appAccess, `groups`.createdAt, `groups`.createdBy
	FROM `groups`
	WHERE groupId = $groupId
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			$sqlUsersCount = "
			SELECT COUNT(*) as nUsers
			FROM users
			WHERE users.groupId = $groupId
			";
		
			$resultTemp = mysqli_query($conn, $sqlUsersCount);
			$resultCheck = mysqli_num_rows($resultTemp);
			
			if ($resultCheck > 0) {
				while ($rowThis = mysqli_fetch_assoc($resultTemp)) {
					$row += $rowThis;
				}
			}
		
			$sqlDevicesCount = "
			SELECT COUNT(*) as nDevices
			FROM devices
			WHERE devices.groupId = $groupId
			";
			
			$resultTemp = mysqli_query($conn, $sqlDevicesCount);
			$resultCheck = mysqli_num_rows($resultTemp);
			
			if ($resultCheck > 0) {
				while ($rowThis = mysqli_fetch_assoc($resultTemp)) {
					$row += $rowThis;
				}
			}

			echo json_encode($row);
		}
	}
	exit();
} elseif ($_POST['function'] == 'updateGroupInfo') {
	$groupId = $_POST['groupId'];
	$groupName = $_POST['groupName'];
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];
	$appAccess = $_POST['appAccess'];
	$dashAccess = $_POST['dashAccess'];
	
	$response = array();

	if ($latitude == '' || $longitude == '') {
		$latitude = null;
		$longitude = null;
	} else {
		if (preg_match('/^[0-9.-]+$/', $latitude) && preg_match('/^[0-9.-]+$/', $longitude)) {
			$latitude = floatval($latitude);
			$longitude = floatval($longitude);

			if ( $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
				$response['status'] = 'Error';
				$response['message'] = 'Invalid coordinates';
				echo json_encode($response);
				exit;
			}
		} else {
			$response['status'] = 'Error';
			$response['message'] = 'Invalid coordinates';
			echo json_encode($response);
			exit;
		}
	}

	if ($appAccess != 0 && $appAccess != 1) {
		$response['status'] = 'Error';
		$response['message'] = 'Invalid app access value';
		echo json_encode($response);
		exit;
	}
	if ($dashAccess != 0 && $dashAccess != 1) {
		$response['status'] = 'Error';
		$response['message'] = 'Invalid dashboard access value';
		echo json_encode($response);
		exit;
	}

	$sql = "UPDATE `groups` SET groupName=?, latitude=?, longitude=?, appAccess=?, dashAccess=? WHERE groupId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ssssss", $groupName, $latitude, $longitude, $appAccess, $dashAccess, $groupId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 'Error';
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 'OK';
		}
        mysqli_stmt_close($stmt);

		echo json_encode($response);

	exit();
} elseif ($_POST['function'] == 'deleteGroup') {
	$groupId = $_POST['groupId'];

	if ($_SESSION['roleId'] == 3 || $_SESSION['roleId'] == 3) {
		echo 'You have no permissions!';
		exit();
	}

	if ($_SESSION['groupId'] == $groupId) {
		echo 'Cannot delete own group profile!';
		exit();
	}
	
	$sql = "DELETE FROM `groups` WHERE groupId = $groupId;";
	mysqli_query($conn, $sql);
	echo 'Group has been deleted!';

	exit();
}

exit();
?>