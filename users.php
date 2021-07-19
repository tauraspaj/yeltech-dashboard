<?php
session_start();
$_SESSION['activeUrl'] = 'users.php';
include_once('header.php');
?>

<!-- New user modal -->
<div id="newuser-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
	<div id="modal-box" class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
		<!-- Title/close btn -->
		<div class="flex justify-between items-center border-b pb-1 border-gray-300">
			<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">New user</p>
			<svg id="close-newuser-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
		</div>
		<div class="w-full">
			<p id="errorResponse" class="hidden text-sm italic text-red-500 text-center mt-2"></p>
			<p id="okResponse" class="hidden text-sm italic text-green-500 text-center mt-2">User successfully registered!</p>
			<form id="newUserForm" method="post" autocomplete="off" class="flex flex-col">
				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Full name<span class="text-red-500">*</span></p>
						<input id="new_fullName" required type="text" class="border border-gray-300">
					</div>

					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Role<span class="text-red-500">*</span></p>
						<select id="new_roleId" class="border border-gray-300">
							<?php
								$sql = "SELECT roleId, roleName FROM roles WHERE roleId >= ".$_SESSION['roleId']." ORDER BY roleId DESC;";
								$result = mysqli_query($conn, $sql);
								if (mysqli_num_rows($result) > 0) {
									while ($row = mysqli_fetch_assoc($result)) {
										echo '<option data-id="'.$row['roleId'].'">'.$row['roleName'].'</option>';
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
						<p class="form-field-title">Email<span class="text-red-500">*</span></p>
						<input id="new_email" type="email" required class="border border-gray-300">
					</div>
				</div>
				<!-- End of row -->

				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Group<span class="text-red-500">*</span></p>
						<select id="new_groupId" class="border border-gray-300">
							<?php
								if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
									$groupFilter = '`groups`.groupId';
								} else {
									$groupFilter = $_SESSION['groupId'];
								}

								$sql = "SELECT groupId, groupName FROM `groups` WHERE groupId = $groupFilter ORDER BY groupName ASC;";
								$result = mysqli_query($conn, $sql);
								if (mysqli_num_rows($result) > 0) {
									while ($row = mysqli_fetch_assoc($result)) {
										echo '<option data-id="'.$row['groupId'].'">'.$row['groupName'].'</option>';
									}
								}  
							?>
						</select>
					</div>

					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Phone number</p>
						<input id="new_phone" type="text" class="border border-gray-300">
						<p class="text-xs italic text-gray-500 ml-2 mt-1">Must start with + or be left empty</p>
					</div>
				</div>
				<!-- End of row -->

				<!-- Row -->
				<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Password<span class="text-red-500">*</span></p>
						<input id="new_password" type="password" required class="border border-gray-300">
						<p class="text-xs italic text-gray-500 ml-2 mt-1">Must be longer than 6 characters</p>
					</div>

					<div class="flex flex-col flex-1 mt-2">
						<p class="form-field-title">Confirm Password<span class="text-red-500">*</span></p>
						<input id="new_confpassword" type="password" required class="border border-gray-300">
					</div>
				</div>
				<!-- End of row -->

				<div class="flex justify-end items-center mt-4 md:mt-0 space-x-4">
					<button id="cancelBtn" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Cancel</button>
					<button type="submit" class="px-4">Create</button>
				</div>

			</form>
		</div>
	</div>
</div>

<!-- View user modal -->
<div id="viewuser-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
	<div id="modal-box" class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
		<!-- Title/close btn -->
		<div class="flex justify-between items-center border-b pb-1 border-gray-300">
			<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">User profile</p>
			<svg id="close-viewuser-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
		</div>

		<!-- Content -->
		<div id="userprofile-content" class="w-full flex flex-col divide-y divide-gray-300">
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Name</div>
				<div id="profile-fullName" class="flex-1"></div>
			</div>
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Group</div>
				<div id="profile-groupName" class="flex-1">
						<?php 
						if ($_SESSION['roleId'] == 1) {
							$sql = "
							SELECT groupId, groupName FROM `groups` ORDER BY groupName ASC;
							";
							$result = mysqli_query($conn, $sql);
							echo '<select id="profile-groupSelect" class="flex-1 h-9 border border-gray-300">';
							if ( mysqli_num_rows($result) > 0 ) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="'.$row['groupId'].'">'.$row['groupName'].'</option>';
								}
							}
							echo '</select>';
						}
						?>
				</div>
			</div>
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Role</div>
				<div id="profile-roleName" class="flex-1">
					<?php 
					if ($_SESSION['roleId'] == 1) {
						$sql = "
						SELECT roleId, roleName FROM roles ORDER BY roleId ASC;
						";
						$result = mysqli_query($conn, $sql);
						echo '<select id="profile-roleSelect" class="flex-1 h-9 border border-gray-300">';
						if ( mysqli_num_rows($result) > 0 ) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<option value="'.$row['roleId'].'">'.$row['roleName'].'</option>';
							}
						}
						echo '</select>';
					}
					?>
				</div>
			</div>
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Email</div>
				<div id="profile-email" class="flex-1"></div>
			</div>
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Phone</div>
				<div id="profile-phone" class="flex-1"></div>
			</div>
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Sending Type</div>
				<div id="profile-sendingType" class="flex-1">
					<?php 
					if ($_SESSION['roleId'] == 1) {
						$sql = "
						SELECT sendingId, sendingType FROM sendingType ORDER BY sendingId ASC;
						";
						$result = mysqli_query($conn, $sql);
						echo '<select id="profile-sendingSelect" class="flex-1 h-9 border border-gray-300">';
						if ( mysqli_num_rows($result) > 0 ) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<option value="'.$row['sendingId'].'">'.$row['sendingType'].'</option>';
							}
						}
						echo '</select>';
					}
					?>
				</div>
			</div>
			<div class="flex h-12 items-center">
				<div class="flex-1 text-center text-sm">Created At</div>
				<div id="profile-createdAt" class="flex-1"></div>
			</div>
		</div>

		<!-- Buttons -->
		<div id="viewuser-buttons" class="flex justify-end items-center mt-4 space-x-4">
			<?php
				// Super Admins and Yeltech Admins can reset password
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					echo '<button id="resetPassword" class="h-10 px-1 border-b-2 italic border-transparent hover:border-lightblue-500 hover:text-black text-gray-500 transition-all">Reset Password</button>';
				}

				// Only admins can access delete button
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2 || $_SESSION['roleId'] == 3) {
					echo '<button id="deleteUser" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Delete</button>';
				}

				// Only super admins can edit info
				if ($_SESSION['roleId'] == 1) {
					echo '<button id="saveUser" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-green-500 hover:bg-green-600 transition-all focus:bg-green-600 focus:text-white">Save</button>';
				}
			?>
			
			<button id="closeBtn" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-lightblue-500 hover:bg-lightblue-600 transition-all focus:bg-lightblue-600 focus:text-white">Close</button>
		</div>
	</div>
