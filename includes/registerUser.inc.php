<?php
session_start();

if (isset($_POST["submit"])) {
	
	$userName = $_POST["userName"];
	$userRole = $_POST["userRole"];
	$userGroup = $_POST["userGroup"];
	$userEmail = $_POST["userEmail"];
	$userPhone = $_POST["userPhone"];
	$userPwd = $_POST["userPwd"];
	$userConfPwd = $_POST["userConfPwd"];

	require_once 'dbh.inc.php';

	// Checks:
		// Empty fields
		// Email that already exists
		// Password atleast 6 characters
		// Incorrect matching password

	$registerErrors = array(
		"messages" => array(),
		"fields" => array()
	);

	// Check for empty userName
	if (empty($userName)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userName", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userName";
		}
	}

	// Check for empty userRole
	if (empty($userRole)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userRole", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userRole";
		}
	}

	if (empty($userGroup)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userGroup", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userGroup";
		}
	}

	// Check for empty userEmail
	if (empty($userEmail)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userEmail", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userEmail";
		}
	}

	// Check for empty userPhone
	if (empty($userPhone)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userPhone", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userPhone";
		}
	}

	// Check for empty userPwd
	if (empty($userPwd)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userPwd", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userPwd";
		}
	}

	// Check for empty userConfPwd
	if (empty($userConfPwd)) {
		if (!in_array("Please fill in all fields!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please fill in all fields!";
		}

		if (!in_array("userConfPwd", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userConfPwd";
		}
	}

	// Use role must be in the 1-4 range
	if ($userRole != '4' && $userRole != '3' && $userRole != '2' && $userRole != '1') {
		if (!in_array("You have entered an invalid role!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "You have entered an invalid role!";
		}

		if (!in_array("userRole", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userRole";
		}
	}

	// Use group value must not be -1
	if ($userGroup == '-1') {
		if (!in_array("Please choose a group!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Please choose a group!";
		}

		if (!in_array("userGroup", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userGroup";
		}
	}

	// Check for email that already exists
	$sql = "SELECT * FROM users WHERE email = ?;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		$registerErrors["messages"][] = "Something went wrong!";
		header("location: ../newuser.php");
		exit();
	}

	mysqli_stmt_bind_param($stmt, "s", $userEmail);
	mysqli_stmt_execute($stmt);

	$resultData = mysqli_stmt_get_result($stmt);

	if ($row = mysqli_fetch_assoc($resultData)) {
		$registerErrors["messages"][] = "This email already exists!";
	} 
	mysqli_stmt_close($stmt);


	// Check for password atleast 6 characters
	if (strlen($userPwd) <= 6) {
		if (!in_array("Password must be longer than 6 characters!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Password must be longer than 6 characters!";
		}

		if (!in_array("userPwd", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userPwd";
		}
	}

	// Check for incorrect matching password
	if ($userPwd !== $userConfPwd) {
		if (!in_array("Both passwords have to match!", $registerErrors["messages"])) {
			$registerErrors["messages"][] = "Both passwords have to match!";
		}

		if (!in_array("userPwd", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userPwd";
		}

		if (!in_array("userConfPwd", $registerErrors["fields"])){
			$registerErrors["fields"][] = "userConfPwd";
		}
	}

	if (!empty($registerErrors["messages"]) || !empty($registerErrors["fields"])) {
		$_SESSION["registerErrors"] = $registerErrors;
		$_SESSION["enteredValues"] = array('userName' => $userName, 'userRole' => $userRole, 'userGroup' => $userGroup,'userEmail' => $userEmail, 'userPhone' => $userPhone);
		header("location: ../newuser.php");
		exit();
	} else {
		// Register user
		$sql = "INSERT INTO users (roleId, fullName, groupId, email, phoneNumber, pwd, createdBy) VALUES (?, ?, ?, ?, ?, ?, ?);";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql)) {
			$registerErrors["messages"][] = "Something went wrong!";
			$_SESSION["registerErrors"] = $registerErrors;
			$_SESSION["enteredValues"] = array('userName' => $userName, 'userRole' => $userRole, 'userGroup' => $userGroup,'userEmail' => $userEmail, 'userPhone' => $userPhone);
			header("location: ../newuser.php");
			exit();
		}

		// Created by is the id of creator's session
		$createdBy = $_SESSION["userId"];
		$hashedPwd = password_hash($userPwd, PASSWORD_DEFAULT);
		mysqli_stmt_bind_param($stmt, "sssssss", $userRole, $userName, $userGroup, $userEmail, $userPhone, $hashedPwd, $createdBy);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);

		$_SESSION["successMessage"] = "User has been successfully registered!";
		header("location: ../users.php");
		exit();
	}

	
} else {
	header("location: ../newuser.php");
	exit();
}