<?php

// ! This is a CRON JOB that should be set to run weekly
// ! It will loop through all groups, check for calibration dates that will expire in 1 or 3 months and send out appropriate emails

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
        // Check for expired devices between the next 27 and 34 days
        $warningMonth1 = getCalibrationDates($thisGroupId,'27 DAY', '34 DAY', $conn);
        // Check for expired devices between the next 87 and 94 days
        $warningMonth3 = getCalibrationDates($thisGroupId,'87 DAY', '94 DAY', $conn);
        // Check if there any any devices that need informing
        if (!empty($warningMonth1) || !empty($warningMonth3) ) {
            $sendEmailCheck = true;
        }

        $thisEmailBody .= "Hello Group Admins of $thisGroupName!<br><br>";
        $thisEmailBody .= "This is an automated email to let you know that there are some devices with a calibration date that is due soon! <br>";

        if (!empty($warningMonth1)) {
            $thisEmailBody .= "<br>The following devices need to be calibrated in the <b>next month</b>:<br>";
            for($i = 0; $i < count($warningMonth1); $i++) {
                // Process device alias
                $deviceAlias = '';
                if ($warningMonth1[$i]['deviceAlias'] != null && $warningMonth1[$i]['deviceAlias'] != '') {
                    $deviceAlias = "({$warningMonth1[$i]['deviceAlias']})";
                }
                // Process date
                $displayDate = new DateTime($warningMonth1[$i]['nextCalibrationDue']);
                $displayDate = $displayDate->format("d F Y");
                $thisEmailBody .= "{$warningMonth1[$i]['deviceName']} $deviceAlias - <b><span style='color:red;'>$displayDate</span></b><br>";
            }
        }

        if (!empty($warningMonth3)) {
            $thisEmailBody .= "<br>The following devices need to be calibrated in the <b>next 3 months</b>:<br>";
            for($i = 0; $i < count($warningMonth3); $i++) {
                // Process device alias
                $deviceAlias = '';
                if ($warningMonth3[$i]['deviceAlias'] != null && $warningMonth3[$i]['deviceAlias'] != '') {
                    $deviceAlias = "({$warningMonth3[$i]['deviceAlias']})";
                }
                // Process date
                $displayDate = new DateTime($warningMonth3[$i]['nextCalibrationDue']);
                $displayDate = $displayDate->format("d F Y");
                $thisEmailBody .= "{$warningMonth3[$i]['deviceName']} $deviceAlias - <b><span style='color:orange;'>$displayDate</span></b><br>";
            }
        }

        $thisEmailBody .= "<br>In order to arrange collection of these devices, please get in touch with us at <b>info@yeltech.com</b>! We recommend to do this as soon as possible to ensure your devices are at the highest quality and the latest firmware!";

        // echo "$thisEmailBody";

        // Choose subject
        $thisEmailSubject = "YELTECH - Your devices are due for calibration soon!";

        // Recipients are all group admins for each group
        $thisEmailRecipients = getGroupAdmins($thisGroupId, $conn);
        $thisEmailRecipients[] = 'tauras@yeltech.co.uk';
        $thisEmailRecipients[] = 'info@yeltech.com';

        // Send email if email check is true
        if ($sendEmailCheck == true) {
            sendEmail($thisEmailRecipients, $thisEmailSubject, $thisEmailBody);
        }
    }
}

// Return a list of devices with selected interval from and to
function getCalibrationDates($groupId, $intervalFrom, $intervalTo, $conn) {
    $returnDevices = array();
    $sql = "
    SELECT devices.deviceName, devices.deviceAlias, devices.nextCalibrationDue FROM devices 
    WHERE devices.nextCalibrationDue >= DATE_ADD(CURRENT_DATE( ), INTERVAL $intervalFrom) AND devices.nextCalibrationDue <= DATE_ADD(CURRENT_DATE( ), INTERVAL $intervalTo) AND devices.groupId = $groupId;
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