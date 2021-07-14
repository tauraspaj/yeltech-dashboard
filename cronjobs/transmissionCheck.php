<?php

// ! This is a CRON JOB that should be set to run hourly

// Connect database
require_once './../includes/dbh.inc.php';

$sql = "
SELECT devices.deviceId, devices.deviceName, devices.devicePhone, devices.deviceStatus FROM devices WHERE devices.deviceStatus = 1;
";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $devicePhone = $row['devicePhone'];
        $deviceName = $row['deviceName'];
        $deviceId = $row['deviceId'];

        $sql2 = "
            SELECT messages.timeSent
            FROM messages
            WHERE messages.fromNumber = '$devicePhone'
            ORDER BY messages.messageId DESC
            LIMIT 1
        ";
        $result2 = mysqli_query($conn, $sql2);
        if ( mysqli_num_rows($result2) > 0 ) {
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $timeSent = $row2['timeSent'];

                // Calculate difference between now and timeSent
                $diff = time() - strtotime($timeSent);
                if ($diff > 60*60*24) {
                    // If it's been over 24 hours since last transmission, turn the device off and clear alarms
                    $sql = "
                        UPDATE devices SET deviceStatus = '0' WHERE deviceId = $deviceId
                    ";
                    mysqli_query($conn, $sql);

                    $sql = "
                        UPDATE alarmTriggers SET isTriggered = 0 WHERE deviceId = $deviceId
                    ";
                    mysqli_query($conn, $sql);

                } else {
                    echo '<br>';
                }
            }
        } else {
            // In case the device is turned on and is not transmitting...
            $sql = "
            UPDATE devices SET deviceStatus = '0' WHERE deviceId = $deviceId
            ";
            mysqli_query($conn, $sql);
        }
    }
}

?>