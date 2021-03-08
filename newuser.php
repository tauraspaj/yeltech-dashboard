<?php
session_start();

if ($_SESSION['roleId'] == 4) {
	header("location: users.php");
	exit();
}

$_SESSION['activeUrl'] = 'users.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto bg-lightblue-50">

	<!-- Page info section -->
	<div class="">
		<!-- Info column grid -->
		<div class="grid grid-cols-5 gap-8 lg:gap-16 p-8">
			<!-- Summary column -->
			<div class="space-y-6 col-span-2 lg:col-span-1">
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2 whitespace-nowrap">
					Users Summary
				</div>
				<?php
				if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
					$groupId = $_SESSION['groupId'];
					// Group admins and standard users only see their group info
					$sql = "SELECT COUNT(*) FROM users WHERE groupId = $groupId";
					$result = mysqli_query($conn, $sql);
					$resultCheck = mysqli_num_rows($result);
					if ($resultCheck > 0) {
						while ($row = mysqli_fetch_assoc($result)) {
							$totalUsers = $row['COUNT(*)'];
						}
					}

					$sql = "SELECT COUNT(*) FROM users WHERE groupId = $groupId AND roleId = 3";
					$result = mysqli_query($conn, $sql);
					$resultCheck = mysqli_num_rows($result);
					if ($resultCheck > 0) {
						while ($row = mysqli_fetch_assoc($result)) {
							$totalAdmins = $row['COUNT(*)'];
						}
					}
				} elseif ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					// Yeltech admins and super admins see global info
					$sql = "SELECT COUNT(*) FROM users";
					$result = mysqli_query($conn, $sql);
					$resultCheck = mysqli_num_rows($result);
					if ($resultCheck > 0) {
						while ($row = mysqli_fetch_assoc($result)) {
							$totalUsers = $row['COUNT(*)'];
						}
					}

					$sql = "SELECT COUNT(*) FROM users WHERE roleId = 3";
					$result = mysqli_query($conn, $sql);
					$resultCheck = mysqli_num_rows($result);
					if ($resultCheck > 0) {
						while ($row = mysqli_fetch_assoc($result)) {
							$totalAdmins = $row['COUNT(*)'];
						}
					}
				}
				?>
				<div class="flex flex-col text-center">
					<p class="text-5xl font-bold text-bluegray-700 tracking-wide"><?php echo $totalUsers; ?></p>
					<p class="text-sm uppercase font-semibold text-bluegray-500 tracking-tight">Total Users</p>
				</div>
				<div class="flex flex-col text-center">
					<p class="text-5xl font-bold text-bluegray-700 tracking-wide"><?php echo $totalAdmins; ?></p>
					<p class="text-sm uppercase font-semibold text-bluegray-500 tracking-tight">Group Admins</p>
				</div>
			</div>
			<!-- 2nd Column -->
			<div class="space-y-6 col-span-3 lg:col-span-2">
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2">
					Pie chart #1
				</div>
				<div class="absolute w-72">
					<div class="relative h-full w-full">
						<canvas id="chart1"></canvas>
					</div>
				</div>
			</div>
			<!-- 3rc Column -->
			<div class="space-y-6 col-span-2 hidden lg:block">
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2">
					Pie chart #2
				</div>
				<div class="absolute w-72">
					<div class="relative h-full w-full">
						<canvas id="chart2"></canvas>
					</div>
				</div>
			</div>
		</div>
		<!-- End of info column grid -->
	</div>
	<!-- End of page info section -->

	<!-- Site content -->
	<div>
		<!-- Sub page nav bar -->
		<div class="flex justify-between bg-bluegray-100 border-t border-gray-200">
			<div>

			</div>
			<div class="flex justify-center items-center h-12">
				<a href="users.php" class="flex justify-center items-center h-full bg-red-500 px-6 text-sm capitalize hover:bg-red-700 transition-all duration-250 text-red-100 border-l border-gray-200 whitespace-nowrap space-x-1">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
					<p class="flex justify-center items-center">Cancel</p>
				</a>
			</div>
		</div>

		<?php
		$errorMessages = "";
		$errorFields = array();
		$enteredValues = array('userName' => "", 'userRole' => "", 'userGroup' => '', 'userEmail' => "", 'userPhone' => "");
		if (isset($_SESSION["registerErrors"])) {
			foreach ($_SESSION["registerErrors"]["messages"] as $message) {
				$errorMessages = $errorMessages.'<br>'.$message;
			}

			foreach ($_SESSION["registerErrors"]["fields"] as $field) {
				array_push($errorFields, $field);
			}
			$enteredValues = $_SESSION["enteredValues"];
			
			unset($_SESSION["registerErrors"]);
			unset($_SESSION["enteredValues"]);
		}

		if (isset($_SESSION["successMessage"])) {
			$successMessage = $_SESSION["successMessage"];
			unset($_SESSION["successMessage"]);
		}
		?>
		<!-- New user section -->
		<div class="bg-white border-b border-t border-gray-200 p-8">
			<!-- Form wrapper -->
			<div class="max-w-4xl mx-auto">
				<form action="includes/registerUser.inc.php" method="post" class="flex flex-col md:space-y-3" autocomplete="off">
					<!-- Display error message -->
					<?php 
						if ($errorMessages != '') {
							echo '<p class="italic text-sm text-red-500 text-center">'.$errorMessages.'<p>';
						}
					?>

					<!-- Name & Role -->
					<div class="flex flex-col md:flex-row md:space-x-16">
						<div class="flex flex-1 flex-col">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Name<span class="text-red-500">*</span></p>
							<input type="text" name="userName" placeholder="Name" value="<?php echo $enteredValues["userName"]; ?>" required spellcheck="false" autocomplete="none" class="outline-none capitalize border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 <?php echo (in_array("userName", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
						</div>
						<div class="flex flex-1 flex-col my-1">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Role<span class="text-red-500">*</span></p>
							<select name="userRole" class="outline-none border border-gray-200 h-12 px-4 text-sm capitalize font-semibold text-bluegray-800 bg-bluegray-100 hover:bg-white hover:text-bluegray-900 w-full <?php echo (in_array("userRole", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
								<?php 
								$selected = 'selected="selected"';
								$enteredValues["userRole"].'test';
								if ($_SESSION['roleId'] == 4) { //Standard user
									echo '';
								}
								if ($_SESSION['roleId'] <= 3) { //Group Admin
									echo '<option value="4" ';
									if ($enteredValues["userRole"] == 4) {
										echo $selected;
									}
									echo '>Standard User</option>';

									echo '<option value="3" ';
									if ($enteredValues["userRole"] == 3) {
										echo $selected;
									}
									echo '>Group Admin</option>';

								}
								if ($_SESSION['roleId'] <= 2) { //Yeltech Admin
									//echo '<option value="1">Super Admin</option>';
								}
								if ($_SESSION['roleId'] == 1) { //Super Admin
									echo '<option value="2" ';
									if ($enteredValues["userRole"] == 2) {
										echo $selected;
									}
									echo '>Yeltech Admin</option>';

									echo '<option value="1" ';
									if ($enteredValues["userRole"] == 1) {
										echo $selected;
									}
									echo '>Super Admin</option>';
								}
								?>
							</select>
						</div>
					</div>

					<!-- Group, Email, Phone -->
					<div class="flex flex-col md:flex-row md:space-x-16">
						<div class="flex flex-1 flex-col my-1">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Group<span class="text-red-500">*</span></p>
							<select name="userGroup" class="outline-none border border-gray-200 h-12 px-4 text-sm capitalize font-semibold text-bluegray-800 bg-bluegray-100 hover:bg-white hover:text-bluegray-900 w-full <?php echo (in_array("userGroup", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
								<?php
								if ($_SESSION['roleId'] == 4) { //Standard user
									echo '';
								}
								if ($_SESSION['roleId'] == 3) { //Group Admin
									echo '<option value="'.$_SESSION['groupId'].'">'.$_SESSION['groupName'].'</option>';
								}
								if ($_SESSION['roleId'] <= 2) { //Yeltech Admin & Super Admin
									$sql = "SELECT groupId, groupName FROM `groups`";

									$result = mysqli_query($conn, $sql);
									$resultCheck = mysqli_num_rows($result);

									echo '<option value="-1" selected disabled>- Select group</option>';
									if ($resultCheck > 0) {
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="'.$row['groupId'].'" ';
											if ($enteredValues["userGroup"] == $row['groupId']) {
												echo $selected;
											}
											echo '>'.$row['groupName'].'</option>';
										}
									}
								}
								?>
							</select>
						</div>
						<div class="flex flex-1 flex-col my-1">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Email<span class="text-red-500">*</span></p>
							<input type="email" name="userEmail" placeholder="Email" value="<?php echo $enteredValues["userEmail"]; ?>" required spellcheck="false" autocomplete="none" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 <?php echo (in_array("userEmail", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
						</div>
						<div class="flex flex-1 flex-col my-1">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Phone<span class="text-red-500">*</span></p>
							<input type="text" name="userPhone" placeholder="Phone" value="<?php echo $enteredValues["userPhone"]; ?>" required spellcheck="false" autocomplete="none" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 <?php echo (in_array("userPhone", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
						</div>
					</div>

					<!-- Password, confirm password -->
					<div class="flex flex-col md:flex-row md:space-x-16">
						<div class="flex flex-1 flex-col my-1">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Password<span class="text-red-500">*</span></p>
							<input type="password" name="userPwd" placeholder="Password" value="" required spellcheck="false" autocomplete="none" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 <?php echo (in_array("userPwd", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
						</div>
						<div class="flex flex-1 flex-col my-1">
							<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Confirm Password<span class="text-red-500">*</span></p>
							<input type="password" name="userConfPwd" placeholder="Confirm password" value="" required spellcheck="false" autocomplete="none" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 <?php echo (in_array("userConfPwd", $errorFields)) ? 'border-red-500' : '' ?> transition-all focus:border-gray-400">
						</div>
					</div>

					<!-- Submit button -->
					<div class="flex justify-center">
						<button type="submit" name="submit" title="Create" class="flex items-center justify-center bg-lightblue-500 border-green-800 hover:bg-lightblue-700 text-lightblue-100 space-x-2 font-semibold uppercase text-sm h-10 w-40 transition-all duration-250 mt-4 mb-8 rounded">
							<p>Create</p>
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></i>
						</button>
					</div>
				</form>
			</div>
			<!-- End of form wrapper -->
		</div>
		<!-- End of user section -->
	</div>
	<!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->

<?php 
include_once('footer.php');
?>