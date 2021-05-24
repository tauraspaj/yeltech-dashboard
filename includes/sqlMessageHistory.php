<?php
require_once 'dbh.inc.php';
session_start();

$searchPhone = $_POST['searchPhone'];
$msgPerPage = $_POST['msgPerPage'];
$offset = $_POST['offset'];

if (!preg_match('/^[0-9+]+$/', $searchPhone)) {
    exit();
}

$sql = "
SELECT messages.fromNumber, messages.toNumber, messages.textBody, messages.messageType, messages.timeSent
FROM messages
WHERE messages.fromNumber = $searchPhone
LIMIT $msgPerPage OFFSET $offset
";
$return = array();
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        $return[] = $row;
    }
}

$sqlTotal = "
SELECT COUNT(*) as totalRows FROM messages WHERE messages.fromNumber = $searchPhone
";

$result = mysqli_query($conn, $sqlTotal);
$resultCheck = mysqli_num_rows($result);

if ($resultCheck > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $return[] = $row;
    }
}

echo json_encode($return);
exit();
?>