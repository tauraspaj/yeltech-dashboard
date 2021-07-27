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
            SELECT DISTINCT alarmTriggers.deviceId, devices.groupId, devices.deviceName, devices.deviceAlias
            FROM alarmTriggers
            LEFT JOIN devices ON alarmTriggers.deviceId = devices.deviceId
            WHERE alarmTriggers.isTriggered = 1 AND devices.groupId = $groupFilter
        ";

        $result = mysqli_query($conn, $sql);
        $triggeredDevices = array();
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {

                // For each device, check how many active alarms it has
                $sql2 = "
                SELECT COUNT(triggerId) AS nAlarmsTriggered FROM alarmTriggers WHERE isTriggered = 1 AND deviceId = {$row['deviceId']};
                ";
                $result2 = mysqli_query($conn, $sql2);
                if ( mysqli_num_rows($result2) > 0 ) {
                    while ($row2 = mysqli_fetch_assoc($result2)) {
                        $row['nAlarmsTriggered'] = $row2['nAlarmsTriggered'];
                    }
                }

                $triggeredDevices[] = $row;
            }
        }

        echo json_encode($triggeredDevices);
        break;
    

    
    case 'getDeviceCoordinates':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $devicesGroup = $_SESSION['groupId'];
        } else {
            $devicesGroup = 'devices.groupId';
        }
        $return = array();

        $sql = "
            SELECT deviceId, deviceName, latitude, longitude FROM devices WHERE devices.groupId = $devicesGroup AND latitude IS NOT NULL AND longitude IS NOT NULL;
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
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
            if ( mysqli_num_rows($result2) > 0 ) {
                while ($row = mysqli_fetch_assoc($result2)) {
                    $emailCount = $row['emailCount'];
                }
            }

            if ($emailCount != 0) {
                $response['status'] = 'Error';
                $response['message'] = 'This email already exists!';
                echo json_encode($response);
                break;
            }
        }

        // Check for unique phone number
        if ($phoneNumber == '') {
            $phoneNumber = null;
        } else {
            if ($phoneNumber != $_SESSION['phoneNumber']) {
                $sql3 = "
                SELECT COUNT(userId) as count FROM users WHERE phoneNumber = '$phoneNumber'
                ";
                $result3 = mysqli_query($conn, $sql3);
                if ( mysqli_num_rows($result3) > 0 ) {
                    while ($row = mysqli_fetch_assoc($result3)) {
                        if ($row['count'] > 0 ) {
                            $response['status'] = 'Error';
                            $response['message'] = 'Phone number already exists!';
    
                            echo json_encode($response);
                            exit();
                        }
                    }
                }
            }
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

    case 'getGroupCoords':
        $groupId = $_SESSION['groupId'];

        $sql = "
            SELECT `groups`.latitude, `groups`.longitude FROM `groups` WHERE `groups`.groupId = $groupId
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo json_encode($row);
            }
        }
        break;

        default:
        break;


    }

?>