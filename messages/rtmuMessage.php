<?php
// Include email sending
require_once './emailFunction.php';

// RTC comes as a string e.g. '151020200908' so we must split it up.
function convert_rtc($rtc){
	$day = substr($rtc, 0, 2);
	$month = substr($rtc, 2, 2);
	$year = substr($rtc, 4, 4);
	$hour = substr($rtc, 8, 2);
	$minute = substr($rtc, 10, 2);
	
	$time_string = "$day-$month-$year $hour:$minute";
	// We convert this number into d-m-Y H-i so that we can use strtotime().
	return $time_string;
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

function processRtmuMessage($conn, $message) {
    $from = $message['fromNumber'];
    $textBody = $message['textBody'];
    $timeSent = $message['timeSent'];

    $data = explode("\n", $textBody);
    $message_type = $data[1];
    
    // Find device Id
    $sql = "SELECT deviceId, deviceStatus FROM devices WHERE devicePhone = $from";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $deviceId = $row['deviceId'];
            $deviceStatus = $row['deviceStatus'];
        }
    }

    // Since we have just received a message to this device, it must be active
    if ($deviceStatus == 0) {
        $sql = "UPDATE devices SET deviceStatus=1 WHERE deviceId = $deviceId";
        mysqli_query($conn, $sql);
    }

    // Bsc devices record channels in the order ai1, ai2, cnt -> aiN, aiN+1, cnt
    $sql = "SELECT channelId FROM channels WHERE (channelType = 'AI' OR channelType = 'COUNTER') AND deviceId = $deviceId
    ORDER BY CASE WHEN channelType = 'AI' THEN '1'
                WHEN channelType = 'COUNTER' THEN '2'
                ELSE channelType END ASC";
    $result = mysqli_query($conn, $sql);
    $channelsArray = array();
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $channelsArray[] = $row['channelId'];
        }
    }

    $alarmTriggers = array();
    $sqlAlarmTriggers = "
    SELECT alarmTriggers.triggerId, alarmTriggers.channelId, alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.isTriggered
    FROM alarmTriggers
    WHERE alarmTriggers.deviceId = $deviceId;
    ";
    $resultAlarmTriggers = mysqli_query($conn, $sqlAlarmTriggers);
    if ( mysqli_num_rows($resultAlarmTriggers) > 0 ) {
        while ($row = mysqli_fetch_assoc($resultAlarmTriggers)) {
            $alarmTriggers[] = $row;
        }
    }


    // Switch statement to process message according to its data type
    switch ($message_type) {
        // message_type is "DATA"
        case "DATA":
            $rtc_string = $data[2];
            // We convert the rtc to a legible date string (m-d-Y H:i). We then increment this value by the time difference
            $rtc = convert_rtc($rtc_string);
    
            // data[3] example: 30,1,8.6,11.1,15.7,15.2,14.7,15.9
            // So we split by ','. The 1st number is the time offset between measurements. 2nd is number of channels. The rest are measurements
            $measurements = explode(',', $data[3]);
            $channels_num = $measurements[1];
            $time_offset = $measurements[0];
            $time_to_add = 0;
    
            // Access the measurements after the first 2 values
            $valuesString = "";
            
            for ($i=2; $i < count($measurements); $i+=$channels_num) { 
                $date_string = date('Y-m-d H:i', strtotime("+$time_to_add minutes", strtotime($rtc)));
                for ($channelIndex = 0; $channelIndex < $channels_num; $channelIndex++) {
                    $thisMeasurement = $measurements[$i+$channelIndex];
                    $thisChannelId = $channelsArray[$channelIndex];
    
                    $triggeredRequired = 0;
    
                    for ($k = 0; $k < count($alarmTriggers); $k++) {
                        switch ( $alarmTriggers[$k]['operator'] ) {
                            case '>':
                                if ($thisMeasurement > $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '>=':
                                if ($thisMeasurement >= $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '==':
                                if ($thisMeasurement == $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '<':
                                if ($thisMeasurement < $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '<=':
                                if ($thisMeasurement <= $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            default:
                                break;
                        }
                        if ($triggeredRequired == 1) {
                            // Check if the alarmtriggered is 1
                            if ($alarmTriggers[$k]['isTriggered'] == 0) {
                                // If the alarm is not already triggered, trigger it
                                $sqlUpdateTrigger = "UPDATE alarmTriggers SET isTriggered = 1 WHERE triggerId = {$alarmTriggers[$k]['triggerId']}";
                                mysqli_query($conn, $sqlUpdateTrigger);
    
                                // Send out emails
                                generateEmail($conn, $alarmTriggers[$k]['triggerId'], $thisMeasurement);
    
                                $alarmTriggers[$k]['isTriggered'] = 1;
                            }
                        } else {
                            if ($alarmTriggers[$k]['isTriggered'] == 1 && $triggeredRequired == 0) {
                                // Update isTriggered
                                $sqlUpdateTrigger = "UPDATE alarmTriggers SET isTriggered = 0 WHERE triggerId = {$alarmTriggers[$k]['triggerId']}";
                                mysqli_query($conn, $sqlUpdateTrigger);
    
                                // Add to triggersHistory
                                addToTriggerHistory($conn, $alarmTriggers[$k]['triggerId']);
    
                                $alarmTriggers[$k]['isTriggered'] = 0;
                            }
                        }
                    }
                    
                    $valuesString .= "({$deviceId}, {$thisChannelId}, {$thisMeasurement}, '{$date_string}'),";
                }
                
                $time_to_add += $time_offset;
                
            }
            
            // Remove the last comma (,)
            $valuesString = substr($valuesString, 0, -1);
    
            $sql = "INSERT INTO measurements(deviceId, channelId, measurement, measurementTime) VALUES $valuesString;";
            mysqli_query($conn, $sql);
    
        break;
    
        // message_type is "DWTS"
        case "DWTS":
            $rtc_string = $data[2];
            // We convert the rtc to a legible date string (m-d-Y H:i). We then increment this value by the time difference
            $rtc = convert_rtc($rtc_string);
    
            // data[3] example: 1,0,14.0,30,14.7,30,15.8,30,22.0
            // So we split by ','. The 1st number is the number of channels. 2nd number is offset of 1st measurement from rtc. After 2nd number it goes : measurement, time offset etc
            $measurements = explode(',', $data[3]);
            $channels_num = $measurements[0];
            // Time offset is always the same which is the first number after the measurements.
            if (array_key_exists((2+$channels_num), $measurements)) {
                $time_offset = $measurements[2+$channels_num];
            } else {
                $time_offset = 30;
            }
            $time_to_add = 0;
    
            // Access each value in the values array. First measurement was recorded at the RTC time and is then increment by appropriate value.
            $valuesString = "";
            for ($i = 2; $i < count($measurements); $i+=$channels_num+1) {
                $date_string = date('Y-m-d H:i', strtotime("+$time_to_add minutes", strtotime($rtc)));
                for ($channelIndex = 0; $channelIndex < $channels_num; $channelIndex++) {
                    $thisMeasurement = $measurements[$i+$channelIndex];
                    $thisChannelId = $channelsArray[$channelIndex];
    
                    $triggeredRequired = 0;
    
                    for ($k = 0; $k < count($alarmTriggers); $k++) {
                        switch ( $alarmTriggers[$k]['operator'] ) {
                            case '>':
                                if ($thisMeasurement > $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '>=':
                                if ($thisMeasurement >= $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '==':
                                if ($thisMeasurement == $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '<':
                                if ($thisMeasurement < $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            case '<=':
                                if ($thisMeasurement <= $alarmTriggers[$k]['thresholdValue']) {
                                    $triggeredRequired = 1;
                                } else {
                                    $triggeredRequired = 0;
                                }
                                break;
                            default:
                                break;
                        }
                        if ($triggeredRequired == 1) {
                            // Check if the alarmtriggered is 1
                            if ($alarmTriggers[$k]['isTriggered'] == 0) {
                                // If the alarm is not already triggered, trigger it
                                $sqlUpdateTrigger = "UPDATE alarmTriggers SET isTriggered = 1 WHERE triggerId = {$alarmTriggers[$k]['triggerId']}";
                                mysqli_query($conn, $sqlUpdateTrigger);
    
                                // Send out emails
                                generateEmail($conn, $alarmTriggers[$k]['triggerId'], $thisMeasurement);
    
                                $alarmTriggers[$k]['isTriggered'] = 1;
                            }
                        } else {
                            if ($alarmTriggers[$k]['isTriggered'] == 1 && $triggeredRequired == 0) {
                                // Update isTriggered
                                $sqlUpdateTrigger = "UPDATE alarmTriggers SET isTriggered = 0 WHERE triggerId = {$alarmTriggers[$k]['triggerId']}";
                                mysqli_query($conn, $sqlUpdateTrigger);
    
                                // Add to triggersHistory
                                addToTriggerHistory($conn, $alarmTriggers[$k]['triggerId']);
    
                                $alarmTriggers[$k]['isTriggered'] = 0;
                            }
                        }
                    }
    
                    $valuesString .= "({$deviceId}, {$thisChannelId}, {$thisMeasurement}, '{$date_string}'),";
                }
                
                $time_to_add += $time_offset;
            }
            
            $valuesString = substr($valuesString, 0, -1);
            $sql = "INSERT INTO measurements(deviceId, channelId, measurement, measurementTime) VALUES $valuesString;";
            mysqli_query($conn, $sql);
        break;
    
        // message_type is "ALARM MESSAGE"
        case "ALARM MESSAGE":
            // 3rd line in the received data shows the input which can be 'DI 1', 'Rail Temp'
            // An analog/counter input will also contain an actual measurement value
            $input_name = $data[2];
            $input_message = $data[3];
    
            // Find channel id
            $sql = "SELECT channelId FROM channels WHERE UPPER(channelName) = UPPER('$input_name')";
            $result = mysqli_query($conn, $sql);
            $resultCheck = mysqli_num_rows($result);
            if ($resultCheck > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $alarmChannel = $row['channelId'];
                }
            } 
            
            // 5th element is the actual measurement value. We can check if this exists. If it does - it is an analog/counter input
            if (array_key_exists(4, $data)) {
                $value = explode(" ", $data[4])[0];
                $alarmUnit = explode(" ", $data[4])[1];
    
                // Send analog/counter reading to db
                $sql = "INSERT INTO smsAlarms(deviceId, channelId, smsAlarmHeader, smsAlarmReading, smsAlarmTime) VALUES ('$deviceId', '$alarmChannel', '$input_message', '$value', '$timeSent');";
                mysqli_query($conn, $sql);
            } else {
                // Send digital reading to db
                $sql = "INSERT INTO smsAlarms(deviceId, channelId, smsAlarmHeader, smsAlarmTime) VALUES ('$deviceId', '$alarmChannel', '$input_message', '$timeSent');";
                mysqli_query($conn, $sql);
            }
        break;
    
        // message_type is "STATUS MESSAGE"
        case "STATUS MESSAGE":
            $status = $data[2];
    
            // Some messages can be START/HALT. If it's ON, then the message is longer.
            if ($status == "ON") {
                $total_samples = $data[3];
                $signal_quality = $data[4];
                $bit_error = $data[5];
                $sua_fua = $data[6];
                $samplingData = $total_samples . "\n" . $signal_quality . "\n" . $bit_error . "\n" . $sua_fua;
    
    
                // Check if there is GPS location at the end of the message
                if (array_key_exists(7, $data)) {
                    $location = explode(',', trim($data[7]) );
                    $latitude = $location[0];
                    $longitude = $location[1];
    
                    $sql = "INSERT INTO smsStatus(deviceId, smsStatus, samplingData, latitude, longitude, smsStatusTime) VALUES($deviceId, '$status', '$samplingData', $latitude, $longitude, '$timeSent')";
                    mysqli_query($conn, $sql);
    
                    // Also update latitude and longitude in devices table
                    $sql = "UPDATE devices SET latitude = $latitude, longitude = $longitude WHERE deviceId = $deviceId";
                    mysqli_query($conn, $sql);
                    
                } else {
                    $sql = "INSERT INTO smsStatus(deviceId, smsStatus, samplingData, smsStatusTime) VALUES($deviceId, '$status', '$samplingData', '$timeSent')";
                    mysqli_query($conn, $sql);
                }
    
                // Since status is ON, change devices.deviceStatus to ON
                $sql = "UPDATE devices SET deviceStatus = '1' WHERE deviceId = $deviceId";
                mysqli_query($conn, $sql);
    
    
            } else {
                $sql = "INSERT INTO smsStatus(deviceId, smsStatus, smsStatusTime) VALUES($deviceId, '$status', '$timeSent')";
                mysqli_query($conn, $sql);
            }
    
        break;
    
        // In case there is no match for message_type
        default:
            echo 'NO MATCH FOUND';
        break;
    }

    // return 'OK';
}