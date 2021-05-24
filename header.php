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
	array(	'title'	=> 'Messages',
			'url' 	=> 'messagehistory.php',
			'icon'	=> '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd"></path></svg>'),

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

	<link rel="shortcut icon" type="image/jpg" href="img/favicon.png"/>
	<title>YelCloud - Manage Your Devices</title>

</head>
<body class="font-display overflow-x-hidden bg-gray-100">
	<!-- Edit profile modal -->
	<div id="editprofile-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
		<div id="modal-box" class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
			<!-- Title/close btn -->
			<div class="flex justify-between items-center border-b pb-1 border-gray-300">
				<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">User profile</p>
				<svg id="close-editprofile-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
			</div>

			<!-- Content -->
			<div id="editprofile-content" class="w-full flex flex-col pt-4">
				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Full name</p>
						<input id="editprofile-fullName" type="text" value="<?php echo $_SESSION['fullName']; ?>" class="border border-gray-300">
					</div>
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Receive notifications by</p>
						<select id="editprofile-sendingId" class="border border-gray-300">
							<?php
								$sql = "SELECT sendingId, sendingType FROM sendingType ORDER BY sendingId ASC";
								$result = mysqli_query($conn, $sql);
								if (mysqli_num_rows($result) > 0) {
									while ($row = mysqli_fetch_assoc($result)) {
										if ($row['sendingId'] == $_SESSION['sendingId']) {
											echo '<option data-id="'.$row['sendingId'].'" selected>'.$row['sendingType'].'</option>';
										} else {
											echo '<option data-id="'.$row['sendingId'].'">'.$row['sendingType'].'</option>';
										}
									}
								}  
							?>
						</select>
					</div>
				</div>
				<!-- End of row -->

				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Email</p>
						<input id="editprofile-email" type="email" value="<?php echo $_SESSION['email']; ?>" class="border border-gray-300">
					</div>
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Phone Number</p>
						<input id="editprofile-phoneNumber" type="text" value="<?php echo $_SESSION['phoneNumber']; ?>" class="border border-gray-300">
						<p class="text-xs italic text-gray-500 ml-2 mt-1">Must start with + or be left empty</p>
					</div>
				</div>
				<!-- End of row -->

				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Group</p>
						<select class="border border-gray-300" disabled>
							<option><?php echo $_SESSION['groupName']; ?></option>
						</select>
					</div>
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Role</p>
						<select class="border border-gray-300" disabled>
							<option><?php echo $_SESSION['role']; ?></option>
						</select>
					</div>
				</div>
				<!-- End of row -->
				
				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">New password</p>
						<input id="editprofile-newpassword" type="password" class="border border-gray-300">
						<p class="text-xs italic text-gray-500 ml-2 mt-1">Leave this empty if you do not wish to change your password</p>
					</div>
				</div>
				<!-- End of row -->
				
				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto border-t border-gray-300 mt-4">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Password</p>
						<input id="editprofile-password" type="password" class="border border-gray-300">
						<p class="text-xs italic text-gray-500 ml-2 mt-1">Enter your password to save changes</p>
					</div>
				</div>
				<!-- End of row -->

			</div>

			<!-- Buttons -->
			<div id="editprofile-buttons" class="flex justify-end items-center mt-4 space-x-4">
				<button id="editprofile-save" data-id="<?php echo $_SESSION['userId']; ?>" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-green-500 hover:bg-green-600 transition-all focus:bg-green-600 focus:text-white">Save</button>
				
				<button id="editprofile-cancel" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-red-500 hover:bg-red-600 transition-all focus:bg-red-600 focus:text-white">Cancel</button>
			</div>
		</div>
	</div>

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
						if ($_SESSION['groupName'] == 'ELECTROMECH' && $_SESSION['email'] == 'electromech@yeltech.com') {
							forEach ($adminPages as $adminPage) {
								if ($adminPage['title'] == 'Messages') {
									echo '
									<a href="'.$adminPage['url'].'" class="flex flex-col justify-center items-center my-4 space-y-2 '.(($adminPage['url'] == $activeUrl)?'text-red-600':'text-gray-400 hover:text-gray-800').'" title="'.$adminPage['title'].'">
										'.$adminPage['icon'].'
										<p class="text-sm">'.$adminPage['title'].'</p>
									</a>
									';
								}
							}
						}
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
						
						if ($_SESSION['groupName'] == 'ELECTROMECH' && $_SESSION['email'] == 'electromech@yeltech.com') {
							forEach ($adminPages as $adminPage) {
								if ($adminPage['title'] == 'Messages') {
									echo '
									<a href="'.$adminPage['url'].'" class="flex flex-col justify-center items-center my-4 space-y-2 '.(($adminPage['url'] == $activeUrl)?'text-red-600':'text-gray-400 hover:text-gray-800').'" title="'.$adminPage['title'].'">
										'.$adminPage['icon'].'
										<p class="text-sm">'.$adminPage['title'].'</p>
									</a>
									';
								}
							}
						}

						if ($_SESSION['roleId'] == 3 || $_SESSION['roleId'] == 4) {
							echo '';
						} elseif ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
							// admin pages generated here
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
						<input id="searchBarText" type="text" autocomplete="off" placeholder="Search..." class="h-full w-full border-none bg-transparent font-normal focus:ring-0 px-2 md:px-0 text-base">
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
							SELECT DISTINCT alarmTriggers.deviceId, devices.groupId, devices.deviceName, devices.deviceAlias
							FROM alarmTriggers
							LEFT JOIN devices ON alarmTriggers.deviceId = devices.deviceId
							WHERE alarmTriggers.isTriggered = 1 AND devices.groupId = $groupFilter
						";

						$result = mysqli_query($conn, $sql);
						$triggeredDevices = array();
						if ( mysqli_num_rows($result) > 0 ) {
							while ($row = mysqli_fetch_assoc($result)) {

								// For each device, check how many active alarms it has
								$sql2 = "
								SELECT COUNT(triggerId) AS nAlarmsTriggered FROM alarmTriggers WHERE isTriggered = 1 AND deviceId = {$row['deviceId']};
								";
								$result2 = mysqli_query($conn, $sql2);
								if ( mysqli_num_rows($result2) > 0 ) {
									while ($row2 = mysqli_fetch_assoc($result2)) {
										$row['nAlarmsTriggered'] = $row2['nAlarmsTriggered'];
									}
								}

								$triggeredDevices[] = $row;
							}
						}
						
						$devicesCount = count($triggeredDevices);
						if ($devicesCount == 0) {
							echo '
							<div id="devicesNotification" class="hidden absolute bottom-100 bg-gray-50 w-64 top-16 right-12 md:right-20 rounded-b border border-t-0 shadow-md border-gray-300">
								<p class="flex justify-center items-center border-t text-sm uppercase h-16 font-medium text-black cursor-default"> 
									You have no alarms!
								</p>
							</div>
							';
							
						} else {
							echo '<div class="bg-red-500 w-5 h-5 text-xs text-white rounded-lg flex items-center justify-center ml-1">'.$devicesCount.'</div>';

							echo '<div id="devicesNotification" class="hidden absolute bottom-100 bg-gray-50 w-64 top-16 right-12 md:right-20 rounded-b border border-t-0 shadow-md border-gray-300 overflow-y-auto" style="max-height: 20rem;">';
							for ($i = 0; $i < $devicesCount; $i++) {
								$name = '';
								if ($triggeredDevices[$i]['deviceAlias'] == null || $triggeredDevices[$i]['deviceAlias'] == '') {
									$name = $triggeredDevices[$i]['deviceName'];
								} else {
									$name = $triggeredDevices[$i]['deviceAlias'];
								}
								echo '
								<div data-id="'.$triggeredDevices[$i]['deviceId'].'" class="flex justify-between items-center text-lightblue-500 hover:bg-gray-100 hover:text-lightblue-600 cursor-pointer space-x-3 py-4 px-2 border-t">
									<div class="flex items-center space-x-2">
										<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path></svg>
										<p class="uppercase font-medium text-sm text-left truncate w-24">'.$name.'</p>
									</div>
									<div>
										<p class="uppercase font-medium text-xs py-1 px-3 mr-2 bg-red-500 text-white rounded-full whitespace-nowrap">'.$triggeredDevices[$i]['nAlarmsTriggered'].' Alarms</p>
									</div>
								</div>';								
							}
							echo '</div>';
						}
						?>
					</button>

					<!-- Profile -->
					<button id="openProfile" class="flex-none h-16 w-16 sm:w-auto flex items-center justify-center text-gray-700 hover:text-white border-r hover:bg-lightblue-400 hover:border-lightblue-400 focus:outline-none px-4">
						<svg class="flex-none w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
						<p class="hidden sm:block whitespace-nowrap ml-2 text-sm"><?php echo explode(" ", $_SESSION['fullName'])[0];?></p>
					</button>

					<!-- Log out icon -->
					<a href="includes/logout.inc.php" class="">
						<button class="flex-none h-16 w-12 md:w-20 flex items-center justify-center text-gray-700 hover:text-white hover:bg-lightblue-400 focus:outline-none focus:bg-lightblue-400">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path></svg>
						</button>
					</a>
				</div>
			</div>