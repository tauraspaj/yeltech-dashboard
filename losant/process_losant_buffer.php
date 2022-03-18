<?php

/*
 * This file will be run every minute (CRON job)
 * All the records will be checked in losantReadingBuffer table
 * Messages will be processed
 * losantReadingBuffer table will then be cleared out
*/

// Connect database
require_once './../includes/dbh.inc.php';

// Include email sending
require_once './../messages/notifications.php';

$processedIdsArray = [];
$sql = "
SELECT losantReadingsBuffer.losantReadingsBufferId, losantReadingsBuffer.measurement, losantReadingsBuffer.measurementTime, losantReadingsBuffer.macId, deviceMACId.deviceId, channels.channelName, channels.channelId, units.unitName
FROM losantReadingsBuffer
LEFT JOIN deviceMACId ON losantReadingsBuffer.macId = deviceMACId.deviceMACId
LEFT JOIN channels ON UPPER(losantReadingsBuffer.channelName) = UPPER(channels.channelName) AND channels.deviceId = deviceMACId.deviceId
LEFT JOIN units ON channels.unitId = units.unitId
";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $losantReadingsBufferId = $row['losantReadingsBufferId'];
        $processedIdsArray[] = $losantReadingsBufferId;

        $deviceId = $row['deviceId'];
        $channelId = $row['channelId'];
        $measurement = $row['measurement'];
        $measurementTime = $row['measurementTime'];
        $macId = $row['macId'];
        $channelName = $row['channelName'];
        $unitName = $row['unitName'];

        // Turn device state on (1 for on and 0 for off)
        setDeviceStatus($deviceId, 1, $conn);

        if ($measurement != null) {
            // Check alarms and send out any notifications if needed
            checkAlarms($channelId, $measurement, $conn);


            // Insert into measurements
            addToMeasurements($channelId, $deviceId, $measurement, $measurementTime, $conn);
        }
    }
}


// Clear table once all readings have been processed
$idsList = implode(', ', $processedIdsArray);
$sqlDelete = "
DELETE FROM losantReadingsBuffer WHERE losantReadingsBufferId IN ($idsList)
";
mysqli_query($conn, $sqlDelete);


function setDeviceStatus($deviceId, $state, $conn) {
    $sql = "
    UPDATE devices SET deviceStatus = $state WHERE deviceId = $deviceId
    ";
    $result = mysqli_query($conn, $sql);
}

function checkAlarms($channelId, $measurement, $conn) {
    $sql = "
    SELECT * FROM alarmTriggers WHERE channelId = $channelId
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $triggerId = $row['triggerId'];
            $operator = $row['operator'];
            $thValue = $row['thresholdValue'];
            $isTriggered = $row['isTriggered'];

            $triggeredRequired = compareToThreshold($measurement, $operator, $thValue);

            if ($isTriggered == 0 AND $triggeredRequired == 1) {
                // If the reading is above threshold and the alarm has not yet been triggered
                // Trigger the alarm
                $sqlUpdateTrigger = "UPDATE alarmTriggers SET isTriggered = 1 WHERE triggerId = $triggerId";
                mysqli_query($conn, $sqlUpdateTrigger);

                // Send out emails
                sendTriggerNotifications($conn, $triggerId, $measurement);
                
            } elseif ($isTriggered == 1 AND $triggeredRequired == 0) {
                // If the alarm is triggered, but we received a reading under the trigger
                // Clear the alarm trigger and add to triggerHistory
                addToTriggerHistory($conn, $triggerId);

                $sqlUpdateTrigger = "UPDATE alarmTriggers SET isTriggered = 0 WHERE triggerId = $triggerId";
                mysqli_query($conn, $sqlUpdateTrigger);                
            } else {
                // Do nothing in both cases:
                    // Received a reading above alarm threshold, but alarm is already triggered
                    // Received reading under threshold, and the alarm is not already triggered
            }


        }
    }

}

function addToMeasurements($channelId, $deviceId, $measurement, $measurementTime, $conn) {
    $sql = "
    INSERT INTO measurements (channelId, deviceId, measurement, measurementTime) VALUES ($channelId, $deviceId, $measurement, '$measurementTime')
    ";
    $result = mysqli_query($conn, $sql);
}


// Function to compare reading to alarm threshold
function compareToThreshold($reading, $operator, $thresholdValue) {
    $response = false;
    switch ($operator) {
        case '>':
            if ($reading > $thresholdValue) { $response = true; }
            break;
        case '>=':
            if ($reading >= $thresholdValue) { $response = true; }
            break;
        case '<':
            if ($reading < $thresholdValue) { $response = true; }
            break;
        case '<=':
            if ($reading <= $thresholdValue) { $response = true; }
            break;
        case '==':
            if ($reading == $thresholdValue) { $response = true; }
            break;
    }
    return $response;
}

function addToTriggerHistory($conn, $triggerId) {
	$sql = "
	SELECT alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.alarmDescription, alarmTriggers.deviceId, channels.channelName, units.unitName
	FROM alarmTriggers
	LEFT JOIN channels ON alarmTriggers.channelId = channels.channelId
	LEFT JOIN units ON channels.unitId = units.unitId
	WHERE alarmTriggers.triggerId = $triggerId
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			$deviceId = $row['deviceId'];
			$channelName = $row['channelName'];
			$unitName = $row['unitName'];
			$operator = $row['operator'];
			$thresholdValue = $row['thresholdValue'];
			$alarmDescription = $row['alarmDescription'];
		}
	}

	$sql = "INSERT INTO triggeredAlarmsHistory (deviceId, channelName, unitName, operator, thresholdValue, alarmDescription) VALUES (?, ?, ?, ?, ?, ?);";
	$stmt = mysqli_stmt_init($conn);
	mysqli_stmt_prepare($stmt, $sql);
	mysqli_stmt_bind_param($stmt, "ssssss", $deviceId, $channelName, $unitName, $operator, $thresholdValue, $alarmDescription);
	if (mysqli_stmt_execute($stmt)) {
		echo 'SUCCESS';
	};
	mysqli_stmt_close($stmt);
}

?>