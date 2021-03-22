<?php
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

switch ($function) {
    case 'globalSearch':
        if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
            $groupFilter = $_SESSION['groupId'];
        } else {
            $groupFilter = 'groupId';
        }
    
        $searchString = $_POST['searchString'];

        $return = array();

        $sql = "
            SELECT 'device' AS type, devices.deviceId as id, devices.deviceName as name, devices.deviceAlias as aliasEmail
            FROM devices
            WHERE (devices.groupId = devices.$groupFilter) AND (devices.deviceName LIKE '%$searchString%' OR devices.deviceAlias LIKE '%$searchString%')
            UNION
            SELECT 'user' AS type, users.userId, users.fullName as fullName, users.email
            FROM users
            WHERE (users.groupId = users.$groupFilter) AND (users.fullName LIKE '%$searchString%' OR users.email LIKE '%$searchString%')
            UNION
            SELECT 'group' AS type, `groups`.groupId, `groups`.groupName, null
            FROM `groups`
            WHERE (`groups`.groupId = `groups`.$groupFilter) AND (`groups`.groupName LIKE '%$searchString%')
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
}

?>