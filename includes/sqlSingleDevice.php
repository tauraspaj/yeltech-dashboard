<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

if ($function == 'loadTable_measurements') {
	$measurementsPerPage = $_POST['measurementsPerPage'];
	$offset = $_POST['offset'];
	$deviceId = $_POST['deviceId'];
	
	$sql = "
	SELECT measurements.measurement, channels.channelName as channelName, measurements.measurementTime, units.unitName as unitName
	FROM measurements
	LEFT JOIN channels ON measurements.channelId = channels.channelId
	LEFT JOIN units ON (measurements.deviceId = channels.deviceId AND channels.unitId = units.unitId)
	WHERE measurements.deviceId = $deviceId
	ORDER BY measurementTime DESC, measurements.channelId ASC
	LIMIT $measurementsPerPage OFFSET $offset
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$returnArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	
	$sqlTotal = "
	SELECT COUNT(*) as totalRows FROM measurements WHERE measurements.deviceId = $deviceId
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
} elseif ($function == 'loadDeviceData') {
	$deviceId = $_POST['deviceId'];

	$sql = "
	SELECT deviceName
	FROM devices
	WHERE deviceId = $deviceId
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$resultArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'loadTable_alarms') {
	$measurementsPerPage = $_POST['measurementsPerPage'];
	$offset = $_POST['offset'];
	$deviceId = $_POST['deviceId'];
	
	$sql = "
	SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmReading, smsAlarms.smsAlarmTime, smsAlarms.isAcknowledged, channels.channelName as channelName
	FROM smsAlarms
	LEFT JOIN channels ON smsAlarms.channelId = channels.channelId
	WHERE smsAlarms.deviceId = $deviceId
	ORDER BY smsAlarms.smsAlarmTime DESC
	LIMIT $measurementsPerPage OFFSET $offset
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$returnArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	
	$sqlTotal = "
	SELECT COUNT(*) as totalRows FROM smsAlarms WHERE smsAlarms.deviceId = $deviceId
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
} elseif ($function == 'loadChart') {
	$deviceId = $_POST['deviceId'];

	$sql = "
	SELECT channels.channelId, channels.channelName
	FROM channels
	WHERE deviceId = $deviceId && channelTypeId = 2
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$returnArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$readingsArray = array();
			$readingsArray[] = $row;

			$sql2 = "
			SELECT measurements.measurement, measurements.measurementTime
			FROM measurements
			WHERE (deviceId = $deviceId AND channelId = {$row['channelId']})
			";
			$dataArr = array();
			$result2 = mysqli_query($conn, $sql2);
			$resultCheck2 = mysqli_num_rows($result2);
			if ($resultCheck2 > 0) {
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$dataArr[] = $row2;
				}
				$readingsArray[] = $dataArr;
			}

			$resultArray[] = $readingsArray;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'loadMap') {
	$deviceId = $_POST['deviceId'];

	$sql = "
	SELECT smsStatus.latitude, smsStatus.longitude 
	FROM smsStatus 
	WHERE ((latitude IS NOT NULL AND longitude IS NOT NULL) AND (deviceId = {$deviceId})) 
	ORDER BY smsStatusId DESC LIMIT 1;
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$resultArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'loadChannelData') {
	$deviceId = $_POST['deviceId'];

	$sql = "
	SELECT channels.channelId, channels.channelName, units.unitAbbreviation
	FROM channels
	LEFT JOIN units ON channels.unitId = units.unitId
	WHERE deviceId = $deviceId
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$resultArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'loadUsersList') {
	$deviceId = $_POST['deviceId'];
	if ($_SESSION['roleId'] == 1 OR $_SESSION['roleId'] == 2) {
		$groupFilter = 'users.groupId';
	} else {
		$groupFilter = $_SESSION['groupId'];
	}

	$sql = "
	SELECT users.userId, users.fullName, users.email
	FROM users
	WHERE users.groupId = $groupFilter
	ORDER BY users.roleId ASC, users.fullName ASC
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$resultArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray[] = $row;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'createCustomAlarm') {
	$deviceId = $_POST['deviceId'];
	$channelId = $_POST['channelId'];
	$operator = $_POST['operator'];
	$thresholdValue = $_POST['thresholdValue'];
	$recipientIds = json_decode($_POST['recipientIds'], true);

	$sql = "INSERT INTO customAlarms (channelId, deviceId, operator, thresholdValue) VALUES (?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $channelId, $deviceId, $operator, $thresholdValue);
    mysqli_stmt_execute($stmt);

    $newCustomAlarmId = '';
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $newCustomAlarmId = mysqli_insert_id($conn);

		$sql = "INSERT INTO customAlarmRecipients (customAlarmId, userId) VALUES (?, ?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $customAlarmId, $userId);
    
        foreach ($recipientIds as $recipient) {
			echo $newCustomAlarmId;
            $customAlarmId = $newCustomAlarmId;
			$userId = $recipient;
            mysqli_stmt_execute($stmt);
		}

		echo 'success';
        mysqli_stmt_close($stmt);
		
    }
} elseif ($function == 'loadSetAlarms') {
	$deviceId = $_POST['deviceId'];

	$sql = "
	SELECT customAlarms.customAlarmId, customAlarms.operator, customAlarms.thresholdValue, channels.channelId, channels.channelName
	FROM customAlarms
	LEFT JOIN channels ON customAlarms.channelId = channels.channelId
	WHERE customAlarms.deviceId = $deviceId;
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	$resultArray = array();
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {

			$sql2 = "
			SELECT users.userId, users.fullName
			FROM customAlarmRecipients
			LEFT JOIN users ON customAlarmRecipients.userId = users.userId
			WHERE customAlarmRecipients.customAlarmId = {$row['customAlarmId']}
			ORDER BY users.roleId ASC, users.fullName ASC;
			";

			$result2 = mysqli_query($conn, $sql2);
			$resultCheck2 = mysqli_num_rows($result2);
			
			$userList = array();
			if ($resultCheck2 > 0) {
				while ($row2 = mysqli_fetch_assoc($result2)) {
					$userList[] = $row2;
				}
			}
			$row['recipients'] = $userList;


			$resultArray[] = $row;
		}
	}
	
	echo json_encode($resultArray);
	exit();
} elseif ($function == 'deleteCustomAlarm') {
	$customAlarmId = $_POST['customAlarmId'];
	
	$sql = "
	DELETE FROM customAlarms WHERE customAlarmId = $customAlarmId;
	";
	
	if (mysqli_query($conn, $sql)) {
		echo "Record deleted successfully";
	} else {
		echo "Error deleting record: " . mysqli_error($conn);
	}
	
	mysqli_close($conn);
	exit();
} elseif ($function == 'loadOverviewData') {
	$deviceId = $_POST['deviceId'];

	$resultArray = array();
	// Get the number of AI channels
	$numberOfAI = '';
	$sql = "
	SELECT COUNT(*) as numberOfAI FROM channels WHERE channelType = 'AI' AND deviceId = $deviceId
	";
	
	$result = mysqli_query($conn, $sql);
	$resultCheck = mysqli_num_rows($result);
	
	if ($resultCheck > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$resultArray['numberOfAI'] = $row['numberOfAI'];
			$numberOfAI = $row['numberOfAI'];
		}
	}

	// Get latest message from DI channel
	$sql2 = "
	SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmTime
	FROM smsAlarms
	LEFT JOIN channels ON smsAlarms.deviceId = channels.deviceId AND channels.channelType = 'DI'
	WHERE smsAlarms.deviceId = $deviceId 
	ORDER BY smsAlarms.smsAlarmId DESC
	LIMIT 1;
	";
	
	$result2 = mysqli_query($conn, $sql2);
	$resultCheck2 = mysqli_num_rows($result2);
	
	if ($resultCheck2 > 0) {
		while ($row = mysqli_fetch_assoc($result2)) {
			$resultArray['probeStatus'] = $row;
		}
	}

	// Get latest reading from each AI channel
	$sql3 = "
	SELECT measurements.measurement, measurements.measurementTime, channels.channelName
	FROM measurements
	LEFT JOIN channels ON measurements.channelId = channels.channelId
	WHERE measurements.deviceId = $deviceId
	ORDER BY measurements.measurementId DESC
	LIMIT $numberOfAI;
	";
	
	$result3 = mysqli_query($conn, $sql3);
	$resultCheck3 = mysqli_num_rows($result3);
	$readings = array();

	if ($resultCheck3 > 0) {
		while ($row = mysqli_fetch_assoc($result3)) {
			$readings[] = $row;
		}
		$resultArray['latestMeasurements'] = $readings;
	}
	
	echo json_encode($resultArray);
	exit();
}

?>