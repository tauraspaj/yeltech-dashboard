<?php
// Email sending functionality
require './../mailer/mailer.php';
// SMS sending functionality
include_once './../plivo/send.php';
// SMS sending functionality to Norway numbers
include_once './../plivo/textmagic_send.php';


function sendTriggerNotifications($conn, $triggerId, $reading) {
	$sql = "
		SELECT alarmTriggers.channelId, alarmTriggers.deviceId, alarmTriggers.operator, alarmTriggers.thresholdValue, alarmTriggers.alarmDescription, alarmTriggers.timeCreated, channels.channelName, channels.unitId, devices.groupId, devices.deviceId, devices.deviceName, devices.deviceAlias, devices.customLocation, devices.latitude, devices.longitude, units.unitName
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

			$groupId = $row['groupId'];
			$deviceId = $row['deviceId'];
			$deviceName = $row['deviceName'];
			$deviceAlias = $row['deviceAlias'];
			$customLocation = $row['customLocation'];
			$latitude = $row['latitude'];
			$longitude = $row['longitude'];

			$unitId = $row['unitId'];
			$unitName = $row['unitName'];
		}
	}

	// Add all email recipients into 1 array and send bulk email
	$emailRecipients = array();
	// Add all sms recipients into 1 array for further checks and processing
	$smsRecipients = array();

	$sql2 = "
		SELECT alarmTriggers.deviceId, alarmRecipients.userId, users.email, users.fullName, users.phoneNumber, sendingType.sendingType
		FROM alarmTriggers
		LEFT JOIN alarmRecipients ON alarmTriggers.deviceId = alarmRecipients.deviceId
		LEFT JOIN users ON alarmRecipients.userId = users.userId
		LEFT JOIN sendingType ON sendingType.sendingId = users.sendingId
		WHERE alarmTriggers.triggerId = $triggerId;
	";
	$result2 = mysqli_query($conn, $sql2);
	if ( mysqli_num_rows($result2) > 0 ) {
		while ($row = mysqli_fetch_assoc($result2)) {
			if (strpos($row['sendingType'], 'EMAIL') !== false) {
				$emailRecipients[] = $row['email'];
			}
	
			// Check if user requires SMS notification
			if (strpos($row['sendingType'], 'SMS') !== false) {
				$smsRecipients[] = ['fullName' => $row['fullName'], 'phoneNumber' => $row['phoneNumber'], 'email' => $row['email']];
			}
		}
	}

	// ! GENERATE EMAIL ! //
	// We now have a list of email recipients so we can sound out the notification. We need to fill the sendEmail() function with a subject and email body
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
			$smsLocation = ", Location: $latitude, $longitude";
		}
	} else {
		$location = "<br>Location: <b>$customLocation</b>";
		$smsLocation = ", Location: $customLocation";
	}

	// Extra formatting for °C
	if ($unitId == 1) {
		$unitName = "°C";
	}

	$timeCreated = date("H:i F j, Y", time()+60*60);

	$emailSubject = "$name1 $name2 - $alarmDescription ALARM ($channelName: $reading $unitName)";
	$emailBody = "
	Device: <b>$name1 $name2</b><br>
	Alarm: <b>$alarmDescription</b><br><br>

	$channelName: <b>$reading $unitName</b><br>
	Trigger: <b>$operator$thresholdValue $unitName</b><br>
	Timestamp: <b>$timeCreated</b>
	$location
	";

	sendEmail($emailRecipients, $emailSubject, $emailBody);

	// ! SMS RECIPIENTS ! //
	// We also have a list of sms recipients so we must first check if the count of this array is less than the sms left by device's group
	$smsLeft = 0;
	$sql3 = "
	SELECT `groups`.smsLeft 
	FROM devices
	LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
	WHERE deviceId = $deviceId;
	";
	$result3 = mysqli_query($conn, $sql3);
	if ( mysqli_num_rows($result3) > 0 ) {
		while ($row = mysqli_fetch_assoc($result3)) {
			$smsLeft = $row['smsLeft'];
		}
	}

	if (count($smsRecipients) <= $smsLeft) {
		// Send the message
		$smsCounter = 0;
		foreach ($smsRecipients as $smsRecipient) {
			if ($smsRecipient['phoneNumber'] != null ) {
				// TO IS USER PHONE NUMBER
				$to = $smsRecipient['phoneNumber'];
				$textBody = "$name1 $name2 - $alarmDescription ALARM, $channelName: $reading $smsLocation";


				// If the first 3 symbols of the recipient number is '+47', that means we must Norwegian service through TextMagic. Else use Plivo.
				if ( substr($to, 0, 3) == '+47' ) {
					sendWithTextMagic($to, $textBody);
				} else {
					// FROM IS OUR PLIVO NUMBER
					$from = '+447862079649';
					sendMessage($from, $to, $textBody);
				}

				$smsCounter += 1;
			}
		}

		// Deduct the number of messages sent
		$newSMSleft = $smsLeft - $smsCounter;
		$sqlUpdateSMS = "UPDATE `groups` SET smsLeft = $newSMSleft WHERE groupId=$groupId";
		mysqli_query($conn, $sqlUpdateSMS);

		// Proactive warning - inform that customers have under 50 credits left
		if ($newSMSleft < 50 && $newSMSleft > 1) {
			$emailSubject_WARNING50 = "WARNING: YOU ONLY HAVE $newSMSleft CREDITS LEFT";
			$emailBody_WARNING50 = "
				Hi! <br>
				We would like to inform you have you only have <b>$newSMSleft</b> SMS credits left.<br><br>
				You will no longer receive SMS alerts if you run out of credits, so in order to avoid any disruption, we urge you to top up as soon as possible.<br>
				Please get in touch with us to add more SMS credits!
			";

			// Now lets create an array of all the recipients
			$warningRecipients = array();
			foreach ($smsRecipients as $smsRecipient) {
				$warningRecipients[] = $smsRecipient['email'];
			}
			$warningRecipients[] = 'info@yeltech.com';
			// Send
			sendEmail($warningRecipients, $emailSubject_WARNING50, $emailBody_WARNING50);
		}
	} else {
		// Send out email notification to all the sms recipients
		$emailSubject2 = "WARNING: YOU DON'T HAVE ENOUGH SMS CREDITS LEFT";
		$emailBody2 = "
			Hi! <br>
			We have attempted to send an SMS notification with the latest alarm from <b>$name1 $name2</b>, but it seems like your group does not have enough credits left!<br><br>

			You have <b>$smsLeft</b> credits left<br>
			And here is a list of people requiring SMS notifications <b>(".count($smsRecipients).")</b>:<br>
		";
		foreach ($smsRecipients as $smsRecipient) {
			$emailBody2 .= "{$smsRecipient['fullName']}<br>";
		}
		$emailBody2 .= "<br>Please get in touch with us to add more SMS credits!";

		// Now lets create an array of all the recipients
		$warningRecipients = array();
		foreach ($smsRecipients as $smsRecipient) {
			$warningRecipients[] = $smsRecipient['email'];
		}
		$warningRecipients[] = 'info@yeltech.com';
		
		// Send
		sendEmail($warningRecipients, $emailSubject2, $emailBody2);
	}
}

