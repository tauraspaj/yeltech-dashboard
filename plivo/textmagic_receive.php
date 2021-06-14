<?php

// Connect database
require_once './../includes/dbh.inc.php';


// Sender's phone numer
$from = $_POST["sender"];
$from = '+'.$from;

// Receiver's phone number - Plivo number
$to = $_POST["receiver"];
$to = '+'.$to;

// The SMS text message which was received
$textBody = $_POST["text"];
// $textBody = "RTMU 2828\nALARM MESSAGE\nRail Temp\nRAIN START\n23.8 %";

$messageuuid = $_POST["id"];
// $messageuuid = "332cd187-c84f-11eb-bdf7-2255312422434s3d21d1s1716";

// Date of the message
$timeSent = date('Y-m-d H:i:s');

$sql = "INSERT INTO pendingMessages (toNumber, fromNumber, textBody, messageuuid, timeSent) VALUES (?, ?, ?, ?, ?);";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    echo("Prepare error");
    exit();
}
if(!mysqli_stmt_bind_param($stmt, "sssss", $to, $from, $textBody, $messageuuid, $timeSent)){
    echo("Bind error");
    exit();
}
if(!mysqli_stmt_execute($stmt)) {
    echo("Execute error");
    exit();
}

mysqli_stmt_close($stmt);

?>