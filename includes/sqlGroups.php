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
	$createdBy = $_SESSION["userId"];
	
	// Register group
	$sql = "INSERT INTO `groups` (groupName, createdBy) VALUES (?, ?);";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		echo("Error");
		exit();
	}
	if(!mysqli_stmt_bind_param($stmt, "ss", $groupName, $createdBy)){
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
}

exit();
?>