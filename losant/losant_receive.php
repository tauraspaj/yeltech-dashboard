<?php
session_start();
// Connect database
require_once './../includes/dbh.inc.php';

$json = file_get_contents('php://input');

// $json = '
// {
//     "macId":"8e7c56c922db",
//     "dataString":"//+OfFbJItsVARIjNEVWZ3iJmqu8zd7v8HELAODD",
//     "values":[
//         {
//             "channelName":"distance",
//             "measurement":"243",
//             "measurementTime":"2022-03-14 09:38:24"
//         },
//         {
//             "channelName":"convertedAmplitude",
//             "measurement":"153",
//             "measurementTime":"2022-03-14 09:38:24"
//         },
//         {
//             "channelName":"monotonic-counter",
//             "measurement":"203",
//             "measurementTime":"2022-03-14 09:38:24"
//         }
//     ]
// }
// ';

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
    $measurementTime = $value['measurementTime'];

    $sql = "
    INSERT INTO losantReadingsBuffer(channelName, measurement, measurementTime, macId) VALUES ('$channelName', $measurement, '$measurementTime', '$macId')
    ";
    $result = mysqli_query($conn, $sql);
}

exit();

// {
//     "macId": "8e7c56c922db",
//     "dataString": "//+OfFbJItsVARIjNEVWZ3iJmqu8zd7v8HELAODD",
//     "values": [
//         {
//             "channelName": "distance",
//             "measurement": "243",
//             "measurementTime": "2022-03-14 09:38:24"
//         },
//         {
//             "channelName": "convertedAmplitude",
//             "measurement": "153",
//             "measurementTime": "2022-03-14 09:38:24"
//         },
//         {
//             "channelName": "monotonic-counter",
//             "measurement": "203",
//             "measurementTime": "2022-03-14 09:38:24"
//         }
//     ]
// }

// REQUEST
// 'macId'
// 'messageString'
// readings: ['channelName', 'measurement', 'measurementTime']

// 1. Use macId to find deviceId

// 2. Insert into losantMessageHistory
// deviceId
// losantMessage

// 3. Insert into losantReadingsBuffer
// channelName
// measurement
// measurementTime
// macId

$json = file_get_contents('php://input');

// Converts it into a PHP object
$payload = json_decode($json, true);
$macId = $payload['macId'];
$dataString = $payload['dataString'];
$values = $payload['values'];

$current = "\n";

$current .= "macId: $macId <br>";
$current .= "dataString: $dataString <br>";

foreach ($values as $value) {
    $channelName = $value['channelName'];
    $measurement = $value['measurement'];
    $measurementTime = $value['measurementTime'];

    $current .= "Channel $channelName : measurement $measurement at $measurementTime<br><br>";
}

?>