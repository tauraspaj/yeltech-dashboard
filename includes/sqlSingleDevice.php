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

		// Get the AI channels
		$aiChannels = array();
		$sql = "SELECT channelId, channelName, unitId FROM channels WHERE channelType = 'AI' AND deviceId = $deviceId ORDER BY channelId ASC";
		// $sql = "SELECT COUNT(*) as numberOfAI FROM channels WHERE channelType = 'AI' AND deviceId = $deviceId";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			$k = 0;		
			while ($row = mysqli_fetch_assoc($result)) {
				$sql3 = "
				SELECT measurements.measurement, measurements.measurementTime, units.unitName
				FROM measurements
				LEFT JOIN units ON units.unitId = {$row['unitId']}
				WHERE measurements.channelId = {$row['channelId']}
				ORDER BY measurements.measurementTime DESC
				LIMIT 1
				";

				$readings = array();

				$result3 = mysqli_query($conn, $sql3);
				if (mysqli_num_rows($result3) > 0) {
					while ($row3 = mysqli_fetch_assoc($result3)) {
						$row += $row3;
					}
					$return['latestMeasurements'][$k] = $row;
				} else {
					$return['latestMeasurements'][$k] = null;
				}
				$k++;
			}
			$numberOfAI = count($aiChannels);
			$return['numberOfAI'] = $k;
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

		$sql4 = "SELECT devices.deviceStatus FROM devices WHERE devices.deviceId = $deviceId";
		$result4 = mysqli_query($conn, $sql4);
		if ( mysqli_num_rows($result4) > 0 ) {
			while ($row = mysqli_fetch_assoc($result4)) {
				$return['deviceStatus'] = $row['deviceStatus'];
			}
		}

		$sql5 = "
			SELECT COUNT(historyId) AS numberOfTriggeredAlarms 
			FROM triggeredAlarmsHistory 
			WHERE deviceId = $deviceId
		";
		$result5 = mysqli_query($conn, $sql5);
		if ( mysqli_num_rows($result5) > 0 ) {
			while ($row = mysqli_fetch_assoc($result5)) {
				$return['numberOfTriggeredAlarms'] = $row['numberOfTriggeredAlarms'];
			}
		}

		$sql6 = "
			SELECT clearedAt
			FROM triggeredAlarmsHistory 
			WHERE deviceId = $deviceId
            ORDER BY clearedAt DESC 
            LIMIT 1
		";
		$result6 = mysqli_query($conn, $sql6);
		if ( mysqli_num_rows($result6) > 0 ) {
			while ($row = mysqli_fetch_assoc($result6)) {
				$return['latestAlarmSent'] = $row['clearedAt'];
			}
		} else {
			$return['latestAlarmSent'] = '';
		}
		
		echo json_encode($return);
		break;
	// ! End of latest readings

	// ! Get triggered alarms
	case 'getTriggeredAlarms':
		$deviceId = $_POST['deviceId'];
		$return = array();

		$sql = "
			SELECT channels.channelName, alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.triggerId, units.unitName
			FROM alarmTriggers
			LEFT JOIN channels ON alarmTriggers.channelId = channels.channelId
			LEFT JOIN units ON channels.unitId = units.unitId
			WHERE alarmTriggers.deviceId = $deviceId AND alarmTriggers.isTriggered = 1;
		";
		
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		}
		
		echo json_encode($return);
		break;
	// ! End of triggered alarms

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
			SELECT 'smsAlarm' AS type, channels.channelName AS channelName, smsAlarms.smsAlarmHeader AS msg1, smsAlarms.smsAlarmReading AS msg2, units.unitName AS unit, smsAlarms.smsAlarmTime AS timestampCol
			FROM smsAlarms
			LEFT JOIN channels ON smsAlarms.channelId = channels.channelId
			LEFT JOIN units ON channels.unitId = units.unitId
			WHERE smsAlarms.deviceId = $deviceId

			UNION

			SELECT 'triggeredHistory' AS type, triggeredAlarmsHistory.channelName AS channelName, triggeredAlarmsHistory.operator AS msg1, triggeredAlarmsHistory.thresholdValue AS msg2, triggeredAlarmsHistory.unitName AS unit,triggeredAlarmsHistory.clearedAt AS timestampCol
			FROM triggeredAlarmsHistory
			WHERE deviceId = $deviceId

			UNION

			SELECT 'smsStatus' AS type, 'DEVICE' AS channelName, smsStatus.smsStatus AS msg1, null AS msg2, null AS unit, smsStatus.smsStatusTime AS timestampCol
			FROM smsStatus
			WHERE smsStatus.deviceId = $deviceId

			ORDER BY timestampCol DESC
			LIMIT $alarmsPerPage OFFSET $offset;
		";

		$alarmHistory = array();

		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$alarmHistory[] = $row;
			}
		}
		$return['alarmHistory'] = $alarmHistory;
		
		$sqlTotalSms = "
			SELECT COUNT(*) as totalRows FROM smsAlarms WHERE smsAlarms.deviceId = $deviceId
		";
		$result = mysqli_query($conn, $sqlTotalSms);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$smsAlarmsCount = $row['totalRows'];
			}
		}

		$totalTriggers = "
			SELECT COUNT(*) as totalRows 
			FROM triggeredAlarmsHistory 
   			WHERE deviceId = $deviceId
		";
		$result = mysqli_query($conn, $totalTriggers);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$triggersHistoryCount = $row['totalRows'];
			}
		}

		$totalTriggers = "
			SELECT COUNT(*) as totalRows 
			FROM smsStatus 
   			WHERE smsStatus.deviceId = $deviceId
		";
		$result = mysqli_query($conn, $totalTriggers);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$smsStatusCount = $row['totalRows'];
			}
		}

		$return['totalCount'] = $smsAlarmsCount + $triggersHistoryCount + $smsStatusCount;
		
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
				ORDER BY measurements.measurementTime ASC
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
		$alarmDescription = $_POST['alarmDescription'];

		$sql = "INSERT INTO alarmTriggers (channelId, deviceId, operator, thresholdValue, alarmDescription) VALUES (?, ?, ?, ?, ?);";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sssss", $channelId, $deviceId, $operator, $thresholdValue, $alarmDescription);
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

	// ! Load measurements log
	case 'loadTable_measurements':
		$measurementsPerPage = $_POST['measurementsPerPage'];
		$offset = $_POST['offset'];
		$deviceId = $_POST['deviceId'];
		$return = array();
		
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
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		}
		
		
		$sqlTotal = "
		SELECT COUNT(*) as totalRows FROM measurements WHERE measurements.deviceId = $deviceId
		";
		
		$result = mysqli_query($conn, $sqlTotal);
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		}
		
		echo json_encode($return);
		exit();
	// ! End of measurements log

	// ! Get device data
	case 'getDeviceData':
		$return = array();
		$deviceId = $_POST['deviceId'];
		$sql = "
		SELECT devices.deviceId, devices.groupId, devices.deviceName, devices.deviceStatus, devices.deviceAlias, devices.customLocation, devices.devicePhone, devices.createdAt, devices.lastCalibration, devices.nextCalibrationDue, devices.latitude, devices.longitude, deviceTypes.deviceTypeName, products.productName, subscriptions.subStart, subscriptions.subFinish, `groups`.groupName
		FROM devices
		LEFT JOIN deviceTypes ON devices.deviceTypeId = deviceTypes.deviceTypeId
		LEFT JOIN products ON devices.productId = products.productId
		LEFT JOIN subscriptions ON devices.deviceId = subscriptions.deviceId
		LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
		WHERE devices.deviceId = $deviceId
		LIMIT 1
		";

		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$deviceChannels = array();
				$sql2 = "
				SELECT channels.channelId, channels.channelName
				FROM channels
				WHERE channels.deviceId = $deviceId
				";
				$result2 = mysqli_query($conn, $sql2);
				if ( mysqli_num_rows($result2) > 0 ) {
					while ($row2 = mysqli_fetch_assoc($result2)) {
						$deviceChannels[] = $row2;
					}
				}
				$row['channels'] = $deviceChannels;
				echo json_encode($row);
			}
		}
		exit();
	// ! End of device data
	
	// ! Update device status
	case 'updateDeviceStatus':
		$deviceId = $_POST['deviceId'];
		$newStatus = $_POST['newStatus'];

		$sql = "
		UPDATE devices SET deviceStatus = $newStatus WHERE deviceId = $deviceId
		";
		if ( mysqli_query($conn, $sql) ) {
			echo 'SUCCESS';
		} else {
			echo 'ERROR';
		}
		break;
	// ! End of device status

	// ! Update device customs
	case 'updateCustoms':
		$deviceId = $_POST['deviceId'];
		$deviceAlias = $_POST['deviceAlias'];
		$customLocation = $_POST['customLocation'];
		$latitude = $_POST['latitude'];
		$longitude = $_POST['longitude'];

		if ($deviceAlias == '') { $deviceAlias = null; }
		if ($customLocation == '') { $customLocation = null; }

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
					break;
				}
			} else {
				$response['status'] = 'Error';
				$response['message'] = 'Invalid coordinates';
				echo json_encode($response);
				break;
			}
		}
		

		$response = array();
		
		$sql = "UPDATE devices SET deviceAlias=?, customLocation=?, latitude=?, longitude=? WHERE deviceId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sssss", $deviceAlias, $customLocation, $latitude, $longitude, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 'Error';
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 'OK';
		}
        mysqli_stmt_close($stmt);

		echo json_encode($response);
		break;
	// ! End of device customs

	// ! Update device customs
	case 'updateMaintainDates':
		$deviceId = $_POST['deviceId'];
		$subStart = $_POST['subStart'];
		$subFinish = $_POST['subFinish'];
		$lastCalibration = $_POST['lastCal'];
		$nextCalibrationDue = $_POST['nextCal'];

		if ($subStart == '0000-00-00' || $subStart == '') { $subStart = null; }
		if ($subFinish == '0000-00-00' || $subFinish == '') { $subFinish = null; }
		if ($lastCalibration == '0000-00-00' || $lastCalibration == '') { $lastCalibration = null; }
		if ($nextCalibrationDue == '0000-00-00' || $nextCalibrationDue == '') { $nextCalibrationDue = null; }

		$response = array();
		
		$sql = "UPDATE subscriptions SET subStart=?, subFinish=? WHERE deviceId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sss", $subStart, $subFinish, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 'Error';
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 'OK';
		}

		$sql = "UPDATE devices SET lastCalibration=?, nextCalibrationDue=? WHERE deviceId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sss", $lastCalibration, $nextCalibrationDue, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 'Error';
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 'OK';
		}


        mysqli_stmt_close($stmt);

		echo json_encode($response);
		break;
	// ! End of device customs

	// ! Return session role id
	case 'getRoleId':
		echo $_SESSION['roleId'];
		break;
	// ! End of session role id

	default: break;
		// 
}
exit();

?>