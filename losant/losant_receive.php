<?php
session_start();
// Connect database
require_once './../includes/dbh.inc.php';

$json = file_get_contents('php://input');

$payload = json_decode($json, true);
$macId = $payload['macId'];
$dataString = $payload['dataString'];
$values = $payload['values'];

// 1. Use macId to find deviceId
$sql = "
SELECT deviceId FROM deviceMacId WHERE deviceMACId='$macId'
";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deviceId = $row['deviceId'];
    }
}

// 2. Insert into losantMessageHistory
// deviceId
// losantMessage
$sql = "
INSERT INTO losantMessageHistory (deviceId, losantMessage) VALUES ($deviceId, '$dataString')
";
$result = mysqli_query($conn, $sql);

// 3. Insert into losantReadingsBuffer
// channelName
// measurement
// measurementTime
// macId

foreach ($values as $value) {
    $channelName = $value['channelName'];
    $measurement = $value['measurement'];
    if ($measurement != "") {
        $measurement = intval($measurement);
        $measurementTime = $value['measurementTime'];
    
        $sql = "
        INSERT INTO losantReadingsBuffer(channelName, measurement, measurementTime, macId) VALUES ('$channelName', $measurement, '$measurementTime', '$macId')
        ";
        $result = mysqli_query($conn, $sql);
    }
}

?>