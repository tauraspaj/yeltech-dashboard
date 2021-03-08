<?php 

require_once 'dbh.inc.php';


$to = $_POST['to'];
$from = $_POST['from'];
$textBody = $_POST['textBody'];

$tempData = explode("\n", $textBody);

$tempDeviceName = $tempData[0];
$tempMessageType = $tempData[1];
$msgType = "";
if ($tempMessageType == 'DWTS' || $tempMessageType == 'DATA') {
	$msgType = 'SMS DATA';
} elseif ($tempMessageType == 'ALARM MESSAGE') {
	$msgType = 'SMS ALARM';
} elseif ($tempMessageType == 'STATUS MESSAGE') {
	$msgType = 'SMS STATUS';
} else {
	$msgType = 'SMS UNDEFINED';
}

$sql = "INSERT INTO messages (toNumber, fromNumber, textBody, messageType) VALUES (?, ?, ?, ?);";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    echo("Prepare error");
    exit();
}
if(!mysqli_stmt_bind_param($stmt, "ssss", $to, $from, $textBody, $msgType)){
    echo("Bind error");
    exit();
}
if(!mysqli_stmt_execute($stmt)) {
    echo("Execute error");
    exit();
}

mysqli_stmt_close($stmt);

$string = $textBody;

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

//$string = "RTMU 2537\nDWTS\n261220192046\n2,0,7.6,8.0,30,7.6,7.8,60,7.7,8.2,90,8.1,8.2,120,8.2,8.4,150,8.2,8.2,180,7.6,8.1,210,7.3,7.3,240,7.1,7.2,270,6.8,7.0,300,6.7,7.0";
//$string = "RTMU 2671\nDATA\n151020200908\n30,2,8.6,11.1,15.7,15.2,14.7,15.9";
//$string = "RTMU 2918\nSTATUS MESSAGE\nON\nTOTAL:1\nSQ : ERR:0, MIN:7, MAX:7, AVG:7\nBER: ERR:1, MIN:-, MAX:-, AVG:-\nSUA:0, FUA:0\n+ 51.538599,+000.083299";
//$string = "EWB 0063\nALARM MESSAGE\nCURRENT MONITOR\nESR BOARD LIGHTS OUT\n0.0 mA";
//$string = "TOG-RAIN GAUGE\nDWTS\n201120200353\n1,0,0.4,15,0.4,30,0.4,45,0.4,60,0.4,75,0.4,90,0.4,105,0.4,120,0.4,135,0.4,150,0.2,165,0.2,180,0.2,195,0.4,210,0.4,225,0.4";
// Split the string into data array by line
$data = explode("\n", $string);

$deviceName = $data[0];
$message_type = $data[1];

// Split unit into device name and serial number
// $device_name = explode(" ", $unit)[0];
// $device_serial = explode(" ", $unit)[1];

// Test data
// for ($i=0; $i < count($data); $i++) { 
// 	echo $data[$i].'<br>';
// }

// Find device Id
$sql = "SELECT deviceId FROM devices WHERE devicePhone = $from";
$result = mysqli_query($conn, $sql);
$resultCheck = mysqli_num_rows($result);
if ($resultCheck > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deviceId = $row['deviceId'];
    }
}

// Bsc devices record channels in the order ai1, ai2, cnt -> aiN, aiN+1, cnt
$sql = "SELECT channelId FROM channels WHERE (channelType = 'AI' OR channelType = 'COUNTER') AND deviceId = $deviceId
ORDER BY CASE WHEN channelType = 'AI' THEN '1'
           	WHEN channelType = 'COUNTER' THEN '2'
          	ELSE channelType END ASC";
$result = mysqli_query($conn, $sql);
$resultCheck = mysqli_num_rows($result);
$channelsArray = array();
if ($resultCheck > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
		array_push($channelsArray, $row['channelId']);
    }
}

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

        //Access the measurements after the first 2 values
        $valuesString = "";
		for ($i=2; $i < count($measurements); $i+=$channels_num) { 
			$date_string = date('Y-m-d H:i', strtotime("+$time_to_add minutes", strtotime($rtc)));
			
			for ($channelIndex = 0; $channelIndex < $channels_num; $channelIndex++) {
				$valuesString .= "({$deviceId}, {$channelsArray[$channelIndex]}, {$measurements[$i+$channelIndex]}, '{$date_string}'),";
			}

			$time_to_add += $time_offset;
        }
		
        $valuesString = substr($valuesString, 0, -1);
        $sql = "INSERT INTO measurements(deviceId, channelId, measurement, measurementTime) VALUES $valuesString;";
        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);

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
		$time_offset = $measurements[2+$channels_num];
		$time_to_add = 0;

        // Access each value in the values array. First measurement was recorded at the RTC time and is then increment by appropriate value.
        $valuesString = "";
		for ($i = 2; $i < count($measurements); $i+=$channels_num+1) {
			$date_string = date('Y-m-d H:i', strtotime("+$time_to_add minutes", strtotime($rtc)));
			for ($channelIndex = 0; $channelIndex < $channels_num; $channelIndex++) {
				//echo 'Channel '.$j.' : '.$measurements[$i+$j].' at '.$date_string.'<br>';
                $valuesString .= "({$deviceId}, {$channelsArray[$channelIndex]}, {$measurements[$i+$channelIndex]}, '{$date_string}'),";
			}
			
			$time_to_add += $time_offset;
        }
        
        $valuesString = substr($valuesString, 0, -1);
        $sql = "INSERT INTO measurements(deviceId, channelId, measurement, measurementTime) VALUES $valuesString;";
        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
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
			$sql = "INSERT INTO smsAlarms(deviceId, channelId, smsAlarmHeader, smsAlarmReading) VALUES ('$deviceId', '$alarmChannel', '$input_message', '$value');";
			if (mysqli_query($conn, $sql)) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
			mysqli_close($conn);
		} else {
			// Send digital reading to db
			$sql = "INSERT INTO smsAlarms(deviceId, channelId, smsAlarmHeader) VALUES ('$deviceId', '$alarmChannel', '$input_message');";
			if (mysqli_query($conn, $sql)) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
			mysqli_close($conn);
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

				$sql = "INSERT INTO smsStatus(deviceId, smsStatus, samplingData, latitude, longitude) VALUES($deviceId, '$status', '$samplingData', $latitude, $longitude)";
				if (mysqli_query($conn, $sql)) {
					echo "New record created successfully";
				} else {
					echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}
			} else {
				$sql = "INSERT INTO smsStatus(deviceId, smsStatus, samplingData) VALUES($deviceId, '$status', '$samplingData')";
				if (mysqli_query($conn, $sql)) {
					echo "New record created successfully";
				} else {
					echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}
			}

			// Since status is ON, change devices.deviceStatus to ON
			$sql = "UPDATE devices SET deviceStatus = '1' WHERE deviceId = $deviceId";
			if (mysqli_query($conn, $sql)) {
				echo "Record updated successfully";
			} else {
				echo "Error updating record: " . mysqli_error($conn);
			}


		} else {
			$sql = "INSERT INTO smsStatus(deviceId, smsStatus) VALUES($deviceId, '$status')";
			if (mysqli_query($conn, $sql)) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
			mysqli_close($conn);
		}

	break;

	// In case there is no match for message_type
	default:
		echo 'NO MATCH FOUND';
	break;
}
?>