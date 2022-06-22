<?php

// ! This is a CRON JOB that should be set to run weekly (email sent every Monday 10am)
// ! It will loop through all groups, and make a list of any missing details

// Connect database
require_once './../includes/dbh.inc.php';
// Email sending functionality
require './../mailer/mailer.php';

// Email will not be sent if there is nothing to be sent and all tasks are completed
$sendEmailCheck = false;
// Choose subject
$emailSubject = "YelCloud Admin To-Do";
$emailBody = "Hello Team!<br><br>";
$emailBody .= "This is an automated email to let you know of missing details on YelCloud!<br>";

// Loop through all groups
$sql = "
SELECT groupId, groupName FROM `groups` ORDER BY groupId;
";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $thisGroupId = $row['groupId'];
        $thisGroupName = $row['groupName'];
        
        // Check if location exists. Will return false if latitude or longitude is null
        $checkLocationExists = checkLocationExists($thisGroupId, $conn);
        // Get devices with no calibration dates
        $missingCalibration = getMissingCalibration($thisGroupId, $conn);
        // Get devices with subscription dates
        $missingSubscription = getMissingSubscriptions($thisGroupId, $conn);
        // Get devices with no latitude/longitude
        $missingDeviceLocation = getMissingDeviceLocations($thisGroupId, $conn);
        // Get list of devices past their calibration date
        $getExpiredCalibrations = getExpiredCalibrations($thisGroupId, $conn);
        // Get list of expired subscriptions
        $getExpiredSubscriptions = getExpiredSubscriptions($thisGroupId, $conn);

        // Check if there any any devices that need informing
        if (!empty($missingCalibration) || !empty($missingSubscription) || !empty($missingDeviceLocation) || !$checkLocationExists || !empty($getExpiredCalibrations) || !empty($getExpiredSubscriptions)) {
            $sendEmailCheck = true;
            $emailBody .= "<br><b>Group: $thisGroupName</b><br>";
        }

        if (!$checkLocationExists) {
            $emailBody .= "- Missing group location (lat&long)</u><br>";
        }

        if (!empty($missingCalibration)) {
            $emailBody .= "- Missing calibration due dates on <b>".count($missingCalibration)." devices</b> <br>";
            // for ($i = 0; $i < count($missingCalibration); $i++) {
            //     $emailBody .= "{$missingCalibration[$i]['deviceName']}<br>";
            // }
        }

        if (!empty($missingSubscription)) {
            $emailBody .= "- Missing subscription end dates on <b>".count($missingSubscription)." devices</b> <br>";
            // for ($i = 0; $i < count($missingSubscription); $i++) {
            //     $emailBody .= "{$missingSubscription[$i]['deviceName']}<br>";
            // }
        }

        if (!empty($missingDeviceLocation)) {
            $emailBody .= "- Missing device locations (lat&long) on <b>".count($missingDeviceLocation)." devices</b> <br>";
            // for ($i = 0; $i < count($missingDeviceLocation); $i++) {
            //     $emailBody .= "{$missingDeviceLocation[$i]['deviceName']}<br>";
            // }
        }

        if (!empty($getExpiredCalibrations)) {
            $emailBody .= "<br><u>These devices are past their calibration date. This may not be incorrect but double check it's up to date.</u><br>";
            for ($i = 0; $i < count($getExpiredCalibrations); $i++) {
                    // Process date
                $displayDate = new DateTime($getExpiredCalibrations[$i]['nextCalibrationDue']);
                $displayDate = $displayDate->format("d F Y");
                $emailBody .= "{$getExpiredCalibrations[$i]['deviceName']} - $displayDate<br>";
            }
        }
        
        if (!empty($getExpiredSubscriptions)) {
            $emailBody .= "<br><u>These devices are past their subscription end date. This may not be incorrect but double check it's up to date.</u><br>";
            for ($i = 0; $i < count($getExpiredSubscriptions); $i++) {
                    // Process date
                $displayDate = new DateTime($getExpiredSubscriptions[$i]['subFinish']);
                $displayDate = $displayDate->format("d F Y");
                $emailBody .= "{$getExpiredSubscriptions[$i]['deviceName']} - $displayDate<br>";
            }
        }


    }
}

// Send this to-do email to yeltech
$emailRecipients[] = 'info@yeltech.com';

// Send email if email check is true
if ($sendEmailCheck == true) {
    // echo $emailBody;
    sendEmail($emailRecipients, $emailSubject, $emailBody);
}

// Get devices with no calibration dates
function getMissingCalibration($groupId, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName
    FROM devices
    WHERE devices.nextCalibrationDue IS NULL AND devices.groupId = $groupId;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $returnDevices[] = $row;
        }
    }
    return $returnDevices;
}

// Get devices with subscription dates
function getMissingSubscriptions($groupId, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName
    FROM subscriptions
    LEFT JOIN devices ON subscriptions.deviceId = devices.deviceId
    WHERE subscriptions.subFinish IS NULL AND devices.groupId = $groupId;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $returnDevices[] = $row;
        }
    }
    return $returnDevices;
}

// Get devices with no latitude/longitude
function getMissingDeviceLocations($groupId, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName
    FROM devices
    WHERE (devices.latitude IS NULL OR devices.longitude IS NULL) AND devices.groupId = $groupId;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $returnDevices[] = $row;
        }
    }
    return $returnDevices;
}

// Get groups with no latitude/longitude
function checkLocationExists($groupId, $conn) {
    $sql = "
    SELECT 1
    FROM `groups`
    WHERE (`groups`.latitude IS NULL OR `groups`.longitude IS NULL) AND `groups`.groupId = $groupId;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        return false;
    } else {
        return true;
    }
}

// Get devices with expired calibration
function getExpiredCalibrations($groupId, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName, devices.nextCalibrationDue FROM devices 
    WHERE devices.nextCalibrationDue <= CURRENT_DATE()
    AND devices.groupId = $groupId;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $returnDevices[] = $row;
        }
    }
    return $returnDevices;
}

// Get devices with expired calibration
function getExpiredSubscriptions($groupId, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName, subscriptions.subFinish
    FROM subscriptions
    LEFT JOIN devices ON subscriptions.deviceId = devices.deviceId
    WHERE subscriptions.subFinish <= CURRENT_DATE() AND devices.groupId = $groupId;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $returnDevices[] = $row;
        }
    }
    return $returnDevices;
}

?>
