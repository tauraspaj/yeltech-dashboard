<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

switch ($_POST['function']) {
	// ! Load product data
	case 'loadProductData':
		$deviceId = $_POST['deviceId'];		
		$sql = "
		SELECT devices.productId as productId, products.productName as productName
		FROM devices 
		LEFT JOIN products ON devices.productId = products.productId
		WHERE deviceId = $deviceId
		";

		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				echo json_encode($row);
			}
		}
		break;
	// ! End of product data

	// ! Get latest readings
	case 'rtmu_getLatestReadings':
		$deviceId = $_POST['deviceId'];
		$return = array();

		// Get the number of AI channels
		$numberOfAI = '';
		$sql = "SELECT COUNT(*) as numberOfAI FROM channels WHERE channelType = 'AI' AND deviceId = $deviceId";
		$result = mysqli_query($conn, $sql);		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return['numberOfAI'] = $row['numberOfAI'];
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
		if (mysqli_num_rows($result2) > 0) {
			while ($row = mysqli_fetch_assoc($result2)) {
				$return['probeStatus'] = $row;
			}
		} else {
			$return['probeStatus'] = 'Undefined';
		}
		
		// Get latest reading from AI channels
		$sql3 = "
		SELECT measurements.measurement, measurements.measurementTime, channels.channelName
		FROM measurements
		LEFT JOIN channels ON measurements.channelId = channels.channelId
		WHERE measurements.deviceId = $deviceId
		ORDER BY measurements.measurementId DESC
		LIMIT $numberOfAI;
		";
		$readings = array();
		$result3 = mysqli_query($conn, $sql3);
		if (mysqli_num_rows($result3) > 0) {
			while ($row = mysqli_fetch_assoc($result3)) {
				$readings[] = $row;
			}
			$return['latestMeasurements'] = $readings;
		} else {
			$return['latestMeasurements'] = 'Undefined';
		}

		$sql4 = "SELECT devices.deviceStatus FROM devices WHERE devices.deviceId = $deviceId";
		$result4 = mysqli_query($conn, $sql4);
		if ( mysqli_num_rows($result4) > 0 ) {
			while ($row = mysqli_fetch_assoc($result4)) {
				$return['deviceStatus'] = $row['deviceStatus'];
			}
		}
		
		echo json_encode($return);
		break;
	// ! End of latest readings

	// ! Get device coordinates
	case 'getDeviceCoordinates':
		$deviceId = $_POST['deviceId'];
		$return = array();

		$sql = "
		SELECT smsStatus.latitude, smsStatus.longitude 
		FROM smsStatus 
		WHERE ((latitude IS NOT NULL AND longitude IS NOT NULL) AND (deviceId = {$deviceId})) 
		ORDER BY smsStatusId DESC LIMIT 1;
		";
		
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return['latitude'] = $row['latitude'];
				$return['longitude'] = $row['longitude'];
			}
		} else {
			$return = 'Error';
		}
		
		echo json_encode($return);
		break;
	// ! End of device coordinates

	// ! Show alarms
	case 'loadTable_alarms':
		$alarmsPerPage = $_POST['alarmsPerPage'];
		$offset = $_POST['offset'];
		$deviceId = $_POST['deviceId'];
		$return = array();
		
		$sql = "
		SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmReading, smsAlarms.smsAlarmTime, smsAlarms.isAcknowledged, channels.channelName as channelName
		FROM smsAlarms
		LEFT JOIN channels ON smsAlarms.channelId = channels.channelId
		WHERE smsAlarms.deviceId = $deviceId
		ORDER BY smsAlarms.smsAlarmTime DESC
		LIMIT $alarmsPerPage OFFSET $offset
		";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		}
		
		
		$sqlTotal = "
		SELECT COUNT(*) as totalRows FROM smsAlarms WHERE smsAlarms.deviceId = $deviceId
		";
		$result2 = mysqli_query($conn, $sqlTotal);
		if (mysqli_num_rows($result2) > 0) {
			while ($row = mysqli_fetch_assoc($result2)) {
				$return[] = $row;
			}
		}
		
		echo json_encode($return);
		break;
	// ! End of alarms

	// ! Get readings
	case 'getDatasets':
		$deviceId = $_POST['deviceId'];
		$dateFrom = $_POST['dateFrom'];
		$dateTo = $_POST['dateTo'];

		$return = array();

		$sql = "
		SELECT channels.channelId, channels.channelName
		FROM channels
		WHERE deviceId = $deviceId && channelType = 'AI'
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				
				$sql2 = "
				SELECT measurements.measurement, measurements.measurementTime
				FROM measurements
				WHERE (deviceId = $deviceId AND channelId = {$row['channelId']}) AND (measurements.measurementTime BETWEEN '$dateFrom' AND '$dateTo')
				";
				$result2 = mysqli_query($conn, $sql2);
				$measurementsData = array();
				if ( mysqli_num_rows($result2) > 0 ) {
					while ($row2 = mysqli_fetch_assoc($result2)) {
						$measurementsData[] = $row2;
					}
				}
				$row['data'] = $measurementsData;
				$return[] = $row;
			}
		}

		echo json_encode($return);
		break;
	// ! End of readings
	
	// ! Get alarm triggers
	case 'getAlarmTriggers':
		$deviceId = $_POST['deviceId'];
		$return = array();

		// Find device name
		$sql = "
		SELECT deviceName FROM devices WHERE deviceId = $deviceId
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return['deviceName'] = $row['deviceName'];
			}
		}

		$sql2 = "
		SELECT channels.channelId, channels.channelName, units.unitName, channels.channelType
		FROM channels
		LEFT JOIN units ON channels.unitId = units.unitId
		WHERE channels.deviceId = $deviceId
		";
		$result2 = mysqli_query($conn, $sql2);
		if ( mysqli_num_rows($result2) > 0 ) {
			$channelsArr = array();
			while ($row = mysqli_fetch_assoc($result2)) {
				$channelsArr[] = $row;
			}
			$return['channels'] = $channelsArr;
		} else {
			$return['channels'] = 'EMPTY';
		}

		$sql3 = "
		SELECT alarmTriggers.triggerId, alarmTriggers.operator, alarmTriggers.thresholdValue, channels.channelId, channels.channelName
		FROM alarmTriggers
		LEFT JOIN channels ON alarmTriggers.channelId = channels.channelId
		WHERE alarmTriggers.deviceId = $deviceId
		";

		$result3 = mysqli_query($conn, $sql3);
		if ( mysqli_num_rows($result3) > 0 ) {
			$alarmTriggersArr = array();
			while ($row = mysqli_fetch_assoc($result3)) {
				$alarmTriggersArr[] = $row;
			}
			$return['alarmTriggers'] = $alarmTriggersArr;
		} else {
			$return['alarmTriggers'] = 'EMPTY';
		}
		echo json_encode($return);
		break;
	// ! End of alarm triggers
	
	// ! Register new trigger
	case 'registerNewTrigger':
		$deviceId = $_POST['deviceId'];
		$channelId = $_POST['channelId'];
		$operator = $_POST['operator'];
		$thresholdValue = $_POST['thresholdValue'];

		$sql = "INSERT INTO alarmTriggers (channelId, deviceId, operator, thresholdValue) VALUES (?, ?, ?, ?);";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ssss", $channelId, $deviceId, $operator, $thresholdValue);
		if (mysqli_stmt_execute($stmt)) {
			echo 'SUCCESS';
		};
        mysqli_stmt_close($stmt);
		break;
	// ! End of new trigger reg

	// ! Delete trigger
	case 'deleteTrigger':
		$deviceId = $_POST['deviceId'];
		$triggerId = $_POST['triggerId'];
		$sql = "
		DELETE FROM alarmTriggers WHERE triggerId = $triggerId;
		";
		
		if (mysqli_query($conn, $sql)) {
			echo "SUCCESS";
		} else {
			echo "ERROR";
		}
		
		mysqli_close($conn);
		break;
	// ! End of trigger delete

	// ! Get all users
	case 'getAllUsers':
		$return = array();

		if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
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
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		} else {
			$return = 'Error';
		}
		
		echo json_encode($return);
		break;
	// ! End of all users

	// ! Get device recipients
	case 'getRecipients':
		$deviceId = $_POST['deviceId'];
		$return = array();

		$sql = "
		SELECT alarmRecipients.userId, alarmRecipients.alarmRecipientId, users.fullName, users.email
		FROM alarmRecipients
		LEFT JOIN users ON alarmRecipients.userId = users.userId
		WHERE deviceId = $deviceId
		ORDER BY users.roleId ASC, users.fullName ASC
		";
		
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		} else {
			$return = 'ERROR';
		}
		
		echo json_encode($return);
		break;
	// ! End of recipients

	// ! Add new recipient
	case 'addNewRecipient':
		$deviceId = $_POST['deviceId'];
		$userId = $_POST['userId'];

		$sql = "INSERT INTO alarmRecipients (userId, deviceId) VALUES (?, ?);";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ss", $userId, $deviceId);
		if (mysqli_stmt_execute($stmt)) {
			echo 'SUCCESS';
		};
        mysqli_stmt_close($stmt);
		break;
	// ! End of new recipient

	// ! Delete recipient
	case 'deleteRecipient':
		$deviceId = $_POST['deviceId'];
		$recipientId = $_POST['recipientId'];

		$sql = "DELETE FROM alarmRecipients WHERE alarmRecipients.alarmRecipientId = '$recipientId'";

		if (mysqli_query($conn, $sql)) {
			echo "SUCCESS";
		} else {
			echo "ERROR";
		}
		break;
	// ! End of delete recipient
	default:
		// 
}
exit();




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