<?php 
/*
 * This file will be run every minute (CRON job)
 * All the records will be checked in pendingMessages table
 * Messages will be processed
 * pendingMessages table will then be cleared out
*/

// Connect database
require_once './../includes/dbh.inc.php';

// Includes
require_once './rtmuMessage.php';

// Function to move message from pendingMessages table to messages
function pendingToMessages($conn, $message) {
    $from = $message['fromNumber'];
    $to = $message['toNumber'];
    $textBody = $message['textBody'];
    $timeSent = $message['timeSent'];
    $messageuuid = $message['messageuuid'];
    
    // Work out message type
    $tempData = explode("\n", $textBody);
    if ($tempData[1] == 'DWTS' || $tempData[1] == 'DATA') {
        $msgType = 'SMS DATA';
    } elseif ($tempData[1] == 'ALARM MESSAGE') {
        $msgType = 'SMS ALARM';
    } elseif ($tempData[1] == 'STATUS MESSAGE') {
        $msgType = 'SMS STATUS';
    } else {
        $msgType = 'SMS UNDEFINED';
    }

    $sqlAddToMessages = "INSERT INTO messages (fromNumber, toNumber, textBody, messageType, timeSent, messageuuid) VALUES (?, ?, ?, ?, ?, ?);";
    $stmtAddToMessages = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmtAddToMessages, $sqlAddToMessages)) {
        echo("Prepare error");
    }
    if(!mysqli_stmt_bind_param($stmtAddToMessages, "ssssss", $from, $to, $textBody, $msgType, $timeSent, $messageuuid)){
        echo("Bind error");
    }
    if(!mysqli_stmt_execute($stmtAddToMessages)) {
        echo("Execute error");
    }
}

// Get all the messages into an array
$sql = "
    SELECT pendingMessageId, fromNumber, toNumber, textBody, messageuuid, timeSent FROM pendingMessages ORDER BY pendingMessageId ASC
";
$result = mysqli_query($conn, $sql);
$allMessages = array();
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $allMessages[] = $row;
    }
}

// Loop through all messages and process them. Keep track of processed ids since they will be deleted
$idsList = array();
foreach ($allMessages as $message) {
    // Move each message from pendingMessages table to messages table
    pendingToMessages($conn, $message);

    // Process the message
    processRtmuMessage( $conn, $message );

    // Make a list of processed messages
    $idsList[] = $message['pendingMessageId'];
}

// DELETE processed ids
$idsList = implode(', ', $idsList);
$sqlDelete = "
DELETE FROM pendingMessages WHERE pendingMessageId IN ($idsList)
";
mysqli_query($conn, $sqlDelete)

?>