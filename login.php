<?php
session_start();

require_once 'includes/dbh.inc.php';

if (isset($_COOKIE["remember"])) {
	require_once 'includes/cookieFunctions.inc.php';
	$check = verifyRememberCookie($conn, $_COOKIE["remember"]);
	if ($check !== false) {
		// If the answer is not false, then the database record is returned
		$_SESSION["userId"] = $check;
	} else {
		// In case the check fails...
	}
} 

if (isset($_SESSION["userId"])) {
	header("location: index.php");
	exit();
}


?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="css/dist.css">
	<link rel="stylesheet" href="css/styles.css">
	<script src="https://kit.fontawesome.com/3ade8de4ba.js" crossorigin="anonymous"></script>

	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<title>YelDash - Manage Your Devices</title>

</head>

<body class="font-display">
	<!-- Set up background -->
	<div class="bg">
		<div class="h-screen w-screen absolute bg-gradient-to-r from-white to-bluegray-100 -z-m1"></div>
		<div class="shape bg-gradient-to-l from-white to-bluegray-100"></div>
	</div>
	<div class="flex items-center justify-center min-h-screen">
		<!-- Content wrapper -->
		<div class="max-w-sm w-full space-y-12 px-6 md:px-0">
			<!-- Logo -->
			<div>
				<img src="img/yeltech-dark.png" class="h-18 md:h-24 w-auto mx-auto" alt="Yeltech" title="Yeltech">
			</div>

			<!-- Form -->
			<form method="post" class="space-y-4">
				<!-- Email -->
				<div class="flex items-center h-12 rounded bg-white border ease-in duration-200">
					<div class="h-12 w-12 flex items-center justify-center flex-none text-gray-600 ease-in-out transform duration-200">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
					</div>
					<input type="email" id="login_email" name="login_email" placeholder="Email Address" required spellcheck="false" autocomplete="on" class="appearance-none text-md w-full py-2 pr-2 bg-transparent placeholder-gray-300 text-gray-900 focus:outline-none">
				</div>

				<!-- Password -->
				<div class="flex items-center h-12 rounded bg-white border ease-in duration-200">
					<div class="h-12 w-12 flex items-center justify-center flex-none text-gray-600 ease-in-out transform duration-200">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
					</div>
					<input type="password" id="login_pwd" name="login_pwd" placeholder="Password" required class="appearance-none text-md w-full py-2 pr-2 bg-transparent placeholder-gray-300 text-gray-900 focus:outline-none">
				</div>

				<!-- Remember me -->
				<div class="flex items-center justify-between">
					<div class="flex items-center">
						<input id="remember_me" type="checkbox" class="h-4 w-4 border-gray-300 cursor-pointer">
						<label for="remember_me" class="text-md text-gray-700 tracking-wide cursor-pointer pl-2 select-none">Remember me</label>
					</div>
					<div>
						<p class="text-sm italic text-gray-400 underline cursor-pointer">Forgot password?</p>
					</div>
				</div>

				<!-- Sign in button -->
				<div class="flex items-center justify-between h-12 rounded">
					<button type="submit" id="login_submit" title="Sign in" class="appearance-none focus:outline-none h-full flex-1 flex items-center justify-center rounded-md text-white transition duration-200 ease-in bg-lightblue-500 hover:bg-lightblue-600">
						Sign in
						<svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
						</button>
				</div>
			</form>
			<!-- End of form -->

			<!-- Social media section -->
			<div class="space-y-4">
				<!-- Socials title -->
				<div class="flex justify-center items-center space-x-2">
					<div class="border-b w-4 border-gray-300"></div>
					<h2 class="text-gray-400 text-xl tracking-wide">Our Socials</h2>
					<div class="border-b w-4 border-gray-300"></div>
				</div>

				<!--Social media icons  -->
				<div class="flex justify-center items-center space-x-4">
					<a href="https://www.linkedin.com/company/yeltech-limited/" class="inline-block" title="LinkedIn"><i
							class="fab fa-linkedin-in text-gray-400 border-gray-200 border-2 rounded p-4 text-lg transition duration-300 ease-in-out hover:text-lightblue-600 hover:border-lightblue-600"></i></a>
					<a href="https://www.facebook.com/YeltechRailwaySolutions/" class="inline-block" title="Facebook"><i
							class="fab fa-facebook-f text-gray-400 border-gray-200 border-2 rounded p-4 text-lg transition duration-300 ease-in-out hover:text-lightblue-600 hover:border-lightblue-600"></i></a>
					<a href="https://twitter.com/yeltechltd?lang=en" class="inline-block" title="Twitter"><i
							class="fab fa-twitter text-gray-400 border-gray-200 border-2 rounded p-4 text-lg transition duration-300 ease-in-out hover:text-lightblue-600 hover:border-lightblue-600"></i></a>
					<a href="https://www.youtube.com/channel/UCRTmvfu0R_qiRwGIHNEMknQ" class="inline-block"
						title="YouTube"><i
							class="fab fa-youtube text-gray-400 border-gray-200 border-2 rounded p-4 text-lg transition duration-300 ease-in-out hover:text-lightblue-600 hover:border-lightblue-600"></i></a>
				</div>
			</div>
			<!-- End of social media section -->
		</div>

		<!-- End of content wrapper -->
	</div>

	<!-- JS Scripts -->
	<script type="text/javascript" src="js/login.js"></script>
</body>

</html>