<?php

// Connect database
require_once './../includes/dbh.inc.php';

$data = file_get_contents('https://api2.yelcloud.com/getftpreadings/100');
$data = json_decode($data, true);

$lastKeyNum = getLastKeyNum($conn);

$updatedLastKeyNum = 0;

// print_r($data);
// echo count($data);

for ($i = count($data)-1; $i >= 0; $i--) {
    // Ensure current data being processed is not duplicate
    if ($data[$i]['KeyNum'] > $lastKeyNum) {
        $thisKeyNum = $data[$i]['KeyNum'];
        $macId = $data[$i]['PhoneNumber'];
        $channelName = $data[$i]['ChannelName'];
        $measurement = round($data[$i]['MeasValue'], 3);

        // Measurement time comes in as '2022-10-06 14:38:36.000' so let's cut off the last 3 '.000'
        $measurementTime = substr($data[$i]['MeasDate'],0, -4);

        // Send to database
        if (strtoupper($channelName) == 'RAIL TEMP' && $data[$i]['MeasValue'] != null) {
            $sql = "
            INSERT INTO losantReadingsBuffer(channelName, measurement, measurementTime, macId) VALUES ('$channelName', $measurement, '$measurementTime', '$macId')
            ";
            $result = mysqli_query($conn, $sql);
        }

        if ($updatedLastKeyNum < $thisKeyNum) {
            $updatedLastKeyNum = $thisKeyNum;
        }        
    }
}

// Update KeyNum with the latest value processed
if ($updatedLastKeyNum > 0) {
    $sql = "
        UPDATE ftpTracker SET lastKeyNum = $updatedLastKeyNum WHERE trackerId=0;
    ";
    $result = mysqli_query($conn, $sql);
}


function getLastKeyNum($conn) {
    // Get latest KeyNum from database
    $sql = "
    SELECT lastKeyNum FROM ftpTracker WHERE trackerId = 0
    ";
    $result = mysqli_query($conn, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['lastKeyNum'] > 0) {
                return $row['lastKeyNum'];
            }
        }
    }
}

?>
