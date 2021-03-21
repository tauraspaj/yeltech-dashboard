<?php
session_start();
$_SESSION['activeUrl'] = 'devices.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto flex-col lg:flex-row bg-gray-100 flex">
	<!-- Filters subnav -->
	<div class="hidden lg:block flex-none bg-gray-100 lg:h-full lg:w-44 xl:w-60">
		<div class="fixed h-full flex flex-col" style="width: inherit;">
			<!-- Filters div -->
			<div class="flex flex-col space-y-8 h-full pt-8 bg-gray-200 rounded-tr-3xl shadow-md">
				<input id="pageSearchBar" type="text" class="h-10 w-40 xl:w-52 outline-none focus:outline-none bg-gray-100 rounded-full text-gray-800 font-medium flex justify-center items-center text-sm space-x-1 mx-auto px-4 border border-gray-300 transition-all focus:border-gray-500" placeholder="Filter devices...">

				<!-- Php code for roles filter -->
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
							<select id="groupFilter" class="focus:outline-none w-full h-8 bg-gray-50 border border-gray-400 px-2 text-sm">
								<option data-id="devices.groupId" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected>All Groups</option>
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
				<div id="productsFilter" class="flex flex-col px-4">
					<!-- Title -->
					<div id="productsTitle" class="flex items-center cursor-pointer space-x-2">
						<div id="icons">
							<svg id="icon_plus" class="w-6 h-6 text-gray-800 transform duration-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
							<svg id="icon_minus" class="w-6 h-6 text-gray-800 transform duration-200 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
						</div>
						<p class="text-xs font-semibold uppercase text-gray-600">Products</p>
					</div>
					<!-- Separator -->
					<div class="border border-gray-300 mt-1"></div>
					
					<!-- Filter content -->
					<div class="mt-2 flex flex-col space-y-1 hidden">
						<?php
						// ! Find products
						$productsString = '';

						if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
							$productsString = "products.productId = products.productId";
						} else {
							$productsArray = array();

							$sql = "SELECT devices.productId FROM devices WHERE devices.groupId = {$_SESSION['groupId']}";
							$result = mysqli_query($conn, $sql);
							if (mysqli_num_rows($result) > 0) {
								while ($row = mysqli_fetch_assoc($result)) {
									$productsArray[] = $row['productId'];
								}
							}

							$productsArray = array_unique($productsArray);
							$productsArray = array_values($productsArray);

							// Generate appropriate search string
							if (count($productsArray) == 0) {
								// Show nothing
								$productsString = 'false';
							} else {
								$arrLength = count($productsArray);
								for ($i = 0; $i < $arrLength; $i++) {
									if ($i == ($arrLength-1)) {
										$productsString .= 'products.productId = '.$productsArray[$i].' ';
									} else {
										$productsString .= 'products.productId = '.$productsArray[$i].' OR ';
									}
								}
							}
						}

						$sql2 = "
						SELECT products.productId, products.productName FROM products WHERE $productsString
						";
						$productResults = mysqli_query($conn, $sql2);
						if (mysqli_num_rows($productResults) > 0) {
							while ($row = mysqli_fetch_assoc($productResults)) {
								echo '<label class="flex items-center space-x-1">
										<input data-id="'.$row['productId'].'" type="checkbox" class="h-4 w-4" checked><span class="text-sm text-gray-600 font-medium">'.$row['productName'].'</span>
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
		<!-- Table card -->
		<div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white shadow-lg border">
			<div class="flex bg-white overflow-x-auto inline-block min-w-full">
				<table class="table-fixed min-w-full">
					<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
						<tr>
							<th class="text-left w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Name</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Group</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Location</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Next Calibration</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Alarms</th>
							<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap"></th>
						</tr>
					</thead>
					<tbody id="devicesTableBody">
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
					<button id="previousDevicesButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
					<button id="nextDevicesButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
				</div>
				<p class="mt-4 text-sm font-semibold">Showing <span id="devicesRange"></span> of <span id="devicesTotal"></span></p>
			</div>
		</div>
	</div>
	<!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxDevices.js"></script>

<!-- Footer -->
<?php 
include_once('./footer.php');
?>

