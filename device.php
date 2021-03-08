<?php
session_start();
$_SESSION['activeUrl'] = 'devices.php';
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
				<div class="border-b text-xs md:text-sm uppercase font-bold text-bluegray-500 pb-2">
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
	<div class="mt-4 md:m-4 lg:m-8">
		<!-- Column structure  -->
		<div class="grid grid-cols-1 gap-y-4 gap-x-0 md:grid-cols-2 md:gap-8 lg:grid-cols-3 lg:gap-8">

			<!-- Channel visual -->
			<div class="bg-white border border-gray-200 h-full">
				<div class="flex h-full py-8">
					<div class="flex-1 flex flex-col justify-center items-center px-4 space-y-4">
						<!-- Probe status -->
						<div class="flex-1 flex flex-col justify-center items-center space-y-1">
							<p class="text-xs uppercase text-bluegray-400">Probe status</p>
							<div class="flex" id="probeStatus">
								
							</div>
							<span class="text-xs text-bluegray-400 uppercase" id="probeDate"></span>
						</div>

						<!-- Separation border -->
						<div class="border-t w-full"></div>

						<!-- Current temperature -->
						<div class="flex-1 flex flex-col justify-center items-center space-y-1">
							<p class="text-xs uppercase text-bluegray-400">Current temperature</p>
							<div class="flex">
								<span class="text-3xl lg:text-5xl font-semibold text-bluegray-800" id="apiTemp_temp"></span><span class="text-bluegray-800 text-xl">&#176;C</span>
							</div>
							<span class="text-xs text-bluegray-400 uppercase" id="apiTemp_location"></span>
						</div>

					</div>
					<!-- Latest measurement -->
					<div id="latestTempDisplay" class="flex-1 h-full flex flex-col justify-center items-center py-4 md:py-0 border-l border-gray-200 space-y-2 px-4 space-y-4">
						<!-- Filled with js -->
					</div>
				</div>
			</div>
			<!-- End of channel visual -->

			<!-- Start of chart -->
			<div class="p-4 border border-gray-200 col-span-1 md:col-span-2 lg:col-span-1 flex justify-center items-center bg-white">
				<canvas id="canvas">
					<!-- Filled with js -->
				</canvas>
			</div>
			<!-- End of chart -->

			<!-- Current temp & latest reading -->
			<div class="bg-white border border-gray-200">
				<div class="flex h-full py-8">
					<!-- Current temperature -->
					<div class="flex-1 flex flex-col justify-center items-center space-y-2">
						<p class="text-xs uppercase text-bluegray-400">Current temperature</p>
						<div class="flex">
							<span class="text-4xl lg:text-6xl font-semibold text-bluegray-800" id="apiTemp_temp"></span><span class="text-bluegray-800 text-xl">&#176;C</span>
						</div>
						<span class="text-xs text-bluegray-400 uppercase" id="apiTemp_location"></span>
					</div>

					<!-- Latest reading -->
					<div class="flex-1 flex flex-col justify-center items-center space-y-2 border-l border-gray-200">
						<p class="text-xs uppercase text-bluegray-400">Latest reading</p>
						<div class="flex">
							<span class="text-4xl lg:text-6xl font-semibold text-bluegray-800" id="latestReading_measurement"></span><span class="text-bluegray-800 text-xl" id="latestReading_unit">
						</div>
						<span class="text-xs text-bluegray-400 uppercase" id="latestReading_time"></span>
					</div>
				</div>

			</div>
			<!-- End of temp and latest reading -->

			<!-- Latest alarm messages -->
			<div class="">
				<div class="flex overflow-x-auto inline-block min-w-full border border-b-0 border-gray-200">
					<table class="table-fixed min-w-full">
						<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
							<tr>
								<th class="text-left w-2/12 py-2 px-4 font-semibold">Channel</th>
								<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-semibold">Alarm</th>
								<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-semibold">Timestamp</th>
							</tr>
						</thead>
						<tbody id="table_alarms">
							<!-- This area gets filled via PHP -->


						</tbody>
					</table>
				</div>
				<div id="loadingOverlay_alarms" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
					<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
					<p>Loading...</p>
				</div>

				<div class="flex flex-col items-center justify-center py-4 border border-t-0 border-gray-200">
					<div class="flex">
						<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
						<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
					</div>
					<p class="mt-4 text-xs font-semibold">Showing <span id="range_alarms"></span> of <span id="total_alarms"></span></p>
				</div>
			</div>
			<!-- End of latest messages -->

			<!-- Map -->
			<div class="h-48 overflow-hidden border border-gray-200 hidden lg:block">
				<div id="map" class="w-full h-full"></div>
			</div>
			<!-- End of map -->

			<!-- Measurements table -->
			<div class="">
				<div class="flex overflow-x-auto inline-block min-w-full border border-b-0 border-gray-200">
					<table class="table-fixed min-w-full">
						<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
							<tr>
								<th class="text-left w-4/12 py-2 px-4 font-semibold">Channel</th>
								<th class="text-center w-2/12 lg:w-4/12 py-2 px-4 font-semibold">Reading</th>
								<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-semibold">Timestamp</th>
							</tr>
						</thead>
						<tbody id="table_measurements">
							<!-- This area gets filled via PHP -->


						</tbody>
					</table>
				</div>
				<div id="loadingOverlay_measurements" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
					<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
					<p>Loading...</p>
				</div>

				<div class="flex flex-col items-center justify-center py-4 border border-t-0 border-gray-200">
					<div class="flex">
						<button id="previous_measurements" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
						<button id="next_measurements" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
					</div>
					<p class="mt-4 text-xs font-semibold">Showing <span id="range_measurements"></span> of <span id="total_measurements"></span></p>
				</div>
			</div>
			<!-- End of measurements table -->

			<!-- Custom alarms -->
			<div class="md:col-span-2 border border-gray-200 bg-white" style="min-height: 260px">
				<div class="flex h-full">
					<!-- Col #1: Side nav -->
					<div id="alarmsNav" class="flex-none flex flex-col w-14 md:w-20 bg-bluegray-50">

						<div class="flex flex-col justify-center items-center py-2 border-gray-200">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
							<p class="text-xs uppercase font-medium text-center mt-1">Alarms</p>
						</div>

						<div id="newAlarm" class="flex flex-col justify-center items-center py-2 border-gray-200">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
							<p class="text-xs uppercase font-medium text-center mt-1">New</p>
						</div>

						<div class="flex-auto border-bluegray-200">
						<!-- Fill -->
						</div>
					</div>

					<!-- Alarm sections -->
					<div id="alarmSections" class="flex-1 flex">
						<!-- Section #1: ALARMS -->
						<div class="flex-auto flex">
							<!-- Col #1 -->
							<div id="customAlarms" class="flex-1 bg-bluegray-50 pt-4 px-4 space-y-4">
								
							</div>
							
							<!-- Col #2 -->
							<div class="flex-1 bg-white border-l border-gray-200 hidden">
							s
							</div>
						</div>
						<!-- End of section #1 -->

						<!-- Section #2: New Alarm -->
						<div class="flex-1 grid grid-cols-2">
							<!-- Col #1 -->
							<div class="bg-bluegray-50 px-2 lg:px-4 border-r border-gray-200">
								<!-- Form -->
								<form id="alarmForm" method="post" autocomplete="off" class="flex flex-col max-w-4xl">
									<!-- Row -->
									<p class="text-xs uppercase font-semibold text-bluegray-600 py-1 text-center border-b">New Alarm</p>
									<!-- End of row -->

									<!-- Row -->
									<div class="flex my-1">
										<div class="flex flex-col flex-1">
											<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Device<span class="text-red-500">*</span></p>
											<input type="text" value="Device Name" disabled id="deviceName" required class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-600 bg-bluegray-100 w-full transition-all focus:border-gray-400 rounded whitespace-nowrap overflow-ellipsis overflow-hidden">
										</div>
									</div>
									<!-- End of Row -->

									<!-- Row -->
									<div class="flex my-1">
										<div class="flex flex-col flex-1">
											<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Channel<span class="text-red-500">*</span></p>
											<select id="channelSelect" class="outline-none border border-gray-200 h-12 px-4 text-sm capitalize font-semibold text-bluegray-800 bg-bluegray-100 transition-all hover:border-gray-400 focus:border-gray-400 hover:text-bluegray-900 rounded">
												<!-- Filled via js -->
											</select>
										</div>
									</div>
									<!-- End of Row -->

									<!-- Row -->
									<div class="flex flex-col lg:flex-row">
										<div class="flex flex-col flex-1 my-1">
											<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Operator<span class="text-red-500">*</span></p>
											<select id="operatorSelect" class="outline-none border border-gray-200 h-12 px-4 text-sm capitalize font-semibold text-bluegray-800 bg-bluegray-100 transition-all hover:border-gray-400 focus:border-gray-400 hover:text-bluegray-900 rounded">
												<option data-id=">" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected>Greater than (>)</option>
												<option data-id=">=" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Greater than or equals (>=)</option>
												<option data-id="==" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Equals (==)</option>
												<option data-id="<" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Less than (<)</option>
												<option data-id="<=" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Less than or equals (<=)</option>
											</select>
										</div>
										<div class="flex flex-col flex-1 lg:ml-4 my-1">
											<p class="text-xs uppercase font-semibold text-bluegray-600 px-2 py-1">Value<span class="text-red-500">*</span></p>
											<div class="flex">
												<input type="text" id="alarmValue" placeholder="99"  required spellcheck="false" autocomplete="none" class="outline-none border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 w-full transition-all focus:border-gray-400 rounded">
												<div id="unitField" class="flex justify-center items-center border border-gray-200 h-12 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 rounded ml-2">
													<p></p>
												</div>
											</div>
										</div>
									</div>
									<!-- End of Row -->

									<!-- Row -->
									<p class="text-xs uppercase font-semibold text-bluegray-600 py-1 text-center border-b mt-2">Recipients</p>
									<!-- End of row -->

									<!-- Row -->
									<div id="selectedRecipients" class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2">
										<!-- Filled with js -->

									</div>
									<!-- End of row -->

									<!-- Row -->
									<div class="flex justify-center items-center my-8">
										<button type="submit" id="submit" name="submit" title="Create" class="focus:outline-none flex items-center justify-center border border-transparent bg-lightblue-500 transition-all hover:bg-lightblue-600 text-lightblue-100 hover:border-lightblue-500 hover:text-white space-x-2 font-semibold uppercase text-sm h-10 w-40 rounded shadow">
											<p>Create</p>
											<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></i>
										</button>
									</div>
									<!-- End of row -->
								</form>

							</div>

							<!-- Col #2 -->
							<div id="usersDiv" class="bg-white px-2 lg:px-4 overflow-y-auto">
								<p class="text-xs uppercase font-semibold text-bluegray-600 py-1 text-center border-b mb-2">Users</p>
								<!-- Search bar -->
								<div class="flex justify-center items-center h-12 mb-4">
									<div class="hidden flex-none sm:flex justify-center items-center h-full bg-bluegray-100 rounded-l border border-r-0 border-gray-200">
										<svg class="w-4 h-4 mx-2 text-bluegray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
									</div>
									<input type="text" id="userSearchBar" placeholder="Search..." spellcheck="false" autocomplete="none" class="outline-none flex-auto w-full h-full px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 transition-all focus:bg-bluegray-50 rounded-r border border-gray-200">
								</div>
								<!-- List of users -->
								<div id="usersList" class="grid cols-1 gap-2 mb-4">
									<!-- Filled with Js -->
								</div>
							</div>
						</div>
						<!-- End of section #2 -->

					</div>
					<!-- End of alarm sections -->


				</div>
			</div>
			<!-- End of custom alarms -->
		</div>
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

