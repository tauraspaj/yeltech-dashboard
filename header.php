<?php
require_once 'includes/dbh.inc.php';
require_once 'includes/cookieFunctions.inc.php';

// Check for cookies
if (isset($_COOKIE["remember"])) {
	$check = verifyRememberCookie($conn, $_COOKIE["remember"]);
	if ($check !== false) {
		// If the answer is not false, then userId from database is returned
		$_SESSION["userId"] = $check;
	} else {
		// In case the check fails...
	}
} 

// If not logged in, redirect
if (!isset($_SESSION["userId"])) {
	header("location: login.php");
	exit();
}

$pages = array(
	array(	'title'	=> 'Home', 
			'url'	=> 'index.php', 
			'icon'	=> '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>'),

	array(	'title'	=> 'Users', 
			'url'	=> 'users.php', 
			'icon'	=> '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>'),

	array(	'title'	=> 'Devices', 
			'url'	=> 'devices.php', 
			'icon'	=> '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path></svg>')
);

$adminPages = array(
	array(	'title'	=> 'Edit groups',
			'url' 	=> 'managegroups.php',
			'icon'	=> '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>'),

	array(	'title'	=> 'New device',
			'url' 	=> 'newdevice.php',
			'icon' 	=> '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path></svg>')
			
);

$activeUrl = $_SESSION['activeUrl'];
unset($_SESSION['activeUrl']);

// Set up user SESSION variables
$sessionId = $_SESSION["userId"];
$sql = "
SELECT users.fullName, users.groupId, `groups`.groupName as groupName, users.email, users.phoneNumber, users.roleId, roles.roleName as role, users.sendingId, sendingType.sendingType as sendingType
FROM users
LEFT JOIN `groups` ON users.groupId = `groups`.groupId
LEFT JOIN sendingType ON users.sendingId = sendingType.sendingId
LEFT JOIN roles ON users.roleId = roles.roleId
WHERE users.userId = $sessionId
";

$result = mysqli_query($conn, $sql);
$resultCheck = mysqli_num_rows($result);

