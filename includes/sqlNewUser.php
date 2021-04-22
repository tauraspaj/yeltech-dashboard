<?php 
require_once 'dbh.inc.php';
session_start();

$fullName = ucwords($_POST['fullName']);
$roleId = $_POST['roleId'];
$email = $_POST['email'];
$groupId = $_POST['groupId'];
$phoneNumber = $_POST['phoneNumber'];
$password = $_POST['password'];
$confPassword = $_POST['confPassword'];

// Validation
// - No empty fields
// - Unique email
// - Password longer than 6 chars
// - Both passwords match

if ( empty($fullName) || empty($roleId) || empty($email) || empty($groupId) || empty($password) || empty($confPassword) ) {
    $response['status'] = 'Error';
    $response['fields'] = null;
    $response['message'] = 'Required fields must be filled in!';

    echo json_encode($response);
    exit();
}

$sql = "
    SELECT COUNT(userId) as count FROM users WHERE email = '$email'
";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['count'] > 0 ) {
        $response['status'] = 'Error';
        $response['message'] = 'Email already exists!';

        echo json_encode($response);
        exit();
        }
    }
}

if ( strlen($password) <= 6) {
    $response['status'] = 'Error';
    $response['message'] = 'Password must be longer than 6 characters!';

    echo json_encode($response);
    exit();
}

if ( $password != $confPassword ) {
    $response['status'] = 'Error';
    $response['message'] = 'Both passwords must match!';

    echo json_encode($response);
    exit();
}

// Register user
$sql = "INSERT INTO users (roleId, fullName, groupId, email, phoneNumber, pwd, createdBy) VALUES (?, ?, ?, ?, ?, ?, ?);";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    $response['status'] = 'Error';
    $response['message'] = 'Something went wrong!';

    echo json_encode($response);
    exit();
}

if ($phoneNumber == '') {
    $phoneNumber = null;
}

// Created by is the id of creator's session
$createdBy = $_SESSION['fullName'];
$hashedPwd = password_hash($password, PASSWORD_DEFAULT);
mysqli_stmt_bind_param($stmt, "sssssss", $roleId, $fullName, $groupId, $email, $phoneNumber, $hashedPwd, $createdBy);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$response['status'] = 'OK';
$response['message'] = 'Success!';

echo json_encode($response);
exit();

?>