<?php

// Connect database
require_once './../includes/dbh.inc.php';

// Plivo
require 'plivo_auth.php';

// Sender's phone numer
$from = $_REQUEST["From"];
$from = '+'.$from;

// Receiver's phone number - Plivo number
$to = $_REQUEST["To"];
$to = '+'.$to;

// The SMS text message which was received
$textBody = $_REQUEST["Text"];
$textBody = str_replace("\r", "\n", $textBody);

// Message UUID
$messageuuid = "Plivo ".$_REQUEST["MessageUUID"];

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