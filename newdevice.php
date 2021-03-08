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
<div class="flex-auto bg-lightblue-50">
	
	<!-- Page info section -->
	<div class="bg-white border-b border-gray-200">
		<!-- Info column grid -->
		<div class="grid grid-cols-5 gap-8 lg:gap-16 p-8">
			<!-- Summary column -->
			<div class="space-y-6 col-span-2 lg:col-span-1">
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2 whitespace-nowrap">
					Info #1
				</div>
			</div>
			<!-- 2nd Column -->
			<div class="space-y-6 col-span-3 lg:col-span-2">
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2">
					Pie chart #1
				</div>
			</div>
			<!-- 3rc Column -->
			<div class="space-y-6 col-span-2 hidden lg:block">
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2">
					Pie chart #2
				</div>
			</div>
		</div>
		<!-- End of info column grid -->
	</div>
    <!-- End of page info section -->
    
    <!-- Site content -->
    <div class="flex flex-col lg:flex-row lg:m-8 mt-4">
        <div id="deviceTypesNav" class="flex flex-row lg:flex-col overflow-x-auto scrollbars-hidden">
            <!-- Generated via JS -->

        </div>

		<!-- Sub-page nav bar is generated automatically based on children on sections div -->
		<div id="sections" class="flex-auto border-b border-gray-200 lg:border lg:border-l-0 bg-white">
			<!-- BSC section -->
			<div id="BSC" class="flex flex-col md:flex-row lg:mx-8">
			
				<!-- BSC form wrapper -->
				<div class="flex-auto w-full max-w-xl lg:max-w-2xl mx-auto">
					<!-- Form -->
					<form method="post" autocomplete="off" class="flex flex-col">
						<p class="form-field-title text-center mt-4 border-b mx-4">Device information</p>

						<!-- Row -->
						<div class="flex flex-col lg:flex-row flex-auto">
							<div class="flex flex-col flex-1 mx-4 mt-2">
								<p class="form-field-title">Product Type<span class="text-red-500">*</span></p>
								<select id="productType" class="select-field-input">
									<?php
										$sql = "SELECT productId, productName FROM products ORDER BY productName ASC";
										$result = mysqli_query($conn, $sql);
										$resultCheck = mysqli_num_rows($result);
										if ($resultCheck > 0) {
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option data-id="'.$row['productId'].'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 normal-case">'.$row['productName'].'</option>';
											}
										}  
									?>
								</select>
							</div>

							<div class="flex flex-col flex-1 mx-4 mt-2">
								<p class="form-field-title">Device Type<span class="text-red-500">*</span></p>
								<select id="deviceType" class="select-field-input">
									<option data-id="-1" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected disabled>Type</option>
									<?php
										$sql = "SELECT deviceTypeId, deviceTypeName FROM deviceTypes ORDER BY deviceTypeName ASC";
										$result = mysqli_query($conn, $sql);
										$resultCheck = mysqli_num_rows($result);
										if ($resultCheck > 0) {
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option data-id="'.$row['deviceTypeId'].'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 normal-case">'.$row['deviceTypeName'].'</option>';
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
								<div class="flex">
									<div class="flex justify-center items-center bg-bluegray-100 border border-r-0 rounded-l text-xs uppercase font-semibold text-bluegray-600 px-2"><span id="devPrefix"></span></div>
									<input type="text" id="deviceName" placeholder="Device Name" required spellcheck="false" autocomplete="none" class="text-field-input rounded-l-none">
								</div>
							</div>

							<div class="flex flex-col flex-1 mx-4 mt-2">
								<p class="form-field-title">Device phone number<span class="text-red-500">*</span></p>
								<input type="text" id="devicePhone" placeholder="Device phone number" required spellcheck="false" autocomplete="none" class="text-field-input">
							</div>
						</div>
						<!-- End of row -->

						<!-- Row -->
						<div class="flex flex-auto">
							<div class="flex flex-col flex-1 mx-4 mt-2">
								<p class="form-field-title">Group<span class="text-red-500">*</span></p>
								<select id="groupId" class="select-field-input">
									<option data-id="-1" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected disabled>Group</option>
									<?php
										$sql = "SELECT groupId, groupName FROM `groups` ORDER BY groupName ASC";
										$result = mysqli_query($conn, $sql);
										$resultCheck = mysqli_num_rows($result);
										if ($resultCheck > 0) {
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option data-id="'.$row['groupId'].'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 normal-case">'.$row['groupName'].'</option>';
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
								<select id="channelType" class="select-field-input">
									<option data-id="-1" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected disabled>Type</option>
									<option data-id="AI" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">AI</option>
									<option data-id="DI" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">DI</option>
									<option data-id="COUNTER" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">COUNTER</option>
								</select>
							</div>

							<div class="flex-auto flex">
								<div class="flex flex-col flex-auto mx-4 mt-2">
									<p class="form-field-title">Channel Name<span class="text-red-500">*</span></p>
									<input type="text" id="channelName" placeholder="Channel name" spellcheck="false" autocomplete="none" class="text-field-input">
								</div>

								<div id="channelUnit" class="flex flex-col flex-1 mx-4 mt-2">
									<p class="form-field-title">Unit<span class="text-red-500">*</span></p>
									<select id="channelUnitSelect" class="select-field-input">
										<option data-id="-1" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected disabled>Unit</option>
										<?php
											$sql = "SELECT unitId, unitName FROM units ORDER BY unitName ASC";
											$result = mysqli_query($conn, $sql);
											$resultCheck = mysqli_num_rows($result);
											if ($resultCheck > 0) {
												while ($row = mysqli_fetch_assoc($result)) {
													echo '<option data-id="'.$row['unitId'].'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 normal-case">'.$row['unitName'].'</option>';
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
										<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Next calibration date<span class="text-red-500">*</span></p>
										<input type="date" id="nextCalibrationDate" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 w-full transition-all focus:border-gray-400 rounded">

										<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1 mt-2">Last calibration date<span class="text-red-500">*</span></p>
										<input type="date" id="lastCalibrationDate" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 w-full transition-all focus:border-gray-400 rounded">
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
										<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Start<span class="text-red-500">*</span></p>
										<input type="date" id="subStartDate" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 w-full transition-all focus:border-gray-400 rounded">

										<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1 mt-2">Finish<span class="text-red-500">*</span></p>
										<input type="date" id="subFinishDate" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 w-full transition-all focus:border-gray-400 rounded">
									</div>
								</div>
							</div>
							<!-- End of subscriptions -->
						</div>
						<!-- End of row -->
						

						<!-- Submit button -->
						<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1 text-center mt-8 border-b mb-4 mx-4 lg:mx-0">Submit</p>
						<div class="flex flex-row flex-1 my-0 mt-2 mx-4 lg:mx-0 lg:my-0 lg:mt-2 justify-center">
							<button type="submit" id="submit" name="submit" title="Create" class="focus:outline-none flex items-center justify-center border border-transparent bg-lightblue-500 transition-all hover:bg-lightblue-600 text-lightblue-100 hover:border-lightblue-500 hover:text-white space-x-2 font-semibold uppercase text-sm h-12 w-40 mb-8 rounded shadow">
								<p>Create</p>
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></i>
							</button>

						</div>

					</form>
					<!-- End of form -->
					<div id="simMessage" class="bg-red-200 cursor-pointer">Simulate Message</div>
				</div>
				<!-- End of BSC form wrapper -->
			</div>
			<!-- End of BSC section -->
			
			<div id="ADU" class="bg-white hidden">
			adu
			</div>
			
			<div id="SIGFOX" class="bg-white hidden">
			sigfox
			</div>

		</div>
        <!-- End of sub page nav bar -->
    </div>
    <!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->

<!-- Load AJAX script for this page -->
<script src="./js/ajaxNewDevice.js"></script>

<!-- Footer -->
<?php 
include_once('footer.php');
?>
