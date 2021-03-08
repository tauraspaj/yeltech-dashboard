<?php
function generateRandom($length) {
	return bin2hex(random_bytes($length));
}

function cookieExists($conn, $selector) {
	$sql = "SELECT * FROM auth_tokens WHERE selector = ?;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		header("location: ../login.php");
		exit();
	}

	mysqli_stmt_bind_param($stmt, "s", $selector);
	mysqli_stmt_execute($stmt);

	$resultData = mysqli_stmt_get_result($stmt);

	if ($row = mysqli_fetch_assoc($resultData)) {
		return $row;
	} else {
		$result = false;
		return $result;
	}

	mysqli_stmt_close($stmt);
}

function createRememberMeCookie($conn, $userId) {
	// Check if remember cookie already exists
	if (!isset($_COOKIE['remember'])) {
		// 1. Create a unique selector
		$bool = true;
		while ($bool === true) {
			$selector = generateRandom(8);

			if (cookieExists($conn, $selector) !== false) {
				$bool = true;
			} elseif (cookieExists($conn, $selector) === false) {
				$bool = false;
			}
		}

		// 2. Create a random validator
		$validator = generateRandom(20);
		$hashedValidator = hash("SHA256", $validator);

		// 2. Add hashed validator to the database (SHA-256)
		$sql = "INSERT INTO auth_tokens (selector, hashedValidator, userId, activeFrom, activeTo) VALUES (?, ?, ?, ?, ?);";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql)) {
			header("location: ../login.php");
			exit();
		}

		// Expiry time: Current time + 1 week in seconds
		$activeFrom = time();
		$activeFrom = date("Y-m-d H:i:s", "$activeFrom");
		$activeTo = time()+(60*60*24*7); 
		$activeTo = date("Y-m-d H:i:s", "$activeTo");

		mysqli_stmt_bind_param($stmt, "sssss", $selector, $hashedValidator, $userId, $activeFrom, $activeTo);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);

		// 3. Create COOKIE with selector:UnhashedValidator
		$cookieString = $selector.':'.$validator;
		$cookieName = 'remember';
		setcookie($cookieName, $cookieString, $activeTo, '/');
	}
}

function verifyRememberCookie($conn, $cookieString) {
	$selector = explode(':', $cookieString)[0];
	$validator = explode(':', $cookieString)[1];

	$row = cookieExists($conn, $selector);
	if ($row) {
		// $hashCheck = hash_equals($row['hashedValidator'], hash('SHA256', $validator));

		$result;

		if (hash_equals($row['hashedValidator'], hash('SHA256', $validator))) {
			$result = $row["userId"];
		} else {
			$result = false;
		}

	} else {
		$result = false;
	}
	return $result;
}

function deleteCookie($conn, $userId) {
	setcookie("remember", null, time()-3600, '/');

	$sql = "DELETE FROM auth_tokens WHERE userId = ?;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		header("location: ../index.php");
		exit();
	}

	mysqli_stmt_bind_param($stmt, "s", $userId);
	mysqli_stmt_execute($stmt);
}
