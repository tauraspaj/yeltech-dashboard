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
		SELECT devices.latitude AS deviceLatitude, devices.longitude AS deviceLongitude, `groups`.latitude AS groupLatitude, `groups`.longitude AS groupLongitude
		FROM devices
		LEFT JOIN `groups` on devices.groupId = `groups`.groupId
		WHERE deviceId = $deviceId
		";
		
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return['deviceLatitude'] = $row['deviceLatitude'];
				$return['deviceLongitude'] = $row['deviceLongitude'];
				$return['groupLatitude'] = $row['groupLatitude'];
				$return['groupLongitude'] = $row['groupLongitude'];
			}
		}
		
		echo json_encode($return);
		break;
	// ! End of device coordinates

	// ! Show alarms
	case 'loadTable_alarms':
		$alarmsPerPage = $_POST['alarmsPerPage'];
		$offset = $_POST['offset'];
		$deviceId = $_POST['deviceId'];
		$fromDate = $_POST['fromDate'];
		$toDate = $_POST['toDate'];

		if ($fromDate != null && $toDate != null) {
			$smsAlarmBetween = "AND (smsAlarms.smsAlarmTime BETWEEN '$fromDate' AND '$toDate')";
			$triggeredHistoryBetween = "AND (triggeredAlarmsHistory.clearedAt BETWEEN '$fromDate' AND '$toDate')";
			$smsStatusBetween = "AND (smsStatus.smsStatusTime BETWEEN '$fromDate' AND '$toDate')";
		} else {
			$smsAlarmBetween = '';
			$triggeredHistoryBetween = '';
			$smsStatusBetween = '';
		}

		$return = array();
		
		$sql = "
			SELECT 'smsAlarm' AS type, channels.channelName AS channelName, smsAlarms.smsAlarmHeader AS msg1, smsAlarms.smsAlarmReading AS msg2, null AS msg3, units.unitName AS unit, smsAlarms.smsAlarmTime AS timestampCol
			FROM smsAlarms
			LEFT JOIN channels ON smsAlarms.channelId = channels.channelId
			LEFT JOIN units ON channels.unitId = units.unitId
			WHERE smsAlarms.deviceId = $deviceId $smsAlarmBetween

			UNION

			SELECT 'triggeredHistory' AS type, triggeredAlarmsHistory.channelName AS channelName, triggeredAlarmsHistory.operator AS msg1, triggeredAlarmsHistory.thresholdValue AS msg2, triggeredAlarmsHistory.alarmDescription AS msg3, triggeredAlarmsHistory.unitName AS unit,triggeredAlarmsHistory.clearedAt AS timestampCol
			FROM triggeredAlarmsHistory
			WHERE deviceId = $deviceId $triggeredHistoryBetween

			UNION

			SELECT 'smsStatus' AS type, 'DEVICE' AS channelName, smsStatus.smsStatus AS msg1, null AS msg2, null AS msg3, null AS unit, smsStatus.smsStatusTime AS timestampCol
			FROM smsStatus
			WHERE smsStatus.deviceId = $deviceId $smsStatusBetween

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
			SELECT COUNT(*) as totalRows FROM smsAlarms WHERE smsAlarms.deviceId = $deviceId $smsAlarmBetween
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
   			WHERE deviceId = $deviceId $triggeredHistoryBetween
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
   			WHERE smsStatus.deviceId = $deviceId $smsStatusBetween
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
		SELECT alarmTriggers.triggerId, alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.alarmDescription, channels.channelId, channels.channelName, units.unitName
		FROM alarmTriggers
		LEFT JOIN channels ON alarmTriggers.channelId = channels.channelId
		LEFT JOIN units ON channels.unitId = units.unitId
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
		
		$fromDate = $_POST['fromDate'];
		$toDate = $_POST['toDate'];

		if ($fromDate != null && $toDate != null) {
			$between = "AND (measurements.measurementTime BETWEEN '$fromDate' AND '$toDate')";
		} else {
			$between = '';
		}

		// echo $between;
		
		$return = array();
		
		$sql = "
		SELECT measurements.measurement, channels.channelName as channelName, measurements.measurementTime, units.unitName as unitName
		FROM measurements
		LEFT JOIN channels ON measurements.channelId = channels.channelId
		LEFT JOIN units ON (measurements.deviceId = channels.deviceId AND channels.unitId = units.unitId)
		WHERE measurements.deviceId = $deviceId $between
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
		SELECT COUNT(*) as totalRows FROM measurements WHERE measurements.deviceId = $deviceId $between
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
		SELECT devices.deviceId, devices.groupId, devices.deviceName, devices.deviceStatus, devices.deviceAlias, devices.customLocation, devices.devicePhone, devices.createdAt, devices.createdBy, devices.lastCalibration, devices.nextCalibrationDue, devices.latitude, devices.longitude, devices.deviceTypeId, deviceTypes.deviceTypeName, devices.productId, products.productName, subscriptions.subStart, subscriptions.subFinish, `groups`.groupName
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
				SELECT channels.channelId, channels.channelName, channels.channelType, units.unitName
				FROM channels
				LEFT JOIN units ON channels.unitId = units.unitId
				WHERE channels.deviceId = $deviceId
				ORDER BY channelId
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
		break;
	// ! End of device data
	
	// ! Update device status
	case 'updateDeviceStatus':
		$deviceId = $_POST['deviceId'];
		$newStatus = $_POST['newStatus'];

		$sql = "
		UPDATE devices SET deviceStatus = $newStatus WHERE deviceId = $deviceId
		";
		mysqli_query($conn, $sql);

		if ($newStatus == 0) {
			$sql = "
				UPDATE alarmTriggers SET isTriggered = 0 WHERE deviceId = $deviceId AND isTriggered = 1;
			";
			mysqli_query($conn, $sql);
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

	// ! Return all registered units
	case 'getUnits':
		$sql = "
		SELECT unitId, unitName FROM units
		";
		$result = mysqli_query($conn, $sql);
		$return = array();
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$return[] = $row;
			}
		}
		echo json_encode($return);
		break;
	// ! End of units

	// ! Delete channel
	case 'deleteChannel':
		$channelId = $_POST['channelId'];
		$sql = "DELETE FROM channels WHERE channelId = $channelId;";
		mysqli_query($conn, $sql);
		echo 'This channel has been deleted';
		break;
	// ! End of delete channel

	// ! Create channel
	case 'createChannel':
		$deviceId = $_POST['deviceId'];
		$channelType = $_POST['channelType'];
		$channelName = $_POST['channelName'];
		$unitId = $_POST['unitId'];

		if ($channelType == 'DI') {
			$unitId = null;
		}

		if ($channelType == null) {
			$response['status'] = '422';
			$response['message'] = 'Channel type cannot be empty!';
			exit();
		}

		if ($channelName == '') {
			$response['status'] = '422';
			$response['message'] = 'Invalid channel name';
			exit();
		}	

		$sql = "INSERT INTO channels (channelName, unitId, deviceId, channelType) VALUES (?, ?, ?, ?);";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ssss", $channelName, $unitId, $deviceId, $channelType);
		if (mysqli_stmt_execute($stmt)) {
			$response['status'] = 200;
			$response['message'] = 'Channel added succesfully!';
		} else {
			$response['status'] = 500;
			$response['message'] = 'Something went wrong!';
		}
        mysqli_stmt_close($stmt);

		echo json_encode($response);
		break;
	// ! End of create channel

	// ! Update device data
	case 'updateDeviceData':
		$deviceId = $_POST['deviceId'];
		$deviceName = $_POST['deviceName'];
		$devicePhone = $_POST['devicePhone'];
		$groupId = $_POST['groupId'];
		$productId = $_POST['productId'];
		$deviceTypeId = $_POST['deviceTypeId'];

		$response = array();

		if ( !preg_match('/^(\+)[0-9]+$/', $devicePhone) ) {
			$response['status'] = 500;
			$response['message'] = 'Invalid device number!';
			echo json_encode($response);
			exit();
		}
		
		$sql = "UPDATE devices SET deviceName=?, devicePhone=?, groupId=?, productId=?, deviceTypeId=? WHERE deviceId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ssssss", $deviceName, $devicePhone, $groupId, $productId, $deviceTypeId, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 500;
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 200;
			$response['message'] = 'Device updated!';
		}
		
		echo json_encode($response);
		break;
	// ! End of device data

	// ! Delete device
	case 'deleteDevice':
		$sql = "
		SELECT groupId FROM `groups` WHERE groupName = 'DELETED DEVICES'
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$groupId = $row['groupId'];
			}
		} else {
			$groupId = 1;
		}

		$deviceId = $_POST['deviceId'];
		$sql = "
		UPDATE devices SET groupId=$groupId WHERE deviceId=$deviceId
		";
		mysqli_query($conn, $sql);
		break;
	// ! End of delete device

	// ! Update device data
	case 'getEWBChannels':
		$deviceId = $_POST['deviceId'];
		$response = array();

		// First find the status of the Digital (EWB Board) channel
		$sql = "
		SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmTime, channels.channelName
		FROM smsAlarms
		LEFT JOIN channels on smsAlarms.channelId = channels.channelId
		WHERE smsAlarms.deviceId = $deviceId AND channels.channelType='DI'
		ORDER BY smsAlarmTime DESC
		LIMIT 1;
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$response['DI'] = $row;
			}
		} else {
			$response['DI'] = null;
		}

		$sql2 = "
		SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmTime, smsAlarms.smsAlarmReading, channels.channelName, units.unitName
		FROM smsAlarms
		LEFT JOIN channels ON smsAlarms.channelId = channels.channelId
		LEFT JOIN units ON channels.unitId = units.unitId
		WHERE smsAlarms.deviceId = $deviceId AND channels.channelType='AI'
		ORDER BY smsAlarmTime DESC
		LIMIT 1;
		";
		$result2 = mysqli_query($conn, $sql2);
		if ( mysqli_num_rows($result2) > 0 ) {
			while ($row = mysqli_fetch_assoc($result2)) {
				$response['AI'] = $row;
			}
		} else {
			$response['AI'] = null;
		}

		echo json_encode($response);
		break;
	// ! End of EWB channel data

	// ! Get EWB message history
	case 'loadTable_messageHistory':
		$messageHistoryPerPage = $_POST['messageHistoryPerPage'];
		$offset = $_POST['offset'];
		$deviceId = $_POST['deviceId'];
		$fromDate = $_POST['fromDate'];
		$toDate = $_POST['toDate'];

		if ($fromDate != null && $toDate != null) {
			$messageHistoryBetween = "AND (messages.timeSent BETWEEN '$fromDate' AND '$toDate')";
		} else {
			$messageHistoryBetween = '';
		}

		$return = array();
		
		$sql = "
		SELECT messages.fromNumber, messages.toNumber, messages.textBody, messages.messageType, messages.timeSent
		FROM messages
		LEFT JOIN devices ON messages.fromNumber = devices.devicePhone
		WHERE devices.deviceId = $deviceId $messageHistoryBetween
		ORDER BY timeSent DESC
		LIMIT $messageHistoryPerPage OFFSET $offset;
		";
		
		$messageHistory = array();

		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$messageHistory[] = $row;
			}
		}
		$return['messageHistory'] = $messageHistory;
		
		$sqlTotalMessages = "
			SELECT COUNT(*) as totalRows FROM messages
			LEFT JOIN devices ON messages.fromNumber = devices.devicePhone
			WHERE devices.deviceId = $deviceId $messageHistoryBetween
		";
		$result = mysqli_query($conn, $sqlTotalMessages);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$messageHistoryCount = $row['totalRows'];
			}
		}

		$return['totalCount'] = $messageHistoryCount;
		
		echo json_encode($return);
		break;
	// ! End of EWB message history

	// ! EWB V2 - Get latest readings
	case 'ewbv2_getLatestReadings':
		$deviceId = $_POST['deviceId'];
		$response = array();

		// First find the status of the Digital (EWB Board) channel
		$sql = "
		SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmTime, channels.channelName
		FROM smsAlarms
		LEFT JOIN channels on smsAlarms.channelId = channels.channelId
		WHERE smsAlarms.deviceId = $deviceId AND channels.channelType='DI'
		ORDER BY smsAlarmTime DESC
		LIMIT 1;
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$response['DI']['alarm'] = $row;
			}
		} else {
			$response['DI']['alarm'] = null;
		}

		// Loop through each analog channel
		$sql = "SELECT ROW_NUMBER() OVER() AS num_row, channels.channelId, channels.channelName, units.unitName 
		FROM channels
		LEFT JOIN units ON channels.unitId = units.unitId
		WHERE channels.channelType = 'AI' AND channels.deviceId = $deviceId 
		ORDER BY channelId ASC";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			// JSON output will be as follows:
			// ai1 { thresholds[], reading[], alarm[] }
			// ai2 { thresholds[], reading[], alarm[] }
			while ($rowChannels = mysqli_fetch_assoc($result)) {
				$thisId = $rowChannels['channelId'];
				
				$thisRow = "AI{$rowChannels['num_row']}"; 
				$response[$thisRow] = array();
				$response[$thisRow]['unit'] = $rowChannels['unitName'];
				$response[$thisRow]['channelName'] = $rowChannels['channelName'];

				// 1. Find thresholds
				$sqlThresholds = "
					SELECT alarmTriggers.triggerId, alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.isTriggered, alarmTriggers.alarmDescription
					FROM alarmTriggers
					WHERE channelId = $thisId
				";
				$resultThresholds = mysqli_query($conn, $sqlThresholds);
				if ( mysqli_num_rows($resultThresholds) > 0 ) {
					while ($rowThresholds = mysqli_fetch_assoc($resultThresholds)) {
						$response[$thisRow]['thresholds'][] = $rowThresholds;
					}
				} else {
					$response[$thisRow]['thresholds'] = null;
				}

				// 2. Find latest reading
				$sqlReading = "
					SELECT measurements.measurement, measurements.measurementTime
					FROM measurements
					WHERE measurements.channelId = $thisId
					ORDER BY measurements.measurementTime DESC
					LIMIT 1
				";
				$resultReading = mysqli_query($conn, $sqlReading);
				if ( mysqli_num_rows($resultReading) > 0 ) {
					while ($rowReading = mysqli_fetch_assoc($resultReading)) {
						$response[$thisRow]['reading'] = $rowReading;
					}
				} else {
					$response[$thisRow]['reading'] = null;
				}

				// 3. Find latest alarm message sent
				$sqlAlarm = "
					SELECT smsAlarms.smsAlarmHeader, smsAlarms.smsAlarmTime, smsAlarms.smsAlarmReading
					FROM smsAlarms
					WHERE smsAlarms.channelId = $thisId
					ORDER BY smsAlarmTime DESC
					LIMIT 1;
				";
				$resultAlarm  = mysqli_query($conn, $sqlAlarm );
				if ( mysqli_num_rows($resultAlarm ) > 0 ) {
					while ($rowAlarm  = mysqli_fetch_assoc($resultAlarm )) {
						$response[$thisRow]['alarm'] = $rowAlarm ;
					}
				} else {
					$response[$thisRow]['alarm'] = null;
				}
			}
		}
		

		echo json_encode($response);
		exit();

		// Get the AI channels
		$aiChannels = array();
		$sql = "SELECT channels.channelId, channels.channelName, units.unitName 
		FROM channels
		LEFT JOIN units ON channels.unitId = units.unitId
		WHERE channels.channelType = 'AI' AND channels.deviceId = $deviceId 
		ORDER BY channelId ASC";
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
				$return['ewbBoard'] = $row;
			}
		} else {
			$return['ewbBoard'] = 'Undefined';
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
	// ! End EWB V2 - Get latest readings

	// ! TILT - Get dashboard settings
	case 'getTiltDashboardSettings':
		$deviceId = $_POST['deviceId'];
		$response = array();

		$sql = "
		SELECT tiltDashboardSettings.imageURL, tiltDashboardSettings.horizontalBox_offset_top, tiltDashboardSettings.horizontalBox_offset_left, tiltDashboardSettings.horizontalBox_lt0_text, tiltDashboardSettings.horizontalBox_mt0_text, tiltDashboardSettings.horizontalBox_lt0_arrowDirection, tiltDashboardSettings.horizontalBox_mt0_arrowDirection, tiltDashboardSettings.verticalBox_offset_top, tiltDashboardSettings.verticalBox_offset_left, tiltDashboardSettings.verticalBox_lt0_text, tiltDashboardSettings.verticalBox_mt0_text, tiltDashboardSettings.verticalBox_lt0_arrowDirection, tiltDashboardSettings.verticalBox_mt0_arrowDirection
		FROM tiltDashboardSettings
		WHERE tiltDashboardSettings.deviceId = $deviceId
		LIMIT 1
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$response = $row;
			}
		} else {
			$response = null;
		}

		echo json_encode($response);
		break;
	// ! End of TILT - Get dashboard settings

	// ! TILT - Update tilt box position
	case 'updateTiltBoxPosition':
		$deviceId = $_POST['deviceId'];
		$pos_left = $_POST['pos_left'];
		$pos_top = $_POST['pos_top'];
		$direction = $_POST['direction'];

		if ($direction == 'horizontal') {
			$sql = "UPDATE tiltDashboardSettings SET horizontalBox_offset_top=?, horizontalBox_offset_left=? WHERE deviceId=?";
			$stmt = mysqli_stmt_init($conn);
			mysqli_stmt_prepare($stmt, $sql);
			mysqli_stmt_bind_param($stmt, "sss", $pos_top, $pos_left, $deviceId);
			mysqli_stmt_execute($stmt);
			if (mysqli_stmt_error($stmt)) {
				$response['status'] = 500;
				$response['message'] = mysqli_stmt_error($stmt);
			} else {
				$response['status'] = 200;
				$response['message'] = 'Device updated!';
			}
		} elseif ($direction == 'vertical') {
			$sql = "UPDATE tiltDashboardSettings SET verticalBox_offset_top=?, verticalBox_offset_left=? WHERE deviceId=?";
			$stmt = mysqli_stmt_init($conn);
			mysqli_stmt_prepare($stmt, $sql);
			mysqli_stmt_bind_param($stmt, "sss", $pos_top, $pos_left, $deviceId);
			mysqli_stmt_execute($stmt);
			if (mysqli_stmt_error($stmt)) {
				$response['status'] = 500;
				$response['message'] = mysqli_stmt_error($stmt);
			} else {
				$response['status'] = 200;
				$response['message'] = 'Device updated!';
			}
		} else {
			$response['status'] = 500;
			$response['message'] = 'Unknown direction!';
			echo json_encode($response);
			exit();
		}

		$response = array();
		
		echo json_encode($response);
		break;
	// ! TILT - End of update tilt box position
	
	// ! TILT - Update tilt settings
	case 'updateTiltSettings':
		$deviceId = $_POST['deviceId'];
		$imageURL = $_POST['imageURL'];

		$horiz_lt0_text = $_POST['horiz_lt0_text'];
		$horiz_mt0_text = $_POST['horiz_mt0_text'];

		if (isset($_POST['horiz_lt0_arrow'])) { $horiz_lt0_arrow = $_POST['horiz_lt0_arrow']; } else { $horiz_lt0_arrow = NULL; }
		if (isset($_POST['horiz_mt0_arrow'])) { $horiz_mt0_arrow = $_POST['horiz_mt0_arrow']; } else { $horiz_mt0_arrow = NULL; }
		
		$vert_lt0_text = $_POST['vert_lt0_text'];
		$vert_mt0_text = $_POST['vert_mt0_text'];
		
		if (isset($_POST['vert_lt0_arrow'])) { $vert_lt0_arrow = $_POST['vert_lt0_arrow']; } else { $vert_lt0_arrow = NULL; }
		if (isset($_POST['vert_mt0_arrow'])) { $vert_mt0_arrow = $_POST['vert_mt0_arrow']; } else { $vert_mt0_arrow = NULL; }

		if ($imageURL == '') { $imageURL = NULL; };
		if ($horiz_lt0_text == '') { $horiz_lt0_text = NULL; };
		if ($horiz_mt0_text == '') { $horiz_mt0_text = NULL; };

		if ($vert_lt0_text == '') { $vert_lt0_text = NULL; };
		if ($vert_mt0_text == '') { $vert_mt0_text = NULL; };

		$sql = "UPDATE tiltDashboardSettings SET imageURL=?, horizontalBox_lt0_text=?, horizontalBox_mt0_text=?, horizontalBox_lt0_arrowDirection=?, horizontalBox_mt0_arrowDirection=?, verticalBox_lt0_text=?, verticalBox_mt0_text=?, verticalBox_lt0_arrowDirection=?, verticalBox_mt0_arrowDirection=? WHERE deviceId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ssssssssss", $imageURL, $horiz_lt0_text, $horiz_mt0_text, $horiz_lt0_arrow, $horiz_mt0_arrow, $vert_lt0_text, $vert_mt0_text, $vert_lt0_arrow, $vert_mt0_arrow, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 500;
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 200;
			$response['message'] = 'Device updated!';

			echo json_encode($response);
			exit();
		}

		$response = array();
		
		echo json_encode($response);
		break;
	// ! TILT - End of update tilt settings

	// ! Get dashboard settings
	case 'getDashboardSettings':
		$deviceId = $_POST['deviceId'];
		$response = array();
		$response['imageURL'] = null;
		$response['box'] = array();

		// Get image URL
		$sql = "
			SELECT imageURL FROM dashboardImage WHERE deviceId = $deviceId LIMIT 1
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$response['imageURL'] = $row['imageURL'];
			}
		}
		
		// Get movable box positions
		$sql = "
			SELECT dashboardMovableBox.channelId, dashboardMovableBox.offset_top, dashboardMovableBox.offset_left, channels.channelName, units.unitName
			FROM dashboardMovableBox
			LEFT JOIN channels ON dashboardMovableBox.channelId = channels.channelId
			LEFT JOIN units ON channels.unitId = units.unitId
			WHERE dashboardMovableBox.deviceId = $deviceId
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($response['box'], $row);
			}
		}

		echo json_encode($response);
		break;
	// ! End of Get dashboard settings

	// ! Update movable box settings
	case 'updateMovableBoxPosition':
		$channelId = $_POST['channelId'];
		$pos_top = $_POST['pos_top'];
		$pos_left = $_POST['pos_left'];


		$sql = "UPDATE dashboardMovableBox SET offset_top=?, offset_left=? WHERE channelId=?";
		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sss", $pos_top, $pos_left, $channelId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 500;
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 200;
			$response['message'] = 'Device updated!';

			echo json_encode($response);
			exit();
		}

		$response = array();
		
		echo json_encode($response);
		break;
	// ! End of Update movable box settings

	// ! Update dashboard image
	case 'updateDashboardImage':
		$deviceId = $_POST['deviceId'];
		$imageURL = $_POST['imageURL'];

		if ($imageURL == '') { $imageURL = NULL; };

		$sql = "UPDATE dashboardImage SET imageURL=? WHERE deviceId=?";

		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "ss", $imageURL, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 500;
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 200;
			$response['message'] = 'Device updated!';

			echo json_encode($response);
			exit();
		}

		$response = array();
		
		echo json_encode($response);
		break;
	// ! End of update dashboard image

	// ! Update bwm formulas
	case 'updateBWMFormulas':
		$deviceId = $_POST['deviceId'];
		$formulaA = $_POST['formulaA'];
		$formulaB = $_POST['formulaB'];
		$formulaC = $_POST['formulaC'];
		$formulaD = $_POST['formulaD'];

		if ($formulaA == '') { $formulaA = NULL; };
		if ($formulaB == '') { $formulaB = NULL; };
		if ($formulaC == '') { $formulaC = NULL; };
		if ($formulaD == '') { $formulaD = NULL; };

		$sql = "UPDATE bwmFormulas SET formulaA=?, formulaB=?, formulaC=?, formulaD=? WHERE deviceId=?";

		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sssss", $formulaA, $formulaB, $formulaC, $formulaD, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 500;
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 200;
			$response['message'] = 'Formulas updated!';

			echo json_encode($response);
			exit();
		}

		$response = array();
		
		echo json_encode($response);
		break;
	// ! End of update bwm formulas

	// ! Get formulas
	case 'getBWMFormulas':
		$deviceId = $_POST['deviceId'];
		$response = array();
		$response['imageURL'] = null;
		$response['formulas'] = array();

		// Get image URL
		$sql = "
			SELECT imageURL FROM dashboardImage WHERE deviceId = $deviceId LIMIT 1
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				$response['imageURL'] = $row['imageURL'];
			}
		}
		
		// Get formulas
		$sql = "
			SELECT *
			FROM bwmFormulas
			WHERE bwmFormulas.deviceId = $deviceId
		";
		$result = mysqli_query($conn, $sql);
		if ( mysqli_num_rows($result) > 0 ) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($response['formulas'], $row);
			}
		}

		echo json_encode($response);
		break;
	// ! End of get formulas

	// ! Update BWM movable box settings
	case 'updateBWMMovableBoxPosition':
		$deviceId = $_POST['deviceId'];
		$formula = $_POST['formula'];
		$pos_top = $_POST['pos_top'];
		$pos_left = $_POST['pos_left'];


		switch ($formula) {
			case 'A':
				$sql = "UPDATE bwmFormulas SET formulaA_offset_top=?, formulaA_offset_left=? WHERE deviceId=?";
				break;
			case 'B':
				$sql = "UPDATE bwmFormulas SET formulaB_offset_top=?, formulaB_offset_left=? WHERE deviceId=?";
				break;
			case 'C':
				$sql = "UPDATE bwmFormulas SET formulaC_offset_top=?, formulaC_offset_left=? WHERE deviceId=?";
				break;
			case 'D':
				$sql = "UPDATE bwmFormulas SET formulaD_offset_top=?, formulaD_offset_left=? WHERE deviceId=?";
				break;
			default:
				break;				
		}

		$stmt = mysqli_stmt_init($conn);
		mysqli_stmt_prepare($stmt, $sql);
		mysqli_stmt_bind_param($stmt, "sss", $pos_top, $pos_left, $deviceId);
		mysqli_stmt_execute($stmt);
		if (mysqli_stmt_error($stmt)) {
			$response['status'] = 500;
			$response['message'] = mysqli_stmt_error($stmt);
		} else {
			$response['status'] = 200;
			$response['message'] = 'Device updated!';

			echo json_encode($response);
			exit();
		}

		$response = array();
		
		echo json_encode($response);
		break;
	// ! End of Update BWM movable box settings

	default: break;
		// 
}
exit();

?>