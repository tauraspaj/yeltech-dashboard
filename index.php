<?php
session_start();
$_SESSION['activeUrl'] = 'index.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto bg-gray-100 flex p-4 lg:p-8">
	<!-- Site content -->
	<div class="flex-auto grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-8 auto-rows-min">

		<div class="col-span-1 card-wrapper h-36">
			<div class="card-header">
				<div class="card-header-icon">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
				</div>
				<div class="card-header-title">
					Group
				</div>
			</div>
			<div class="flex-auto flex justify-center items-center bg-gray-50 rounded-b-xl">
				<p id="groupDisplay" class="text-2xl font-medium text-gray-800"><?php echo $_SESSION['groupName']?></p>
			</div>
		</div>

		<div class="col-span-1 card-wrapper h-36">
			<div class="card-header">
				<div class="card-header-icon">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path></svg>
				</div>
				<div class="card-header-title">
					Total devices
				</div>
			</div>
			<div class="flex-auto flex justify-center items-center bg-gray-50 rounded-b-xl">
				<p id="totalDevicesDisplay" class="text-2xl font-medium text-gray-800">
					<?php
						if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
							$devicesGroup = 'devices.groupId';
							$usersGroup = 'users.groupId';
						} else {
							$devicesGroup = $_SESSION['groupId'];
							$usersGroup = $_SESSION['groupId'];
						}
						$sql = "
							SELECT COUNT(*) as totalDevices FROM devices WHERE devices.groupId = $devicesGroup
						";
						$result = mysqli_query($conn, $sql);
						if ( mysqli_num_rows($result) > 0 ) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo $row['totalDevices'];
							}
						}
					?>
				</p>
			</div>
		</div>

		<div class="col-span-1 card-wrapper h-36">
			<div class="card-header">
				<div class="card-header-icon">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>
				</div>
				<div class="card-header-title">
					Total users
				</div>
			</div>
			<div class="flex-auto flex justify-center items-center bg-gray-50 rounded-b-xl">
				<p id="totalUsersDisplay" class="text-2xl font-medium text-gray-800">
					<?php
					$sql2 = "
					SELECT COUNT(*) as totalUsers FROM users WHERE users.groupId = $usersGroup
					";
					$result2 = mysqli_query($conn, $sql2);
					if ( mysqli_num_rows($result2) > 0 ) {
						while ($row = mysqli_fetch_assoc($result2)) {
							echo $row['totalUsers'];
						}
					}
					?>
				</p>
			</div>
		</div>

		<?php
		$sql2 = "
		SELECT smsLeft FROM `groups` WHERE groupId = {$_SESSION['groupId']}
		";
		$result2 = mysqli_query($conn, $sql2);
		if ( mysqli_num_rows($result2) > 0 ) {
			while ($row = mysqli_fetch_assoc($result2)) {
				$smsLeft = $row['smsLeft'];
			}
		}

		$borderHighlight = '';
		if ($smsLeft < 50) {
			$borderHighlight = 'border-2 border-red-500';
		} else if ($smsLeft < 100) {
			$borderHighlight = 'border border-yellow-500';
		}
		?>
		<div class="col-span-1 card-wrapper h-36 <?php echo $borderHighlight;?>">
			<div class="card-header">
				<div class="card-header-icon">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
				</div>
				<div class="card-header-title">
					SMS Credits
				</div>
			</div>
			<div class="flex-auto flex flex-col justify-center items-center bg-gray-50 rounded-b-xl">
				<p id="totalAlarmsDisplay" class="text-2xl font-medium text-gray-800">
					<?php echo $smsLeft; ?>
				</p>
				<p class="text-center text-xs italic mt-1">To top up, get in touch <br> <span class="font-medium">info@yeltech.com</span></p>
			</div>
		</div>

		<div class="col-span-2 card-wrapper">
			<div class="card-header text-red-900">
				<div class="card-header-icon bg-red-100 text-red-500">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
				</div>
				<div class="card-header-title bg-red-100 text-red-900">
					Alarmed devices
				</div>
			</div>
			<div id="alarmedList" class="flex-auto flex flex-col p-4 space-y-2 overflow-y-auto bg-gray-50 rounded-b-xl" style="max-height: 24rem;">
				
			</div>
		</div>

		<div class="col-span-2 card-wrapper">
			<div class="card-header">
				<div class="card-header-icon">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"></path></svg>
				</div>
				<div class="card-header-title">
					Devices map
				</div>
			</div>
			<div class="flex-auto flex justify-center items-center h-96 p-2 bg-gray-50 rounded-b-xl">
				<div id="homeMap" class="w-full h-full rounded-xl"></div>
			</div>
		</div>

	</div>
	<!-- End of site content -->
</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxIndex.js"></script>

<?php 
include_once('./footer.php');
?>