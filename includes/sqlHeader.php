<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

switch ($function) {
    case 'globalSearch':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $devicesGroup = $_SESSION['groupId'];
            $usersGroup = $_SESSION['groupId'];
            $groupsGroup = $_SESSION['groupId'];
        } else {
            $devicesGroup = 'devices.groupId';
            $usersGroup = 'users.groupId';
            $groupsGroup = '`groups`.groupId';
        }
    
        $searchString = $_POST['searchString'];

        $return = array();

        $sql = "
            SELECT 'device' AS type, devices.deviceId as id, devices.deviceName as name, devices.deviceAlias as aliasEmail
            FROM devices
            WHERE (devices.groupId = $devicesGroup) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%')
            UNION
            SELECT 'user' AS type, users.userId, users.fullName as fullName, users.email
            FROM users
            WHERE (users.groupId = $usersGroup) AND (users.fullName LIKE '%$searchString%' OR users.email LIKE '%$searchString%')
            UNION
            SELECT 'group' AS type, `groups`.groupId, `groups`.groupName, null
            FROM `groups`
            WHERE (`groups`.groupId = $groupsGroup) AND (`groups`.groupName LIKE '%$searchString%')
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
            LEFT JOIN alarmTriggers ON triggeredAlarmsHistory.triggerId = alarmTriggers.triggerId
            LEFT JOIN devices ON alarmTriggers.deviceId = devices.deviceId
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
    default:
        break;
}

?>