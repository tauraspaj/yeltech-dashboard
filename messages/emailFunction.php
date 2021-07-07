<?php
// Email sending functionality
require './../mailer/mailer.php';
function generateEmail($conn, $triggerId, $reading) {
	$sql = "
	SELECT alarmTriggers.channelId, alarmTriggers.deviceId, alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.alarmDescription, alarmTriggers.timeCreated, channels.channelName, channels.unitId, devices.deviceName, devices.deviceAlias, devices.customLocation, devices.latitude, devices.longitude, units.unitName
	FROM alarmTriggers
	LEFT JOIN devices ON alarmTriggers.deviceId = devices.deviceId
	LEFT JOIN channels ON alarmTriggers.channelId = channels.channelId
	LEFT JOIN units ON channels.unitId = units.unitId
	WHERE alarmTriggers.triggerId = $triggerId
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			$operator = $row['operator'];
			$thresholdValue = $row['thresholdValue'];
			$alarmDescription = $row['alarmDescription'];
			$timeCreated = $row['timeCreated'];

			$channelName = $row['channelName'];

			$deviceName = $row['deviceName'];
			$deviceAlias = $row['deviceAlias'];
			$customLocation = $row['customLocation'];
			$latitude = $row['latitude'];
			$longitude = $row['longitude'];

			$unitId = $row['unitId'];
			$unitName = $row['unitName'];
		}
	}

	$recipients = array();
	$sql2 = "
	SELECT alarmTriggers.deviceId, alarmRecipients.userId, users.email, sendingType.sendingType
	FROM alarmTriggers
	LEFT JOIN alarmRecipients ON alarmTriggers.deviceId = alarmRecipients.deviceId
	LEFT JOIN users ON alarmRecipients.userId = users.userId
	LEFT JOIN sendingType ON sendingType.sendingId = users.sendingId
	WHERE alarmTriggers.triggerId = $triggerId AND sendingType.sendingType = 'EMAIL';
	";
	$result2 = mysqli_query($conn, $sql2);
	if ( mysqli_num_rows($result2) > 0 ) {
		while ($row = mysqli_fetch_assoc($result2)) {
			array_push($recipients, $row['email']);
		}
	}

	if ($deviceAlias != null) {
		$name1 = $deviceAlias;
		$name2 = "($deviceName)";
	} else {
		$name1 = $deviceName;
		$name2 = "";
	}

	$location = "";
	if ($customLocation == null) {
		if ($latitude == null || $longitude == null) {
			// Don't display location
		} else {
			$location = "<br>Location: <b>$latitude, $longitude</b>";
		}
	} else {
		$location = "<br>Location: <b>$customLocation</b>";
	}

	// Extra formatting for °C
	if ($unitId == 1) {
		$unitName = "&deg;C";
	}

	$timeCreated = date("H:i F j, Y", time()+60*60);

	$subject = "$name1 $name2 - $alarmDescription ALARM ($channelName: $reading °C)";
	$emailBody = "
	Device: <b>$name1 $name2</b><br>
	Alarm: <b>$alarmDescription</b><br><br>

	$channelName: <b>$reading $unitName</b><br>
	Trigger: <b>$operator$thresholdValue $unitName</b><br>
	Timestamp: <b>$timeCreated</b>
	$location
	";

	sendEmail($recipients, $subject, $emailBody);
}