function forwardEWBalarms($conn, $deviceId, $message) {
	$sqlDevice = "
	SELECT devices.deviceName, devices.deviceAlias, devices.customLocation, devices.latitude, devices.longitude, devices.groupId
	FROM devices
	WHERE deviceId = $deviceId
	";
	$resultDevice = mysqli_query($conn, $sqlDevice);
	if ( mysqli_num_rows($resultDevice) > 0 ) {
		while ($row = mysqli_fetch_assoc($resultDevice)) {
			// Get device data
			$deviceData = [
				'deviceName' => $row['deviceName'],
				'deviceAlias' => $row['deviceAlias'],
				'customLocation' => $row['customLocation'],
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude']
			];
			$groupId = $row['groupId'];
		}
	}

	// Add all email recipients into 1 array and send bulk email
	$emailRecipients = array();
	// Add all sms recipients into 1 array for further checks and processing
	$smsRecipients = array();

	$sql = "
	SELECT alarmRecipients.userId, users.fullName, users.email, users.phoneNumber, sendingType.sendingType
	FROM alarmRecipients
	LEFT JOIN users ON alarmRecipients.userId = users.userId
	LEFT JOIN sendingType ON sendingType.sendingId = users.sendingId
	WHERE alarmRecipients.deviceId = $deviceId
	";
	$result = mysqli_query($conn, $sql);
	if ( mysqli_num_rows($result) > 0 ) {
		while ($row = mysqli_fetch_assoc($result)) {
			if (strpos($row['sendingType'], 'EMAIL') !== false) {
				$emailRecipients[] = $row['email'];
			}
	
			// Check if user requires SMS notification
			if (strpos($row['sendingType'], 'SMS') !== false) {
				$smsRecipients[] = ['fullName' => $row['fullName'], 'phoneNumber' => $row['phoneNumber'], 'email' => $row['email']];
			}
		}
	}

	// ! GENERATE EMAIL ! //
	// We now have a list of email recipients so we can sound out the notification. We need to fill the sendEmail() function with a subject and email body
	if ($deviceData['deviceAlias'] != null) {
		$name1 = $deviceData['deviceAlias'];
		$name2 = "({$deviceData['deviceName']})";
	} else {
		$name1 = $deviceData['deviceName'];
		$name2 = "";
	}

	$location = "";
	if ($deviceData['customLocation'] == null) {
		if ($deviceData['latitude'] == null || $deviceData['longitude'] == null) {
			// Don't display location
		} else {
			$location = "<br>Location: <b>{$deviceData['latitude']}, {$deviceData['longitude']}</b>";
			$smsLocation = ", Location: {$deviceData['latitude']}, {$deviceData['longitude']}";
		}
	} else {
		$location = "<br>Location: <b>{$deviceData['customLocation']}</b>";
		$smsLocation = ", Location: {$deviceData['customLocation']}";
	}

	$timeCreated = date("H:i F j, Y", time()+60*60);

	$emailSubject = "$name1 $name2 - ALARM";
	$emailBody = "
	Device: <b>$name1 $name2</b><br>
	Alarm: <b>$message</b><br><br>

	Timestamp: <b>$timeCreated</b>
	$location
	";
	sendEmail($emailRecipients, $emailSubject, $emailBody);

	// ! SMS RECIPIENTS ! //
	// We also have a list of sms recipients so we must first check if the count of this array is less than the sms left by device's group
	$smsLeft = 0;
	$sql3 = "
	SELECT `groups`.smsLeft 
	FROM devices
	LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
	WHERE deviceId = $deviceId;
	";
	$result3 = mysqli_query($conn, $sql3);
	if ( mysqli_num_rows($result3) > 0 ) {
		while ($row = mysqli_fetch_assoc($result3)) {
			$smsLeft = $row['smsLeft'];
		}
	}

	if (count($smsRecipients) <= $smsLeft) {
		// Send the message
		$smsCounter = 0;
		foreach ($smsRecipients as $smsRecipient) {
			if ($smsRecipient['phoneNumber'] != null ) {
				// FROM IS OUR PLIVO NUMBER
				$from = '+447862079649';
				// TO IS USER PHONE NUMBER
				$to = $smsRecipient['phoneNumber'];
				$textBody = $message;
                
				sendMessage($from, $to, $textBody);
				$smsCounter += 1;
			}
		}

		
		// Deduct the number of messages sent
		$newSMSleft = $smsLeft - $smsCounter;
		$sqlUpdateSMS = "UPDATE `groups` SET smsLeft = $newSMSleft WHERE groupId=$groupId";
		mysqli_query($conn, $sqlUpdateSMS);
		
		// Proactive warning - inform that customers have under 50 credits left
		if ($newSMSleft < 50 && $newSMSleft > 1) {
			$emailSubject_WARNING50 = "WARNING: YOU ONLY HAVE $newSMSleft CREDITS LEFT";
			$emailBody_WARNING50 = "
				Hi! <br>
				We would like to inform you have you only have <b>$newSMSleft</b> SMS credits left.<br><br>
				You will no longer receive SMS alerts if you run out of credits, so in order to avoid any disruption, we urge you to top up as soon as possible.<br>
				Please get in touch with us to add more SMS credits!
			";

			// Now lets create an array of all the recipients
			$warningRecipients = array();
			foreach ($smsRecipients as $smsRecipient) {
				$warningRecipients[] = $smsRecipient['email'];
			}
			$warningRecipients[] = 'info@yeltech.com';
			// Send
			sendEmail($warningRecipients, $emailSubject_WARNING50, $emailBody_WARNING50);
		}
	} else {
		// Send out email notification to all the sms recipients
		$emailSubject2 = "WARNING: YOU DON'T HAVE ENOUGH SMS CREDITS LEFT";
		$emailBody2 = "
			Hi! <br>
			We have attempted to send an SMS notification with the latest alarm from <b>$name1 $name2</b>, but it seems like your group does not have enough credits left!<br><br>

			You have <b>$smsLeft</b> credits left<br>
			And here is a list of people requiring SMS notifications <b>(".count($smsRecipients).")</b>:<br>
		";
		foreach ($smsRecipients as $smsRecipient) {
			$emailBody2 .= "{$smsRecipient['fullName']}<br>";
		}
		$emailBody2 .= "<br>Please get in touch with us to add more SMS credits!";

		// Now lets create an array of all the recipients
		$warningRecipients = array();
		foreach ($smsRecipients as $smsRecipient) {
			$warningRecipients[] = $smsRecipient['email'];
		}
		$warningRecipients[] = 'info@yeltech.com';
		
		// Send
		sendEmail($warningRecipients, $emailSubject2, $emailBody2);
	}
}