</div>

<!-- Bottom right dashboard window -->
<div class="flex-auto flex-col lg:flex-row bg-gray-100 flex">
	<!-- Filters subnav -->
	<div class="hidden lg:block flex-none lg:h-full bg-transparent lg:w-44 xl:w-60">
		<div class="fixed h-full flex flex-col bg-transparent" style="width: inherit;">
			<!-- New user btn -->
			<?php 
			if ($_SESSION['roleId'] != 4) {
				echo '<div class="bg-gray-100 pb-8 shadow-md rounded-br-3xl">
					<div href="./newuser.php" class="h-10 w-40 xl:w-52 focus:outline-none bg-lightblue-400 rounded-lg shadow text-white font-medium flex items-center text-sm mt-6 mx-auto transition-all hover:bg-lightblue-500 hover:ring-1 cursor-pointer" title="Create new user">
						<div class="w-10 h-full rounded-l-lg flex justify-center items-center bg-lightblue-600">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path></svg>
						</div>
						<div id="open-newuser-modal" class="flex-auto h-full flex justify-center items-center">
							<p>New User</p>
						</div>
					</div>
				</div>';
			}
			?>
					
			<!-- Filters div -->
			<div class="flex flex-col space-y-8 h-full pt-8 bg-gray-200 rounded-tr-3xl shadow-md">
				<input id="userSearch" type="text" class="h-10 w-40 xl:w-52 outline-none focus:outline-none bg-gray-100 rounded-lg text-gray-800 font-medium flex justify-center items-center text-sm space-x-1 mx-auto px-4 border border-gray-300 transition-all" placeholder="Filter users...">

				<!-- Php code for groups filter -->
				<?php
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					echo '
					<!-- Single filter -->
					<div class="flex flex-col px-4">
						<!-- Title -->
						<div id="groupsTitle" class="flex items-center cursor-pointer space-x-2">
							<div id="icons">
								<svg id="icon_plus" class="w-6 h-6 text-gray-800 transform duration-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
								<svg id="icon_minus" class="w-6 h-6 text-gray-800 transform duration-200 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
							</div>
							<p class="text-xs font-semibold uppercase text-gray-600">Group</p>
						</div>
						<!-- Separator -->
						<div class="border border-gray-300 mt-1"></div>

						<!-- Filter content -->
						<div class="mt-2 hidden">
							<select id="groupFilter" class="focus:outline-none w-full h-10 bg-gray-50 border border-gray-400 px-2 text-sm">
								<option data-id="users.groupId" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected>All Groups</option>
					';

					$sql = "SELECT groupId, groupName FROM `groups` ORDER BY groupName ASC";
					$groupResults = mysqli_query($conn, $sql);
					$resultCheck = mysqli_num_rows($groupResults);
					if ($resultCheck > 0) {
						while ($row = mysqli_fetch_assoc($groupResults)) {
							echo '<option data-id="'.$row['groupId'].'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">'.$row['groupName'].'</option> ';
						}
					}

					echo "
							</select>
						</div>
						<!-- End of filter content -->
					</div>
					<!-- End of single filter -->
					";
				}
				?>

				<!-- Single filter -->
				<div id="rolesFilter" class="flex flex-col px-4">
					<!-- Title -->
					<div id="rolesTitle" class="flex items-center cursor-pointer space-x-2">
						<div id="icons">
							<svg id="icon_plus" class="w-6 h-6 text-gray-800 transform duration-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
							<svg id="icon_minus" class="w-6 h-6 text-gray-800 transform duration-200 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
						</div>
						<p class="text-xs font-semibold uppercase text-gray-600">Roles</p>
					</div>
					<!-- Separator -->
					<div class="border border-gray-300 mt-1"></div>
					
					<!-- Filter content -->
					<div class="mt-2 flex flex-col space-y-1 hidden">
						<!-- Normal users can't filter super admins -->
						<?php
						$sql = "SELECT roleId, roleName FROM roles ORDER BY roleId ASC";
						$roleResults = mysqli_query($conn, $sql);
						$resultCheck = mysqli_num_rows($roleResults);
						if ($resultCheck > 0) {
							while ($row = mysqli_fetch_assoc($roleResults)) {
								if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
									echo '<label class="flex items-center space-x-1">
											<input data-id="'.$row['roleId'].'" type="checkbox" class="h-4 w-4" checked><span class="text-sm text-gray-600 font-medium">'.$row['roleName'].'</span>
										</label>';
								} else {
									if ($row['roleId'] == 3 || $row['roleId'] == 4) {
										echo '<label class="flex items-center space-x-1">
											<input data-id="'.$row['roleId'].'" type="checkbox" class="h-4 w-4" checked><span class="text-sm text-gray-600 font-medium">'.$row['roleName'].'</span>
										</label>';
									}
								}
							}
						}
						?>

					</div>
					<!-- End of filter content -->
				</div>
				<!-- End of single filter -->

				<!-- Single filter -->
				<div id="sendingTypesFilter" class="flex flex-col px-4">
					<!-- Title -->
					<div id="sendingTypeTitle" class="flex items-center cursor-pointer space-x-2">
						<div id="icons">
							<svg id="icon_plus" class="w-6 h-6 text-gray-800 transform duration-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
							<svg id="icon_minus" class="w-6 h-6 text-gray-800 transform duration-200 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
						</div>
						<p class="text-xs font-semibold uppercase text-gray-600">Sending type</p>
					</div>
					
					<!-- Separator -->
					<div class="border border-gray-300 mt-1"></div>
					
					<!-- Filter content -->
					<div class="mt-2 flex flex-col space-y-1 hidden">
						<?php
						$sql = "SELECT sendingId, sendingType FROM sendingType ORDER BY sendingId ASC";
						$sendingTypeResults = mysqli_query($conn, $sql);
						$resultCheck = mysqli_num_rows($sendingTypeResults);
						if ($resultCheck > 0) {
							while ($row = mysqli_fetch_assoc($sendingTypeResults)) {
								echo '<label class="flex items-center space-x-1">
										<input data-id="'.$row['sendingId'].'" type="checkbox" class="h-4 w-4" checked><span class="text-sm text-gray-600 font-medium">'.$row['sendingType'].'</span>
									</label>';
							}
						}
						?>
					</div>
					<!-- End of filter content -->
				</div>
				<!-- End of single filter -->

			</div>
			<!-- End of filters -->
		</div>
	</div>
	<!-- End of filters subnav -->

	<!-- Site content -->
	<div class="flex-auto grid grid-cols-1 p-4 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-3 lg:gap-8 lg:p-8 auto-rows-min">
		<!-- Card -->
		<div class="col-span-1 flex flex-row justify-center items-center space-x-10 card-wrapper h-32 relative bg-gradient-to-r from-white to-gray-200 overflow-hidden">

			<div class="flex flex-col items-center" style="z-index:1;">
				<?php
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2 ) {
					$groupFilter = 'users.groupId';
				} else {
					$groupFilter = $_SESSION['groupId'];
				}
				$sql = "
				SELECT COUNT(userId) as totalUsers FROM users WHERE users.groupId = $groupFilter
				";
				$result = mysqli_query($conn, $sql);
				if ( mysqli_num_rows($result) > 0 ) {
					while ($row = mysqli_fetch_assoc($result)) {
						$totalUsers = $row['totalUsers'];
					}
				}

				$sql = "
				SELECT COUNT(userId) as totalGroupAdmins FROM users WHERE users.groupId = $groupFilter AND users.roleId = 3
				";
				$result = mysqli_query($conn, $sql);
				if ( mysqli_num_rows($result) > 0 ) {
					while ($row = mysqli_fetch_assoc($result)) {
						$totalGroupAdmins = $row['totalGroupAdmins'];
					}
				}
				?>
				<p class="uppercase text-6xl font-bold"><?php echo $totalUsers; ?></p>
				<p class="uppercase text-sm text-gray-400 font-medium whitespace-nowrap">Total users</p>
			</div>
			<div class="flex flex-col items-center lg:hidden xl:flex" style="z-index:1;">
				<p class="uppercase text-6xl font-bold"><?php echo $totalGroupAdmins; ?></p>
				<p class="uppercase text-sm text-gray-400 font-medium whitespace-nowrap">Group admins</p>
			</div>

			<div class="absolute -bottom-6 -right-2 text-white opacity-80 transform -rotate-6" style="z-index:0;">
				<svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
			</div>
		</div>
		<!-- End of card -->

		<!-- Card -->
		<div class="flex col-span-1 flex-row justify-center items-center space-x-4 card-wrapper h-32 relative bg-gradient-to-r from-white to-gray-200 overflow-hidden">

			<div class="flex flex-col text-center" style="z-index:1;">
				<?php
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2 ) {
					$groupFilter = '`groups`.groupId';
				} else {
					$groupFilter = $_SESSION['groupId'];
				}
				$sql = "
				SELECT COUNT(historyId) as nAlarmsTriggered 
				FROM triggeredAlarmsHistory
				LEFT JOIN devices ON triggeredAlarmsHistory.deviceId = devices.deviceId
				LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
				WHERE `groups`.groupId = $groupFilter;
				";
				$result = mysqli_query($conn, $sql);
				if ( mysqli_num_rows($result) > 0 ) {
					while ($row = mysqli_fetch_assoc($result)) {
						$nAlarms = $row['nAlarmsTriggered'];
					}
				}
				if ($nAlarms > 0) {
					$sql = "
					SELECT clearedAt
					FROM triggeredAlarmsHistory
					LEFT JOIN devices ON triggeredAlarmsHistory.deviceId = devices.deviceId
					LEFT JOIN `groups` ON devices.groupId = `groups`.groupId
					WHERE `groups`.groupId = $groupFilter
					ORDER BY triggeredAlarmsHistory.clearedAt DESC
					LIMIT 1;
					";
					$result = mysqli_query($conn, $sql);
					if ( mysqli_num_rows($result) > 0 ) {
						while ($row = mysqli_fetch_assoc($result)) {
							$alarmDate = $row['clearedAt'];
							$alarmDate = date("H:i F j, Y", strtotime($alarmDate)+60*60);
							$alarmDate = 'Latest: '. $alarmDate;
						}
					}
				} else {
					$alarmDate = '';
				}
				?>
				<p class="uppercase text-sm text-gray-400 font-medium text-center">Alarms Sent</p>
				<p class="uppercase text-6xl font-bold"><?php echo $nAlarms; ?></p>
				<p class="uppercase text-sm text-gray-400 font-medium text-center"><?php echo $alarmDate; ?></p>
			</div>

			<div class="absolute -bottom-6 -right-2 text-white opacity-80 transform -rotate-6" style="z-index:0;">
				<svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
			</div>
		</div>
		<!-- End of card -->

		<!-- Card -->
		<div class="hidden col-span-1 lg:flex flex-row justify-center items-center space-x-4 card-wrapper h-32 relative bg-gradient-to-r from-white to-gray-200 overflow-hidden">

			<div class="flex flex-col" style="z-index:1;">
				<p class="uppercase text-sm text-gray-400 font-medium text-center">Newest users</p>
			</div>

			<div id="card_latestUsers" class="grid grid-cols-1 divide-y divide-gray-200 divide-solid" style="z-index: 1;">
				<!-- Filled via js -->
			</div>

			<div class="absolute -bottom-6 -right-2 text-white opacity-80 transform -rotate-6" style="z-index:0;">
				<svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
			</div>
		</div>
		<!-- End of card -->

		<!-- Table card -->
		<div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white shadow-lg border">
			<div class="flex bg-white overflow-x-auto min-w-full">
				<table class="table-fixed min-w-full">
					<thead class="uppercase text-xs bg-bluegray-50 border-b text-bluegray-900">
						<tr>
							<th class="text-left w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Name</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Group</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Role</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Phone</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Sending Type</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap"></th>
						</tr>
					</thead>
					<tbody id="usersTableBody">
						<!-- This area gets filled via PHP -->
					</tbody>
				</table>
			</div>
			<div id="loadingOverlay" class="flex flex-auto w-full justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
				<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
				<p>Loading...</p>
			</div>

			<div class="flex flex-col items-center justify-center py-4">
				<div class="flex">
					<button id="previousUsersButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
					<button id="nextUsersButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
				</div>
				<p class="mt-4 text-sm font-semibold">Showing <span id="usersRange"></span> of <span id="usersTotal"></span></p>
			</div>
		</div>
	</div>
	<!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->	


<!-- Load AJAX script for this page -->
<script src="./js/ajaxUsers.js"></script>

<!-- Footer -->
<?php 
include_once('footer.php');
?>
