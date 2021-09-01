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
			<div class="flex flex-col space-y-8 h-full pt-4 px-4 shadow-md">
				<input id="pageSearchBar" type="text" class="h-10 w-full outline-none focus:outline-none bg-white focus:bg-white rounded-lg text-gray-800 font-medium flex justify-center items-center text-sm space-x-1 mx-auto px-4 border border-gray-300 transition-all" placeholder="Filter devices...">

				<!-- Php code for roles filter -->
				<?php
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					echo '
					<!-- Single filter -->
					<div class="flex flex-col px-2">
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
				<div id="productsFilter" class="flex flex-col px-2">
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
	<div class="flex-auto flex flex-col">
		<!-- Pagination -->
		<div class="h-14 bg-gray-300 border-b w-full grid-cols-3 px-6 flex justify-center items-center space-x-4">
			<button id="previousDevicesButton" class="focus:outline-none text-gray-600 transition hover:bg-gray-200 border border-gray-400 h-8 w-14 flex justify-center items-center cursor-pointer disabled:opacity-75 disabled:text-bluegray-400 disabled:bg-gray-300 disabled:cursor-default"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></button>

			<p class="text-xs uppercase font-medium text-gray-600 whitespace-nowrap">Showing <span class="font-extrabold" id="devicesRange"></span> of <span id="devicesTotal"></p>

			<button id="nextDevicesButton" class="focus:outline-none text-gray-600 transition hover:bg-gray-200 border border-gray-400 h-8 w-14 flex justify-center items-center cursor-pointer disabled:opacity-75 disabled:text-bluegray-400 disabled:bg-gray-300 disabled:cursor-default"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></button>
		</div>
		
		<div id="devicesTableBody" class="flex-auto p-4 flex flex-col space-y-4 lg:space-y-4">

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

