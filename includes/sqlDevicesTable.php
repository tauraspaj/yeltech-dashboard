<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

if ($function == 'showDevices') {
	// Display all devices to super admins
	if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
		$groupFilter = $_SESSION['groupId'];
	} else {
		$groupFilter = $_POST['groupId'];
	}

	$searchString = $_POST['pageSearchString'];

	$productTypeSearch = '';
	if (isset($_POST['selectedProducts'])) {
		if (is_array($_POST['selectedProducts'])) {
			for ($i = 0; $i < count($_POST['selectedProducts']); $i++) {
				if ($i == count($_POST['selectedProducts'])-1) {
					$productTypeSearch .= "devices.productId = {$_POST['selectedProducts'][$i]}";
				} else {
					$productTypeSearch .= "devices.productId = {$_POST['selectedProducts'][$i]} OR ";
				}
			}
		} else {
			$productTypeSearch = "devices.productId = {$_POST['selectedProducts']}";
		}
	} else {
		$productTypeSearch = "devices.productId = '-1'";
	}

	$devicesPerPage = $_POST['devicesPerPage'];
	$offset = $_POST['offset'];

	$sql = "
	SELECT devices.deviceId, devices.deviceName, devices.deviceAlias, devices.nextCalibrationDue, devices.deviceStatus, devices.customLocation, devices.latitude, devices.longitude, `groups`.groupName as groupName
	FROM devices
	LEFT JOIN `groups` on devices.groupId = `groups`.groupId
	WHERE ($productTypeSearch) AND (devices.groupId = $groupFilter) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%')
	ORDER BY `groups`.groupName ASC, devices.deviceName ASC
	LIMIT $devicesPerPage OFFSET $offset
	";

	$resultArray = array();

	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);

	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			// Find the number of alarms triggered for each device
			$sqlAlarms = "
				SELECT COUNT(triggerId) AS alarmsTriggered FROM alarmTriggers WHERE deviceId = {$row['deviceId']} AND isTriggered = 1;
			";
			$resultAlarms = mysqli_query($conn, $sqlAlarms);
			if ( mysqli_num_rows($resultAlarms) > 0 ) {
				while ($row3 = mysqli_fetch_assoc($resultAlarms)) {
					$row +=  $row3;
				}
			}

			$resultArray[] = $row;
		}
	}


	$sqlTotal = "
	SELECT COUNT(*) as totalRows FROM devices WHERE ($productTypeSearch) AND (devices.groupId = $groupFilter) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%')
	";

	$result = mysqli_query($conn, $sqlTotal);
	$resultCheck = mysqli_num_rows($result);

	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			// Return power role id
			$powerRole = array('powerRole' => $_SESSION['roleId']);
			$row += $powerRole;
			$resultArray[] = $row;
		}
	}

	echo json_encode($resultArray);
	exit();
}


?>