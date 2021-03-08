<?php
session_start();

require_once 'dbh.inc.php';
require_once 'cookieFunctions.inc.php';

if (isset($_COOKIE["remember"])) {
	deleteCookie($conn, $_SESSION["userId"]);
}

session_unset();
session_destroy();


header("location: ../login.php");
exit();