<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

if ($function == 'showDevices') {
	// Display all devices to super admins
	if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
		$groupFilter = $_SESSION['groupId'];
		$order = 'ORDER BY alarmsTriggered DESC, devices.deviceStatus DESC, `groups`.groupName ASC, devices.deviceName ASC';
	} else {
		$groupFilter = $_POST['groupId'];
		$order = 'ORDER BY `groups`.groupName ASC, devices.deviceName ASC';
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
	SELECT DISTINCT devices.deviceId, devices.deviceName, devices.deviceAlias, devices.nextCalibrationDue, devices.deviceStatus, devices.customLocation, devices.latitude, devices.longitude, `groups`.groupName as groupName, subscriptions.subFinish, (SELECT COUNT(*) FROM alarmTriggers WHERE alarmTriggers.isTriggered = 1 AND alarmTriggers.deviceId = devices.deviceId) AS alarmsTriggered
	FROM devices
	LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
	LEFT JOIN subscriptions ON devices.deviceId = subscriptions.deviceId
	LEFT JOIN alarmTriggers ON devices.deviceId = alarmTriggers.deviceId
	WHERE ($productTypeSearch) AND (devices.groupId = $groupFilter) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%' OR devices.devicePhone LIKE '%$searchString%' OR `groups`.groupName LIKE '%$searchString%')
	$order
	LIMIT $devicesPerPage OFFSET $offset
	";

	$resultArray = array();

	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);

	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$sqlTriggeredAlarms = "
				SELECT alarmDescription FROM alarmTriggers WHERE deviceId = {$row['deviceId']} AND isTriggered = 1
			";
			$resultTriggeredAlarms = mysqli_query($conn, $sqlTriggeredAlarms);
			$triggeredAlarmsArr = array();
			if ( mysqli_num_rows($resultTriggeredAlarms) > 0 ) {
				while ($rowTriggeredAlarms = mysqli_fetch_assoc($resultTriggeredAlarms)) {
					array_push($triggeredAlarmsArr, $rowTriggeredAlarms);
				}
			}
			$row['alarms'] = $triggeredAlarmsArr;

			$sqlReading = "
				SELECT measurements.measurement, measurements.measurementTime, units.unitName
				FROM measurements
				LEFT JOIN channels ON measurements.channelId = channels.channelId
				LEFT JOIN units ON channels.unitId = units.unitId
				WHERE measurements.deviceId = {$row['deviceId']}
				ORDER BY measurements.channelId ASC, measurements.measurementTime DESC
				LIMIT 1
			";
			$resultReading = mysqli_query($conn, $sqlReading);
			if ( mysqli_num_rows($resultReading) > 0 ) {
				while ($row4 = mysqli_fetch_assoc($resultReading)) {
					$row += $row4;
				}
			} else {
				$row['measurement'] = null;
				$row['measurementTime'] = null;
			}

			$resultArray[] = $row;
		}
	}


	$sqlTotal = "
	SELECT COUNT(*) as totalRows FROM devices 
	LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
	WHERE ($productTypeSearch) AND (devices.groupId = $groupFilter) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%' OR devices.devicePhone LIKE '%$searchString%' OR `groups`.groupName LIKE '%$searchString%')
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