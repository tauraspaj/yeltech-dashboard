<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

switch ($function) {
    case 'globalSearch':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $devicesGroup = $_SESSION['groupId'];
        } else {
            $devicesGroup = 'devices.groupId';
        }
    
        $searchString = $_POST['searchString'];

        $return = array();

        $sql = "
            SELECT 'device' AS type, devices.deviceId as id, devices.deviceName as name, devices.deviceAlias as aliasEmail
            FROM devices
            WHERE (devices.groupId = $devicesGroup) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%')
            LIMIT 3;
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                $return[] = $row;
            }
        }

        echo json_encode($return);
        break;
    case 'getAlarmedDevices':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $groupFilter = $_SESSION['groupId'];
        } else {
            $groupFilter = 'devices.groupId';
        }
        
        $sql = "
            SELECT devices.deviceId, devices.deviceName, devices.deviceAlias, COUNT(alarmTriggers.isTriggered) as nAlarmsTriggered
            FROM devices
            LEFT JOIN alarmTriggers ON devices.deviceId = alarmTriggers.deviceId
            WHERE devices.groupId = $groupFilter AND alarmTriggers.isTriggered = 1
        ";

        $result = mysqli_query($conn, $sql);
        $triggeredDevices = array();
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                $triggeredDevices[] = $row;
            }
        }

        echo json_encode($triggeredDevices);
        break;
    
    case 'getHomeData':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $devicesGroup = $_SESSION['groupId'];
            $usersGroup = $_SESSION['groupId'];
        } else {
            $devicesGroup = 'devices.groupId';
            $usersGroup = 'users.groupId';
        }

        $return = array();

        $sql = "
            SELECT COUNT(*) as totalDevices FROM devices WHERE devices.groupId = $devicesGroup
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                $return['totalDevices'] = $row['totalDevices'];
            }
        }

        $sql2 = "
            SELECT COUNT(*) as totalUsers FROM users WHERE users.groupId = $usersGroup
        ";
        $result2 = mysqli_query($conn, $sql2);
        if ( mysqli_num_rows($result2) > 0 ) {
            while ($row = mysqli_fetch_assoc($result2)) {
                $return['totalUsers'] = $row['totalUsers'];
            }
        }

        $sql3 = "
            SELECT COUNT(*) as totalAlarms
            FROM triggeredAlarmsHistory
            LEFT JOIN devices ON triggeredAlarmsHistory.deviceId = devices.deviceId
            WHERE devices.groupId = $devicesGroup
        ";
        $result3 = mysqli_query($conn, $sql3);
        if ( mysqli_num_rows($result3) > 0 ) {
            while ($row = mysqli_fetch_assoc($result3)) {
                $return['totalAlarms'] = $row['totalAlarms'];
            }
        }

        echo json_encode($return);
        break;
    
    case 'getDeviceCoordinates':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $devicesGroup = $_SESSION['groupId'];
        } else {
            $devicesGroup = 'devices.groupId';
        }
        $return = array();

        $sql = "
            SELECT deviceId, deviceName FROM devices WHERE devices.groupId = $devicesGroup;
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                
                $sql2 = "
                    SELECT smsStatus.latitude, smsStatus.longitude 
                    FROM smsStatus 
                    WHERE ((latitude IS NOT NULL AND longitude IS NOT NULL) AND (deviceId = {$row['deviceId']})) 
                    ORDER BY smsStatusId DESC LIMIT 1;
                ";

                $result2 = mysqli_query($conn, $sql2);
                if ( mysqli_num_rows($result2) > 0 ) {
                    while ($row2 = mysqli_fetch_assoc($result2)) {
                        $row['latitude'] = $row2['latitude'];
                        $row['longitude'] = $row2['longitude'];
                    }
                } else {
                    $row['latitude'] = null;
                    $row['longitude'] = null;
                }

                $return[] = $row;
            }
        }

        echo json_encode($return);
        break;
        
    case 'saveProfileChanges':
        $userId = $_POST['userId'];
        $fullName = $_POST['fullName'];
        $sendingId = $_POST['sendingId'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phoneNumber'];
        $newPassword = $_POST['newPassword'];
        $password = $_POST['password'];

        $response = array();

        // Retrieve user's old password
        $sql = "
        SELECT pwd FROM users WHERE userId = $userId;
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                $pwdHashed = $row['pwd'];
            }
        }

        // Firstly check if old password is correct before making any changes
        if (!password_verify($password, $pwdHashed)) {
            $response['status'] = 'Error';
            $response['message'] = 'Entered password is incorrect!';
            echo json_encode($response);
            break;
        }

        // Check if entered email is unique
        if ($email != $_SESSION['email']) {
            $sql2 = "
                SELECT COUNT(*) as emailCount FROM users WHERE email = '$email'
            ";
            $result2 = mysqli_query($conn, $sql2);
            if ( mysqli_num_rows($result) > 0 ) {
                while ($row = mysqli_fetch_assoc($result2)) {
                    $emailCount = $row['emailCount'];
                }
            }

            if ($emailCount != 0) {
                $response['status'] = 'Error';
                $response['message'] = 'This email already exists in the database!';
                echo json_encode($response);
                break;
            }
        }

        // If phone number is empty, set it to null
        if ($phoneNumber == '') {
            $phoneNumber = null;
        }

        // If new password is not empty, update it
        if ( !empty($newPassword) ) {
            if( strlen($newPassword) <= 6 ) {
                $response['status'] = 'Error';
                $response['message'] = 'New password must be longer than 6 characters!';
                echo json_encode($response);
                break;
            } else {
                $sql = "UPDATE users SET fullName=?, email=?, phoneNumber=?, sendingId=?, pwd=? WHERE userId=?";
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "ssssss", $fullName, $email, $phoneNumber, $sendingId, $newPasswordHash, $userId);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_error($stmt)) {
                    $response['status'] = 'Error';
                    $response['message'] = 'Something went wrong!';
                } else {
                    $response['status'] = 'OK';
                    $response['message'] = 'Success!';
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // Update values
            $sql = "UPDATE users SET fullName=?, email=?, phoneNumber=?, sendingId=? WHERE userId=?";
            $stmt = mysqli_stmt_init($conn);
            mysqli_stmt_prepare($stmt, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $fullName, $email, $phoneNumber, $sendingId, $userId);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_error($stmt)) {
                $response['status'] = 'Error';
                $response['message'] = 'Something went wrong!';
            } else {
                $response['status'] = 'OK';
                $response['message'] = 'Success!';
            }
            mysqli_stmt_close($stmt);
        }

        echo json_encode($response);

        break;

        default:
        break;
}

?>