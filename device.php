<?php
session_start();
$_SESSION['activeUrl'] = 'devices.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto flex-col lg:flex-row bg-gray-100 flex">

	<!-- Edit device modal -->
	<div id="editDevice-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
		<div class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
			<!-- Title/close btn -->
			<div class="flex justify-between items-center border-b pb-1 border-gray-300">
				<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">Device profile</p>
				<svg id="close-device-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
			</div>

			<!-- Content -->
			<div id="groupprofile-content" class="w-full flex flex-col divide-y divide-gray-300">
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">ID</div>
					<div id="deviceProfile-id" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">Name</div>
					<input id="deviceProfile-deviceName" class="flex-1 h-8 border border-gray-300" type="text">
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">SIM</div>
					<input id="deviceProfile-devicePhone" class="flex-1 h-8 border border-gray-300" type="text">
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">Group</div>
					<select id="deviceProfile-group" class="flex-1 h-8 py-0 border border-gray-300">
						<?php
						$sql = "SELECT groupId, groupName FROM `groups` ORDER BY groupName ASC";
						$result = mysqli_query($conn, $sql);
						$resultCheck = mysqli_num_rows($result);
						if ($resultCheck > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<option data-id="'.$row['groupId'].'">'.$row['groupName'].'</option>';
							}
						}  
						?>
					</select>
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">Product</div>
					<select id="deviceProfile-product" class="flex-1 h-8 py-0 border border-gray-300">
						<?php
						$sql = "SELECT productId, productName FROM products";
						$result = mysqli_query($conn, $sql);
						$resultCheck = mysqli_num_rows($result);
						if ($resultCheck > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<option data-id="'.$row['productId'].'">'.$row['productName'].'</option>';
							}
						}  
						?>
					</select>
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">Device Type</div>
					<select id="deviceProfile-deviceType" class="flex-1 h-8 py-0 border border-gray-300">
						<?php
						$sql = "SELECT deviceTypeId, deviceTypeName FROM deviceTypes";
						$result = mysqli_query($conn, $sql);
						$resultCheck = mysqli_num_rows($result);
						if ($resultCheck > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo '<option data-id="'.$row['deviceTypeId'].'">'.$row['deviceTypeName'].'</option>';
							}
						}  
						?>
					</select>
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">Created At</div>
					<div id="deviceProfile-createdAt" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
				</div>
				<div class="flex h-10 items-center">
					<div class="flex-1 text-center text-sm">Created By</div>
					<div id="deviceProfile-createdBy" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
				</div>

				<div class="flex items-center">
					<div class="w-20 lg:flex-1 text-center text-sm">Channels</div>

					<div id="deviceProfile-channels" class="flex-1 flex flex-col space-y-1 mt-2">
				
					</div>
				</div>
			</div>

			<!-- Buttons -->
			<div id="deviceButtons" class="flex justify-end items-center mt-4 space-x-4">
				<button id="deleteDevice" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Delete</button>

				<button id="saveDevice" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-green-500 hover:bg-green-600 transition-all focus:bg-green-600 focus:text-white">Save</button>
				
				<button id="cancelDevice" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-lightblue-500 hover:bg-lightblue-600 transition-all focus:bg-lightblue-600 focus:text-white">Cancel</button>
			</div>
		</div>
	</div>

	<!-- Filters subnav -->
	<div class="block w-screen overflow-x-auto flex-none bg-gray-200 lg:h-full lg:w-44 xl:w-60">
		<div class="lg:fixed h-16 lg:h-full flex flex-row lg:flex-col border-b lg:border-b-0 lg:border-r border-gray-300" style="width: inherit;">
			<!-- Subpage nav -->
			<div class="bg-gray-100 border-r lg:border-r-0 lg:border-b border-gray-300 flex lg:flex-col items-center justify-center py-6 px-2">
				<span id="sidePanel-deviceName" class="text-sm whitespace-nowrap font-semibold uppercase mx-6 lg:mx-0 bg-blue-100 text-lightblue-600"></span>
				<?php 
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					echo '
					<button id="editDevice" class="lg:mt-4 mr-2 lg:mr-0 p-1 flex justify-center items-center text-xs uppercase font-semibold text-lightblue-600 border border-lightblue-500 transition-all hover:text-white hover:bg-lightblue-500 focus:outline-none focus:text-white focus:bg-lightblue-500">
						<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
						Edit
					</button>';
				}
				?>
			</div>
			<div id="subPageNav" class="flex flex-row px-2 space-x-8 lg:space-x-0 lg:flex-col lg:space-y-8 h-full lg:pt-8">
				<!-- Filled via js according to product type -->
			</div>
			<!-- End of subpage nav -->
		</div>
	</div>
	<!-- End of filters subnav -->

	<!-- Site content -->
	<div id="siteContent" data-groupId="<?php echo $_SESSION['groupId']?>" data-roleId="<?php echo $_SESSION['roleId']?>" class="flex-auto p-4 lg:p-6">
		<!-- Filled via js -->


	</div>
	<!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxSingleDevice.js"></script>

<!-- Footer -->
<?php 
include_once('./footer.php');
?>