if ($resultCheck > 0) {
	while ($row = mysqli_fetch_assoc($result)) {
		// print_r($row);
		$_SESSION['fullName'] = $row['fullName'];

		$_SESSION['email'] = $row['email'];
		$_SESSION['phoneNumber'] = $row['phoneNumber'];

		$_SESSION['groupName'] = $row['groupName'];
		$_SESSION['groupId'] = $row['groupId'];

		$_SESSION['roleId'] = $row['roleId'];
		$_SESSION['role'] = $row['role'];

		$_SESSION['sendingId'] = $row['sendingId'];
		$_SESSION['sendingType'] = $row['sendingType'];
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Chart js -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
	
	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<!-- Mapbox API -->
	<script src='https://api.mapbox.com/mapbox-gl-js/v2.1.1/mapbox-gl.js'></script>
	<link href='https://api.mapbox.com/mapbox-gl-js/v2.1.1/mapbox-gl.css' rel='stylesheet' />

	<link rel="stylesheet" href="css/dist.css">
	<link rel="stylesheet" href="css/styles.css">

	<title>YelDash - Manage Your Devices</title>

</head>
<body class="font-display overflow-x-hidden">

	<div class="flex bg-gray-50">
        <!-- Left sidebars-->
		<!-- Desktop version sidebar -->
        <div id="leftSidebar_desktop" class="hidden lg:block sticky top-0 flex-none h-screen w-28 bg-gray-50 transition-all transform lg:-translate-x-0 lg:-mr-0 z-30">
			<!-- Left sidebar wrapper -->
			<div class="w-full h-full flex flex-col">
				<!-- Logo -->
				<div class="flex-none h-16 bg-gray-50 flex justify-center items-center">
						<a href="index.php" title="Yeltech">
							<img src="img/logo.png" alt="">
						</a>
				</div>

				<!-- Nav -->
				<div class="flex-auto flex flex-col justify-between shadow-md py-4 border-t">
					<!-- Pages accessible by all -->
					<div class="flex flex-col">
						<?php 
						forEach ($pages as $page) {
							echo '
							<a href="'.$page['url'].'" class="flex flex-col justify-center items-center my-4 space-y-2 '.(($page['url'] == $activeUrl)?'text-lightblue-500':'text-gray-400 hover:text-gray-800').'" title="'.$page['title'].'">
								'.$page['icon'].'
								<p class="text-sm">'.$page['title'].'</p>
							</a>
							';
						}
						?>
					</div>

					<!-- Admin pages -->
					<div class="flex flex-col">
						<?php 
						if ($_SESSION['roleId'] == 3 || $_SESSION['roleId'] == 4) {
							echo '';
						} elseif ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
							forEach ($adminPages as $adminPage) {
								echo '
								<a href="'.$adminPage['url'].'" class="flex flex-col justify-center items-center my-4 space-y-2 '.(($adminPage['url'] == $activeUrl)?'text-red-600':'text-gray-400 hover:text-gray-800').'" title="'.$adminPage['title'].'">
									'.$adminPage['icon'].'
									<p class="text-sm">'.$adminPage['title'].'</p>
								</a>
								';
							}
						}
						?>
					</div>
				</div>
			</div>
			<!-- End of wrapper -->
		</div>
		<!-- End of desktop version sidebar -->
		
		<!-- Mobile version sidebar -->
        <div id="leftSidebar_mobile" class="block lg:hidden fixed top-0 z-50 flex-none h-screen w-full border-r shadow ease-in-out transform duration-200 -translate-x-full">
			<div id="leftSidebarFade" class="bg-black bg-opacity-25 h-screen w-screen absolute top-0">
				<div class="absolute top-3 right-3 border-2 border-gray-100 text-white p-2 bg-gray-700">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
				</div>
			</div>
			
			<div id="leftSidebar_mobileInner" class="bg-white h-full w-36 ease-in-out transform duration-500 -translate-x-full z-50">
				<!-- Left sidebar wrapper INSIDE MOBILE NAV-->
			<div class="w-full h-full flex flex-col">
				<!-- Logo -->
				<div class="flex-none h-16 bg-gray-50 flex justify-center items-center">
						<a href="index.php" title="Yeltech">
							<img src="img/logo.png" alt="">
						</a>
				</div>

				<!-- Nav -->
				<div class="flex-auto flex flex-col justify-between shadow-md py-4 border-t">
					<!-- Pages accessible by all -->
					<div class="flex flex-col">
						<?php 
						forEach ($pages as $page) {
							echo '
							<a href="'.$page['url'].'" class="flex flex-col justify-center items-center my-4 space-y-2 '.(($page['url'] == $activeUrl)?'text-lightblue-500':'text-gray-400 hover:text-gray-800').'" title="'.$page['title'].'">
								'.$page['icon'].'
								<p class="text-sm">'.$page['title'].'</p>
							</a>
							';
						}
						?>
					</div>

					<!-- Admin pages -->
					<div class="flex flex-col">
						<?php 
						if ($_SESSION['roleId'] == 3 || $_SESSION['roleId'] == 4) {
							echo '';
						} elseif ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
							forEach ($adminPages as $adminPage) {
								echo '
								<a href="'.$adminPage['url'].'" class="flex flex-col justify-center items-center my-4 space-y-2 '.(($adminPage['url'] == $activeUrl)?'text-red-600':'text-gray-400 hover:text-gray-800').'" title="'.$adminPage['title'].'">
									'.$adminPage['icon'].'
									<p class="text-sm">'.$adminPage['title'].'</p>
								</a>
								';
							}
						}
						?>
					</div>
				</div>
			</div>
			<!-- End of wrapper -->
				
			</div>
		
		</div>
		<!-- End of mobile version sidebar -->
        <!-- End of left sidebars-->

        <!-- Right -->
        <div class="flex-auto flex flex-col">
            <!-- Right top bar -->
            <div class="sticky top-0 flex-none flex justify-between h-16 bg-gray-50 shadow-md z-20">
				<div class="flex flex-auto max-w-xl">
					<!-- Burger icon for desktop -->
					<button id="burger_desktop" class="hidden lg:flex flex-none h-16 w-16 items-center justify-center border-r border-l hover:bg-lightblue-400 group hover:border-lightblue-400 focus:outline-none">
						<svg class="w-5 h-5 text-gray-700 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
					</button>
					
					<!-- Burger icon for mobile -->
					<button id="burger_mobile" class="flex lg:hidden flex-none h-16 w-16 items-center justify-center border-r border-l hover:bg-lightblue-400 group hover:border-lightblue-400 focus:outline-none">
						<svg class="w-5 h-5 text-gray-700 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
						<svg class="w-3 h-3 text-lightblue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
					</button>
					
					<!-- Search bar -->
					<div class="flex-auto flex items-center relative">
						<div class="flex-none hidden md:flex justify-center text-gray-800 transition-all duration-200 px-4">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
						</div>
						<input type="text" autocomplete="off" placeholder="Search..." class="appearance-none outline-none h-full w-full px-2 md:px-0 flex-auto text-gray-800 bg-transparent">
						<div id="searchResults" class="hidden absolute bottom-100 h-32 bg-red-200 w-full top-16">

						</div>
					</div>
				</div>

				<div class="flex">
					<!-- Notification icon -->
					<button class="flex-none h-16 w-16 md:w-20 flex items-center justify-center border-l hover:bg-lightblue-400 group hover:border-lightblue-400 focus:outline-none focus:bg-lightblue-400 focus:border-lightblue-400">
						<svg class="w-5 h-5 text-gray-700 group-hover:text-white group-focus:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
						<div class="bg-red-500 w-5 h-5 text-xs text-white rounded-lg flex items-center justify-center ml-1">5</div>
					</button>

					<!-- Profile -->
					<button class="flex-none h-16 w-12 md:w-20 flex items-center justify-center border-l border-r hover:bg-lightblue-400 group hover:border-lightblue-400 focus:outline-none focus:bg-lightblue-400 focus:border-lightblue-400">
						<svg class="w-5 h-5 text-gray-700 group-hover:text-white group-focus:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
					</button>
					<!-- Log out icon -->

					<a href="includes/logout.inc.php" class="">
						<button class="flex-none h-16 w-12 md:w-20 flex items-center justify-center hover:bg-lightblue-400 group focus:outline-none focus:bg-lightblue-400">
							<svg class="w-5 h-5 text-gray-700 group-hover:text-white group-focus:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
						</button>
					</a>
				</div>
			</div>