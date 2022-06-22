<?php

// ! This is a CRON JOB that should be set to run every 2 weeks
// ! It will loop through all groups, check for calibration dates that have expired and send out appropriate emails

// Connect database
require_once './../includes/dbh.inc.php';
// Email sending functionality
require './../mailer/mailer.php';

// Loop through all groups
$sql = "
SELECT groupId, groupName FROM `groups` ORDER BY groupId;
";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $thisGroupId = $row['groupId'];
        $thisGroupName = $row['groupName'];
        $thisEmailBody = '';
        $thisEmailSubject = '';
        
        // Email will not be sent if devices don't meet the conditions
        $sendEmailCheck = false;
        // Get expired calibrations
        $expiredCalibrations = getExpiredCalibrations($thisGroupId, $conn);
        // Check if there any any devices that need informing
        if (!empty($expiredCalibrations)) {
            $sendEmailCheck = true;
        }

        $thisEmailBody .= "Hello Group Admins of $thisGroupName!<br><br>";
        $thisEmailBody .= "This is an automated email to let you know that there are some devices that are now past their calibration date! <br>";

        $thisEmailBody .= "<br>The following devices need to be calibrated <b>as soon as possible</b>:<br>";
        for($i = 0; $i < count($expiredCalibrations); $i++) {
            // Process device alias
            $deviceAlias = '';
            if ($expiredCalibrations[$i]['deviceAlias'] != null && $expiredCalibrations[$i]['deviceAlias'] != '') {
                $deviceAlias = "({$expiredCalibrations[$i]['deviceAlias']})";
            }
            // Process date
            $displayDate = new DateTime($expiredCalibrations[$i]['nextCalibrationDue']);
            $displayDate = $displayDate->format("d F Y");
            $thisEmailBody .= "{$expiredCalibrations[$i]['deviceName']} $deviceAlias - <b><span style='color:red;'>$displayDate</span></b><br>";
        }
        
        $thisEmailBody .= "<br>In order to arrange collection of these devices, please get in touch with us at <b>info@yeltech.com</b>! We recommend to do this as soon as possible to ensure your devices are at the highest quality and the latest firmware!";

        // Choose subject
        $thisEmailSubject = "YELTECH - Your devices are now past their calibration date!";

        // Recipients are all group admins for each group
        $thisEmailRecipients = getGroupAdmins($thisGroupId, $conn);
        $thisEmailRecipients[] = 'info@yeltech.com';

        // Send email if email check is true
        if ($sendEmailCheck == true) {
            sendEmail($thisEmailRecipients, $thisEmailSubject, $thisEmailBody);
        }
    }
}

function getExpiredCalibrations($groupId, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName, devices.deviceAlias, devices.nextCalibrationDue FROM devices 
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

function getGroupAdmins($groupId, $conn) {
    $groupAdmins = array();
    $sql = "
    SELECT users.email 
    FROM users
    WHERE users.roleId = 3 AND users.groupId = $groupId
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $groupAdmins[] = $row['email'];
        }
    }
    return $groupAdmins;
}
?>
