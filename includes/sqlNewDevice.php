<?php 
require_once 'dbh.inc.php';
session_start();

$function = $_POST['function'];

if ($function == 'checkIfExists') {
    $checkInput = $_POST['checkInput'];
    $inTable = $_POST['inTable'];
    $inColumn = $_POST['inColumn'];
    $result = mysqli_query($conn, "SELECT 1 FROM $inTable WHERE $inColumn LIKE '%$checkInput'");
    echo mysqli_num_rows($result);
    exit();
} elseif ($function == 'create') {
    $productTypeId = $_POST['productType'];
    $deviceTypeId = $_POST['deviceType'];
    $deviceName = $_POST['deviceName'];
    $devicePhone = $_POST['devicePhone'];

    if ($_POST['nextCalibrationDate'] == '' ? $nextCalibrationDue = null : $nextCalibrationDue = $_POST['nextCalibrationDate']);
    if ($_POST['lastCalibrationDate'] == '' ? $lastCalibration = null : $lastCalibration = $_POST['lastCalibrationDate']);
    if ($_POST['subStart'] == '' ? $subStart = null : $subStart = $_POST['subStart']);
    if ($_POST['subFinish'] == '' ? $subFinish = null : $subFinish = $_POST['subFinish']);

    $groupId = $_POST['groupId'];
    $allChannels = json_decode($_POST['submitChannels'], true);
    $createdBy = $_SESSION['fullName'];

    $sql = "INSERT INTO devices (deviceName, deviceTypeId, productId, devicePhone, groupId, createdBy, lastCalibration, nextCalibrationDue) VALUES (?, ?, ?, ?, ?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", $deviceName, $deviceTypeId, $productTypeId, $devicePhone, $groupId, $createdBy, $lastCalibration, $nextCalibrationDue);
    mysqli_stmt_execute($stmt);

    $newDeviceId = '';
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $newDeviceId = mysqli_insert_id($conn);
    }

    // Only create subscriptions and channels if device has been successfully created
    if ($newDeviceId != '') {
        $sql = "INSERT INTO subscriptions (deviceId, subStart, subFinish) VALUES (?, ?, ?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $newDeviceId, $subStart, $subFinish);
        mysqli_stmt_execute($stmt);

        $sql = "INSERT INTO channels (channelName, unitId, deviceId, channelType) VALUES (?, ?, ?, ?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $channelName, $unitId, $deviceId, $channelType);
    
        foreach ($allChannels as $channel) {
            $deviceId = $newDeviceId;

            $channelName = $channel['channelName'];
            $channelType = $channel['channelTypeId'];

            if ($channel['unitId'] == '') {
                $unitId = null;
            } else {
                $unitId = $channel['unitId'];
            }
    
            mysqli_stmt_execute($stmt);
        }
    
        echo 'Device has been registered!';
        mysqli_stmt_close($stmt);
    }

    // TILT devices need their dashboard settings too
    $sql = "
    SELECT productId FROM products WHERE productName='TILT' LIMIT 1;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tiltProductId = $row['productId'];
        }
    }
    if ($productTypeId == $tiltProductId) {
        $sql = "INSERT INTO tiltDashboardSettings (deviceId) VALUES (?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "s", $newDeviceId);
        mysqli_stmt_execute($stmt);
    }

    // 1. Check if BWM RADAR
    // 2. If it is, initialise dashboardImage table
    // 3. if it is, get its analog channels and initialise dashboardMovableBox table
    $sql = "
    SELECT productId FROM products WHERE productName='BWM RADAR' LIMIT 1;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bwmRadarProductId = $row['productId'];
        }
    }
    if ($productTypeId == $bwmRadarProductId) {
        // Initialise dashboardImageTable
        $sql = "INSERT INTO dashboardImage (deviceId) VALUES (?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "s", $newDeviceId);
        mysqli_stmt_execute($stmt);

        // Initialise dashboardMovableBox
        $chanArr = array();
        $sql = "
        SELECT channelId FROM channels WHERE deviceId=$newDeviceId
        ";
        $result = mysqli_query($conn, $sql);
        if ( mysqli_num_rows($result) > 0 ) {
            while ($row = mysqli_fetch_assoc($result)) {
                $chanArr[] = $row['channelId'];
            }
        }
        // Insert analog channels
        for($i = 0; $i < count($chanArr); $i++) {
            $sql = "INSERT INTO dashboardMovableBox (deviceId, channelId) VALUES (?, ?);";
            $stmt = mysqli_stmt_init($conn);
            mysqli_stmt_prepare($stmt, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $newDeviceId, $chanArr[$i]);
            mysqli_stmt_execute($stmt);
        }
    }

    // Initialise 4 formulas for BSC BWM
    $sql = "
    SELECT productId FROM products WHERE productName='BWM' LIMIT 1;
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bwmProductId = $row['productId'];
        }
    }
    
    if ($productTypeId == $bwmProductId) {
        // Initialise dashboardImageTable
        $sql = "INSERT INTO dashboardImage (deviceId) VALUES (?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "s", $newDeviceId);
        mysqli_stmt_execute($stmt);

        // Initialise 4 formulas
        $sql = "INSERT INTO bwmFormulas (deviceId) VALUES (?);";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "s", $newDeviceId);
        mysqli_stmt_execute($stmt);
    }
}
exit();
?>
