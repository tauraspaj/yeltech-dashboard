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
<body class="font-display overflow-x-hidden bg-gray-100">

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
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
						</div>
						<input id="searchBarText" type="text" autocomplete="off" placeholder="Search..." class="appearance-none outline-none h-full w-full px-2 md:px-0 flex-auto text-gray-800 bg-transparent">
						<div id="searchResults" class="hidden absolute bottom-100 bg-gray-50 w-full top-16 rounded-b border border-t-0 shadow-md border-gray-300">

						</div>
						<div id="clearSearchBtn" class="relative mr-2 text-gray-400 hover:text-gray-600 cursor-pointer hidden">
							<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
						</div>
					</div>
				</div>

				<div class="flex">
					<!-- Notification icon -->
					<button id="notificationsButton" class="flex-none h-16 w-16 md:w-20 flex items-center justify-center text-gray-700 hover:text-white border-l border-r hover:bg-lightblue-400 hover:border-lightblue-400 focus:outline-none focus:text-white focus:bg-lightblue-400 focus:border-lightblue-400">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
						<?php 
						if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
							$groupFilter = $_SESSION['groupId'];
						} else {
							$groupFilter = 'devices.groupId';
						}
						
						$sql = "
							SELECT devices.deviceId, devices.deviceName, devices.deviceAlias
							FROM devices
							LEFT JOIN alarmTriggers ON devices.deviceId = alarmTriggers.deviceId
							WHERE devices.groupId = $groupFilter AND alarmTriggers.isTriggered = 1;
						";

						$result = mysqli_query($conn, $sql);
						$triggeredDevices = array();
						if ( mysqli_num_rows($result) > 0 ) {
							while ($row = mysqli_fetch_assoc($result)) {
								$triggeredDevices[] = $row;
							}
						}

						$devicesCount = count($triggeredDevices);
						if ($devicesCount == 0) {
							echo '';
							echo '
							<div id="devicesNotification" class="hidden absolute bottom-100 bg-gray-50 w-64 top-16 right-12 md:right-20 rounded-b border border-t-0 shadow-md border-gray-300">
								<div class="flex justify-center items-center border-t text-sm uppercase h-16 font-medium"> 
									You have no alarms!
								</div>
							</div>
							';
						} else {
							echo '<div class="bg-red-500 w-5 h-5 text-xs text-white rounded-lg flex items-center justify-center ml-1">'.$devicesCount.'</div>';

							echo '<div id="devicesNotification" class="hidden absolute bottom-100 bg-gray-50 w-64 top-16 right-12 md:right-20 rounded-b border border-t-0 shadow-md border-gray-300">';
							for ($i = 0; $i < $devicesCount; $i++) {
								$name = '';
								if ($triggeredDevices[$i]['deviceAlias'] != null) {
									$name = $triggeredDevices[$i]['deviceAlias'];
								} else {
									$name = $triggeredDevices[$i]['deviceName'];
								}
								echo '
								<div data-id="'.$triggeredDevices[$i]['deviceId'].'" class="flex justify-start items-center text-lightblue-500 hover:bg-gray-100 hover:text-lightblue-600 cursor-pointer space-x-3 py-4 px-2 border-t"> 
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path></svg>
									<div class="flex flex-col justify-start">
										<p class="uppercase font-medium text-sm text-left">'.$name.'</p>
										<p class="uppercase font-normal text-xs px-2 bg-red-400 text-white rounded-full w-20">Triggered</p>
									</div>
								</div>';
							}
							echo '</div>';
						}
						?>
					</button>

					<!-- Profile -->
					<button class="hidden flex-none h-16 w-12 md:w-20 flex items-center justify-center text-gray-700 hover:text-white border-r hover:bg-lightblue-400 hover:border-lightblue-400 focus:outline-none focus:bg-lightblue-400 focus:border-lightblue-400">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
					</button>

					<!-- Log out icon -->
					<a href="includes/logout.inc.php" class="">
						<button class="flex-none h-16 w-12 md:w-20 flex items-center justify-center text-gray-700 hover:text-white hover:bg-lightblue-400 focus:outline-none focus:bg-lightblue-400">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path></svg>
						</button>
					</a>
				</div>
			</div>