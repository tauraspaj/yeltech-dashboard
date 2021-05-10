<?php
session_start();

if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
	header("location: index.php");
	exit();
}

$_SESSION['activeUrl'] = 'newdevice.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto flex-col lg:flex-row bg-gray-100 flex p-4">
	<!-- Site content -->
	<div class="flex-auto grid grid-cols-1 xl:grid-cols-2">
		<!-- Wrapper -->
		<div class="col-span-1 xl:col-span-1 flex-auto flex flex-col lg:flex-row">
			<div id="deviceTypesNav" class="flex flex-row lg:flex-col overflow-x-auto scrollbars-hidden">
				<!-- Generated via JS -->
			</div>

			<!-- Sub-page nav bar is generated automatically based on children on sections div -->
			<div id="sections" class="flex-auto border-b border-gray-200 lg:border lg:border-l-0 bg-white">
				<!-- BSC section -->
				<div id="BSC" class="flex flex-col md:flex-row lg:mx-8 border border-t-0 lg:border-none">
				
					<!-- BSC form wrapper -->
					<div class="flex-auto w-full max-w-xl lg:max-w-2xl mx-auto">
						<!-- Form -->
						<form method="post" autocomplete="off" class="flex flex-col">
							<p class="form-field-title text-center mt-4 border-b mx-4">Device information</p>

							<!-- Row -->
							<div class="flex flex-col lg:flex-row flex-auto">
								<div class="flex flex-col flex-1 mx-4 mt-2">
									<p class="form-field-title">Product Type<span class="text-red-500">*</span></p>

									<select id="productType">
										<?php
											$sql = "SELECT productId, productName FROM products ORDER BY productName ASC";
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

								<div class="flex flex-col flex-1 mx-4 mt-2">
									<p class="form-field-title">Device Type<span class="text-red-500">*</span></p>

									<select id="deviceType">
										<option data-id="-1" selected disabled>Type</option>
										<?php
											$sql = "SELECT deviceTypeId, deviceTypeName FROM deviceTypes ORDER BY deviceTypeName ASC";
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
							</div>
							<!-- End of row -->

							<!-- Row -->
							<div class="flex flex-col lg:flex-row flex-auto">
								<div class="flex flex-col flex-1 mx-4 mt-2">
									<p class="form-field-title">Device Name<span class="text-red-500">*</span></p>
									<input type="text" id="deviceName" placeholder="Device Name" required spellcheck="false" autocomplete="none">
								</div>

								<div class="flex flex-col flex-1 mx-4 mt-2">
									<p class="form-field-title">Device phone number<span class="text-red-500">*</span></p>
									<input type="text" id="devicePhone" placeholder="Device phone number" required spellcheck="false" autocomplete="none">
								</div>
							</div>
							<!-- End of row -->

							<!-- Row -->
							<div class="flex flex-auto">
								<div class="flex flex-col flex-1 mx-4 mt-2">
									<p class="form-field-title">Group<span class="text-red-500">*</span></p>
									<select id="groupId" class="select-field-input">
										<option data-id="-1" selected disabled>Group</option>
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
							</div>
							<!-- End of row -->

							<p class="form-field-title text-center mt-4 border-b mx-4">Channels</p>

							<!-- Row -->
							<div class="flex flex-col lg:flex-row flex-auto">
								<div class="flex-auto flex flex-col mx-4 mt-2">
									<p class="form-field-title">Channel Type<span class="text-red-500">*</span></p>
									<select id="channelType">
										<option data-id="-1" selected disabled>Type</option>
										<option data-id="AI">AI</option>
										<option data-id="DI">DI</option>
										<option data-id="COUNTER">COUNTER</option>
									</select>
								</div>

								<div class="flex-auto flex">
									<div class="flex flex-col flex-auto mx-4 mt-2">
										<p class="form-field-title">Channel Name<span class="text-red-500">*</span></p>
										<input type="text" id="channelName" placeholder="Channel name" spellcheck="false" autocomplete="none">
									</div>

									<div id="channelUnit" class="flex flex-col flex-1 mx-4 mt-2">
										<p class="form-field-title">Unit<span class="text-red-500">*</span></p>

										<select id="channelUnitSelect">
											<option data-id="-1" selected disabled>Unit</option>
											<?php
												$sql = "SELECT unitId, unitName FROM units ORDER BY unitName ASC";
												$result = mysqli_query($conn, $sql);
												$resultCheck = mysqli_num_rows($result);
												if ($resultCheck > 0) {
													while ($row = mysqli_fetch_assoc($result)) {
														echo '<option data-id="'.$row['unitId'].'">'.$row['unitName'].'</option>';
													}
												}  
											?>
										</select>
									</div>
								</div>
							</div>
							<!-- End of row -->

							<!-- Row -->
							<div class="flex flex-row flex-auto">
								<div class="mt-2 mr-4">
									<button id="addChannel" name="addChannel" type="button" class="focus:outline-none h-10 flex justify-center items-center whitespace-nowrap ml-4 px-4 cursor-pointer rounded bg-green-100 border border-green-200 text-sm font-semibold text-green-800 transition-all hover:bg-green-300 hover:text-green-900 hover:border-green-500">Add channel</button>
								</div>
								<div class="flex-auto">
									<div id="channelsDisplay" class="grid grid-cols-1 lg:grid-cols-2 gap-2 pt-2 mr-4">
										<!-- Filled with js -->
									</div>
								</div>
							</div>
							<!-- End of row -->

							<!-- Row -->
							<div class="flex flex-col lg:flex-row lg:space-x-16">
								<!-- Calibration -->
								<div class="flex-1">
									<p class="form-field-title text-center mt-8 border-b mb-4 mx-4 lg:mx-0">Calibration Info</p>
									<div class="flex flex-col justify-center">
										<!-- Checkbox -->
										<div id="calibrationParent" class="flex justify-center items-center">
											<input type="checkbox" id="calibrationCheckbox" class="hidden">
											<label id="calibrationLabel" for="calibrationCheckbox" class="bg-red-200 flex items-center p-1 rounded-lg w-12 h-6 cursor-pointer transition-all duration-300">
												<div id="checkboxBubble" class="bg-red-600 h-4 w-4 rounded-full transition-all duration-300 transform"></div>
											</label>
										</div>
										<!-- End of checkbox -->
										<div id="calibrationFields" class="flex-auto flex-col mt-4 mx-4 lg:mx-0 hidden">
											<p class="form-field-title">Last calibration date<span class="text-red-500">*</span></p>
											<input type="date" id="lastCalibrationDate">

											<p class="form-field-title mt-3">Next calibration date<span class="text-red-500">*</span></p>
											<input type="date" id="nextCalibrationDate">
										</div>

									</div>
								</div>
								<!-- End of calibration -->

								<!-- Subscriptions -->
								<div class="flex-1">
									<p class="form-field-title text-center mt-8 border-b mb-4 mx-4 lg:mx-0">Subscription</p>
									<div class="flex flex-col justify-center">
										<!-- Checkbox -->
										<div class="flex justify-center items-center">
											<input type="checkbox" id="subscriptionCheckbox" class="hidden">
											<label id="subscriptionLabel" for="subscriptionCheckbox" class="bg-red-200 flex items-center p-1 rounded-lg w-12 h-6 cursor-pointer transition-all duration-300">
												<div id="checkboxBubble" class="bg-red-600 h-4 w-4 rounded-full transition-all duration-300 transform"></div>
											</label>
										</div>
										<!-- End of checkbox -->
										<div id="subscriptionFields" class="flex-auto flex-col mt-4 mx-4 lg:mx-0 hidden">
											<p class="form-field-title">Start<span class="text-red-500">*</span></p>
											<input type="date" id="subStartDate">

											<p class="form-field-title mt-3">Finish<span class="text-red-500">*</span></p>
											<input type="date" id="subFinishDate">
										</div>
									</div>
								</div>
								<!-- End of subscriptions -->
							</div>
							<!-- End of row -->
							
							<!-- Submit button -->
							<p class="form-field-title text-center mt-8 border-b mb-4 mx-4 lg:mx-0">Submit</p>
							<div class="flex flex-row flex-1 my-0 mt-2 mx-4 lg:mx-0 lg:my-0 lg:mt-2 justify-center">
								<button type="submit" id="submit" name="submit" title="Create device" class="w-40">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path></svg>
									<p>Create</p>
								</button>

							</div>

						</form>
						<!-- End of form -->
					</div>
					<!-- End of BSC form wrapper -->
				</div>
				<!-- End of BSC section -->
				
				<!-- <div id="ADU" class="bg-white hidden">
				adu
				</div>
				
				<div id="SIGFOX" class="bg-white hidden">
				sigfox
				</div> -->

			</div>
			<!-- End of sub page nav bar -->
		</div>
		<!-- End of wrapper -->
	</div>
	<!-- End of site content -->
</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxNewDevice.js"></script>

<!-- Footer -->
<?php 
include_once('./footer.php');
?>

