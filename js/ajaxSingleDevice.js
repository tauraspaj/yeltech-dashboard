$(document).ready(function () {
	
	// ! Find deviceId from URL
    var deviceId = window.location.href.split('?id=')[1];
    if (deviceId == null) {
        document.location.href = 'devices.php';
    }

	// ! List of defined components
	/*
		* rtmu_dashboard

		* deviceData
		* alarms
		* recipients
		* map
		* log
	*/

	// ! Function to show appropriate component
	function displayComponent(component) {
		switch (component) {
			case 'rtmu_dashboard':
				display_rtmu_dashboard();
				break;
			case 'deviceData':
				display_deviceData();
				break;
			case 'alarms':
				display_alarms();
				break;
			case 'recipients':
				display_recipients();
				break;
			case 'map':
				display_map();
				break;
			case 'log':
				display_log();
				break;
		}
	}

	// ! Data about each device
	var subNavbar = {
		rtmu: [
			{ title: 'Alarms', component:'alarms' },
			{ title: 'Dashboard', component:'rtmu_dashboard' },
			{ title: 'Device Info', component:'deviceData' },
			{ title: 'Recipients', component:'recipients' },
			{ title: 'Log', component:'log' }
		],
		ewb: [
			{ title: 'EWB Dashboard', component:'ewb_dashboard' },
			{ title: 'Device Info', component:'deviceData' },
			{ title: 'Alarms', component:'alarms' },
			{ title: 'Recipients', component:'recipients' },
			{ title: 'Log', component:'log' }
		]
	}

	// ! Check device's product type
	function findProductType() {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
                deviceId: deviceId,
				function: 'loadProductData'
			},
			success: function (data) {
				data = JSON.parse(data);
				generateSubNavigation(data);
			}
		})
	}

	// ! Run this function on load to find product and generate sub navbar
	findProductType();

	// ! Generate nav bar according to product name
	function generateSubNavigation(productData) {
		var output = '';
		for (i = 0; i < subNavbar[ productData.productName.toLowerCase() ].length; i++) {
			output += `
			<div id="component" data-component="` + subNavbar[ productData.productName.toLowerCase() ][i].component + `" class="flex items-center cursor-pointer space-x-2 text-gray-500 hover:text-gray-900">
				<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.243 3.03a1 1 0 01.727 1.213L9.53 6h2.94l.56-2.243a1 1 0 111.94.486L14.53 6H17a1 1 0 110 2h-2.97l-1 4H15a1 1 0 110 2h-2.47l-.56 2.242a1 1 0 11-1.94-.485L10.47 14H7.53l-.56 2.242a1 1 0 11-1.94-.485L5.47 14H3a1 1 0 110-2h2.97l1-4H5a1 1 0 110-2h2.47l.56-2.243a1 1 0 011.213-.727zM9.03 8l-1 4h2.938l1-4H9.031z" clip-rule="evenodd"></path></svg>
				<p class="text-xs font-semibold uppercase whitespace-nowrap">` + subNavbar[ productData.productName.toLowerCase() ][i].title + `</p>
			</div>
			`;
		}
		$('#subPageNav').html(output);
		displayComponent(subNavbar[ productData.productName.toLowerCase() ][0].component)
	}

	// ! Display component on click
	$('#subPageNav').delegate( '#component', 'click', function() {
		var component = $(this).attr('data-component');
		displayComponent(component);
	})

	// ! ---------------------------------
	// ! ***** Building components *****  |
	// ! ---------------------------------
	siteContent = $('#siteContent');

	// ! SHOW COMPONENT: Display rtmu dashboard
	function display_rtmu_dashboard() {
		// * Put all cards together and generate final output
		output = `
		<div class="grid grid-cols-2 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-4 lg:gap-6">
			<!-- Card -->
			<div id="statusAndProbeCards"></div>
			<!-- End of card -->

			<!-- Card -->
			<div id="latestMeasurementsCard"></div>
			<!-- End of card -->
			
			<!-- Card -->
			<div id="latestTempCard"></div>
			<!-- End of card -->

			<!-- Card -->
			<div class="col-span-1 flex flex-col justify-center items-center bg-white shadow-md rounded-xl text-center space-y-2 relative py-4">
				<div class="hidden md:block lg:hidden xl:block absolute top-2 left-2 bg-purple-100 text-purple-500 rounded-full p-2">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
				</div>
				<div class="flex flex-row justify-center items-center">
					<p class="uppercase text-xs font-medium text-purple-500 bg-purple-50 rounded-xl px-2 py-1">Total alarms sent</p>
				</div>
				<p class="font-medium text-gray-800"><span class="text-5xl tracking-tighter">54</span></p>
				<p class="text-xs text-gray-400">Latest: 2020-20-20</p>
			</div>
			<!-- End of card -->
			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 shadow-lg bg-gray-50 rounded-xl flex flex-col">
				<!-- Filters -->
				<div class="flex-none flex justify-between items-center h-12 bg-white rounded-t-xl border-b">
					<div class="flex items-center">
						<div class="hidden md:block bg-blue-100 text-blue-500 rounded-full p-2 ml-4 mr-2 lg:mr-4">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
						</div>
						<div id="dateSelectors" class="flex justify-center items-center space-x-2 ml-2">
							<div data-id="3hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800">3hr</div>
							<div data-id="12hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800">12hr</div>
							<div data-id="1d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800">1d</div>
							<div data-id="7d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800">7d</div>
							<div data-id="30d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800">30d</div>
							<div data-id="CAL" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
							</div>
						</div>
					</div>
				</div>

				<!-- Chart -->
				<div class="flex-auto p-2 lg:p-4 flex justify-center items-center">
					<canvas id="canvas">
						<!-- Filled with js -->
					</canvas>
				</div>
			</div>
			<!-- End of card-->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 shadow-lg bg-gray-50 rounded-xl flex flex-col">
				<!-- Filters -->
				<div class="flex-none flex items-center h-12 bg-white rounded-t-xl border-b">
					<div class="absolute hidden md:block bg-purple-100 text-purple-500 rounded-full p-2 mx-4">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="mx-auto">
						<div class="text-purple-800 font-medium text-sm bg-purple-100 rounded-lg py-1 px-4">
							Alarms	
						</div>
					</div>
				</div>

				<!-- Table -->
				<div class="flex-auto py-2 px-4">
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-500">Channel</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500">Alarm</th>
									<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500">Timestamp</th>
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

					<div class="flex flex-col items-center justify-center py-4">
						<div class="flex">
							<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
						</div>
						<p class="mt-4 text-xs font-semibold">Showing <span id="range_alarms"></span> of <span id="total_alarms"></span></p>
					</div>
				</div>
			</div>
			<!-- End of card-->
		</div>
		`;
		// * Update site content once its generated
		siteContent.html(output);

		// * Status, probe and latest readings card
		rtmu_getLatestReadings().then( function(readingsData) {
			// * statusAndProbeCards
			//#region
			// Process device status
			if (readingsData.deviceStatus == 1) {
				var deviceStatus = 'ON';
				var statusColor = 'green';
				var statusIcon = '<svg class="w-6 h-6 text-'+statusColor+'-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path></svg>'
			} else {
				var deviceStatus = 'OFF';
				var statusColor = 'red';
				var statusIcon = '<svg class="w-6 h-6 text-'+statusColor+'-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3.707 2.293a1 1 0 00-1.414 1.414l6.921 6.922c.05.062.105.118.168.167l6.91 6.911a1 1 0 001.415-1.414l-.675-.675a9.001 9.001 0 00-.668-11.982A1 1 0 1014.95 5.05a7.002 7.002 0 01.657 9.143l-1.435-1.435a5.002 5.002 0 00-.636-6.294A1 1 0 0012.12 7.88c.924.923 1.12 2.3.587 3.415l-1.992-1.992a.922.922 0 00-.018-.018l-6.99-6.991zM3.238 8.187a1 1 0 00-1.933-.516c-.8 3-.025 6.336 2.331 8.693a1 1 0 001.414-1.415 6.997 6.997 0 01-1.812-6.762zM7.4 11.5a1 1 0 10-1.73 1c.214.371.48.72.795 1.035a1 1 0 001.414-1.414c-.191-.191-.35-.4-.478-.622z"></path></svg>'
			}

			// Process probe status
			if (readingsData.probeStatus.smsAlarmHeader == 'PROBE ON TRACK') {
				var probeStatus = 'ON';
				var probeColor = 'green';
			} else {
				var probeStatus = 'OFF';
				var probeColor = 'red';
			}

			var statusAndProbeCards = `
				<div class="col-span-1 flex flex-col space-y-4">
					<!-- Device status -->
					<div class="bg-`+statusColor+`-50 shadow-md rounded-xl h-16 lg:h-20 flex justify-between items-center">
						<div class="flex-1 flex items-center space-x-2 pl-4">
							`+statusIcon+`
							<p class="uppercase text-xs lg:text-sm font-medium text-gray-700">Status</p>
						</div>
						<div class="flex-1 text-center">
							<p class="uppercase text-xs lg:text-sm font-medium text-`+statusColor+`-500">` + deviceStatus + `</p>
						</div>
					</div>

					<!-- Probe status -->
					<div class="bg-`+probeColor+`-50 shadow-md rounded-xl h-16 lg:h-20 flex justify-between items-center">
						<div class="flex-1 flex items-center space-x-2 pl-4">
							<svg class="w-6 h-6 text-`+probeColor+`-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>
							<p class="uppercase text-xs lg:text-sm font-medium text-gray-700">Probe</p>
						</div>
						<div class="flex-1 text-center">
							<p class="uppercase text-xs lg:text-sm font-medium text-`+probeColor+`-500">`+probeStatus+`</p>
						</div>
					</div>
				</div> 
			`
			
			// Insert card
			$('#statusAndProbeCards').html(statusAndProbeCards);
			//#endregion

			// * latestMeasurementsCard
			//#region
			if (readingsData.numberOfAI == 1) {
				var latestMeasurementsCard = `
					<div class="col-span-1 flex flex-col justify-center items-center bg-white shadow-md rounded-xl text-center space-y-2 relative h-36 lg:h-44">
						<div class="hidden md:block lg:hidden xl:block absolute top-2 left-2 bg-blue-100 text-blue-500 rounded-full p-2">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
							<svg class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
						</div>
						<div class="flex flex-row justify-center items-center">
							<p class="uppercase text-xs font-medium text-blue-500 bg-blue-50 rounded-xl px-2 py-1">`+readingsData.latestMeasurements[0].channelName+`</p>
						</div>
						<div class="space-y-2">
							<p class="font-medium text-gray-800"><span class="text-5xl tracking-tighter">`+readingsData.latestMeasurements[0].measurement+`</span> <span class="">&#176;C</span></p>
							<p class="text-xs text-gray-400">`+readingsData.latestMeasurements[0].measurementTime+`</p>
						</div>
					</div>
				`;
			}
			if (readingsData.numberOfAI == 2) {
				var latestMeasurementsCard = `
					<div id="chan1Div" class="col-span-1 flex flex-col justify-center items-center bg-white shadow-md rounded-xl text-center space-y-2 relative h-36 lg:h-44">
						<div class="hidden md:block lg:hidden xl:block absolute top-2 left-2 bg-blue-100 text-blue-500 rounded-full p-2">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
							<svg class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
						</div>
						<div class="flex flex-row justify-center items-center">
							<p id="chan1" class="uppercase text-xs font-medium text-blue-500 bg-blue-50 rounded-xl px-2 py-1 cursor-default">`+readingsData.latestMeasurements[1].channelName+`</p>
							<p id="chan2" class="uppercase text-xs text-gray-400 rounded-xl px-2 py-1 hover:text-black cursor-pointer">`+readingsData.latestMeasurements[0].channelName+`</p>
						</div>
						<div class="space-y-2">
							<p class="font-medium text-gray-800"><span class="text-5xl tracking-tighter">`+readingsData.latestMeasurements[1].measurement+`</span> <span class="">&#176;C</span></p>
							<p class="text-xs text-gray-400">`+readingsData.latestMeasurements[0].measurementTime+`</p>
						</div>
					</div>
					<div id="chan2Div" class="col-span-1 flex flex-col justify-center items-center bg-white shadow-md rounded-xl text-center space-y-2 relative h-36 lg:h-44">
						<div class="hidden md:block lg:hidden xl:block absolute top-2 left-2 bg-blue-100 text-blue-500 rounded-full p-2">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
							<svg class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
						</div>
						<div class="flex flex-row justify-center items-center">
							<p id="chan1" class="uppercase text-xs text-gray-400 rounded-xl px-2 py-1 hover:text-black cursor-pointer">`+readingsData.latestMeasurements[1].channelName+`</p>
							<p id="chan2" class="uppercase text-xs font-medium text-blue-500 bg-blue-50 rounded-xl px-2 py-1 cursor-default">`+readingsData.latestMeasurements[0].channelName+`</p>
						</div>
						<div class="space-y-2">
							<p class="font-medium text-gray-800"><span class="text-5xl tracking-tighter">`+readingsData.latestMeasurements[0].measurement+`</span> <span class="">&#176;C</span></p>
							<p class="text-xs text-gray-400">`+readingsData.latestMeasurements[0].measurementTime+`</p>
						</div>
					</div>
				`
			}
			$('#latestMeasurementsCard').html(latestMeasurementsCard);

			// Switching between channels functionality
			$('#chan2Div').hide();
			$('#chan1, #chan2').on('click', function() {
				var parentDiv = $('#'+$(this).attr('id')+'Div');
				if ( parentDiv.is(':hidden') ) {
					parentDiv.show();
					parentDiv.siblings('#chan1Div, #chan2Div').hide();
				}
			})
			//#endregion
		})

		// * Latest temp card
		getDeviceCoordinates().then( function(deviceCoordinates) {
			// * latestTempCard
			//#region 
			var latestTempCard = `
				<div class="col-span-1 flex flex-col justify-center items-center bg-white shadow-md rounded-xl text-center space-y-2 relative py-4 h-36 lg:h-44">
					<div class="hidden md:block lg:hidden xl:block absolute top-2 left-2 bg-yellow-100 text-yellow-500 rounded-full p-2">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="flex flex-row justify-center items-center">
						<p class="uppercase text-xs font-medium text-yellow-500 bg-yellow-50 rounded-xl px-2 py-1">Current temp</p>
					</div>
					<p class="font-medium text-gray-800"><span id="api_temp" class="text-5xl tracking-tighter"></span> <span class="">&#176;C</span></p>
					<p id="api_loc" class="text-xs text-gray-400"></p>
				</div>
				`;
			// Update values with latest temp
			$('#latestTempCard').html(latestTempCard);
			getLatestTemp(deviceCoordinates, 'api_temp', 'api_loc');
			//#endregion
		})

		// * Load alarms card
		//#region 
		var alarmPageNumber = 1;
		var alarmsPerPage = 5;
		getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms');

		// Alarm paging
		$('#next_alarms').on('click', function () {
			alarmPageNumber += 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms');
		})
		$('#previous_alarms').on('click', function () {
			alarmPageNumber -= 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms');
		})
		//#endregion

		// * Load chart card
		//#region 
		var ctx = $('#canvas')[0].getContext('2d');
		var chart = new Chart(ctx, {
			type: 'line',
			data: { datasets: [] },
			options: {
				scales: {
					xAxes: [{
						type: 'time',
						time: {
							unit: 'hour',
							stepSize: 0.5,
							tooltipFormat: 'HH:mm DD-MM-YYYY',
						},
						ticks: {
							autoSkip: true,
							maxTicksLimit: 10,
							major: {
								enabled: true,
								fontStyle: 'bold',
								fontSize: 14
							},
						},
						scaleLabel: {
							display: true,
							labelString: 'Time of alarm'
						},
						gridLines: {
							drawOnChartArea: false
						}
					}],
					yAxes: [{
						scaleLabel: {
							display: true,
							labelString: 'Temperature (°C)'
						},
						ticks: {
							autoSkip: true,
							maxTicksLimit: 6
						}
					}]
				}
			}
		});
		getDatasets('3hr','NOW').then( function (data) {
			drawChart(chart, data);
		})

		var activeClass = 'text-gray-800 bg-gray-100';
		$('#dateSelectors').children().first().addClass(activeClass);
		$('#dateSelectors').children().on('click', function() {
			$('#dateSelectors').children().removeClass(activeClass);
			$(this).addClass(activeClass);
			var dataid = $(this).attr('data-id');
			if (dataid != 'CAL') {
				getDatasets(dataid,'NOW').then( function (data) {
					drawChart(chart, data);
				})
			}
		})
		//#endregion
		
	}		
	
	// ! SHOW COMPONENT: Display device data
	function display_deviceData() {
		siteContent.html('DeviceData');
	}

	// ! SHOW COMPONENT: Display alarms
	function display_alarms() {
		// * Put all cards together and generate final output
		output = `
		<div class="grid grid-cols-2 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-4 lg:gap-6">
			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 shadow-lg bg-gray-50 rounded-xl flex flex-col">
				<!-- Filters -->
				<div class="flex-none flex items-center h-12 bg-white rounded-t-xl border-b">
					<div class="absolute hidden md:block bg-purple-100 text-purple-500 rounded-full p-2 mx-4">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="mx-auto">
						<div class="text-purple-800 font-medium text-sm bg-purple-100 rounded-lg py-1 px-4">
							Manage Alarms	
						</div>
					</div>
				</div>

				<!-- Table -->
				<div class="flex-auto py-2 px-4">
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-500">Channel</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500">Alarm</th>
									<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500">Timestamp</th>
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

					<div class="flex flex-col items-center justify-center py-4">
						<div class="flex">
							<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
						</div>
						<p class="mt-4 text-xs font-semibold">Showing <span id="range_alarms"></span> of <span id="total_alarms"></span></p>
					</div>
				</div>
			</div>
			<!-- End of card-->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 shadow-lg bg-gray-50 rounded-xl flex flex-col">
				<!-- Filters -->
				<div class="flex-none flex items-center h-12 bg-white rounded-t-xl border-b">
					<div class="absolute hidden md:block bg-purple-100 text-purple-500 rounded-full p-2 mx-4">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="mx-auto">
						<div class="text-purple-800 font-medium text-sm bg-purple-100 rounded-lg py-1 px-4">
							Alarms	
						</div>
					</div>
				</div>

				<!-- Table -->
				<div class="flex-auto py-2 px-4">
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-500">Channel</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500">Alarm</th>
									<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500">Timestamp</th>
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

					<div class="flex flex-col items-center justify-center py-4">
						<div class="flex">
							<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
						</div>
						<p class="mt-4 text-xs font-semibold">Showing <span id="range_alarms"></span> of <span id="total_alarms"></span></p>
					</div>
				</div>
			</div>
			<!-- End of card-->			
		</div>
		`;
		
		// * Update site content once its generated
		siteContent.html(output);
	}

	// ! SHOW COMPONENT: Display recipients
	function display_recipients() {
		siteContent.html('Recipients');
	}

	// ! SHOW COMPONENT: Display log
	function display_log() {
		siteContent.html('Log');
	}
	
	// ! Hide loaders on load
    $('#loadingOverlay_measurements, #loadingOverlay_alarms').hide();



	// ! -----------------------
	// ! ***** Functions *****  |
	// ! -----------------------
	function pageControl(total, returned, perPage, pageNumber, id) {
		$('#total_'+id).html(total);

		var offset = 0;
		if (pageNumber == 1) {
			offset = 0
		} else {
			offset = (pageNumber - 1) * perPage;
		}

		if (returned < perPage) {
			$('#range_'+id).html(eval(offset + 1) + '-' + total);
		} else {
			$('#range_'+id).html(eval(offset + 1) + '-' + eval(offset + perPage));
		}

		if (total == 0) {
			$('#range_'+id).html(total);
		}

		// Previous button should be disabled on page 1
		if (pageNumber <= 1) {
			$("#previous_"+id).prop('disabled', true);
		} else {
			$("#previous_"+id).prop('disabled', false);
		}

		// Next button should be disabled when all the rows have been returned
		if (total <= pageNumber * perPage) {
			$("#next_"+id).prop('disabled', true);
		} else {
			$("#next_"+id).prop('disabled', false);
		}
	}

	function rtmu_getLatestReadings() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'rtmu_getLatestReadings'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}


	function getDatasets(dateFrom, dateTo) {
		if (dateTo.toUpperCase() == 'NOW') {
			var now = new Date();
			var formattedNow = now.getFullYear() + "-" + (now.getMonth()+1) +  "-" + now.getDate() + " " + now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds();
		}

		dateFrom = dateFrom.toString();
		var milis = 0;
		switch (dateFrom){
			case '3hr':
				milis = 3*60*60*1000;
				break;
			case '12hr':
				milis = 12*60*60*1000;
				break;
			case '1d':
				milis = 24*60*60*1000;
				break;
			case '7d':
				milis = 7*24*60*60*1000;
				break;
			case '30d':
				milis = 30*24*60*60*1000;
				break;
			default:
		}

		var dateFrom = new Date(Date.now() - milis);
		var formattedDateFrom = dateFrom.getFullYear() + "-" + (dateFrom.getMonth()+1) +  "-" + dateFrom.getDate() + " " + dateFrom.getHours() + ":" + dateFrom.getMinutes() + ":" + dateFrom.getSeconds();

		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					dateTo: formattedNow,
					dateFrom: formattedDateFrom,
					function: 'getDatasets'
				},
				success: function (data) {
					data = JSON.parse(data);
					// console.log(data);
					resolve(data);
				}
			})
		})
	}
	
	function drawChart(chart, data){
		colourPalette = {
			backgroundColors: ['rgba(14, 165, 233, 0.6)', 'rgba(239, 68, 68, 0.6)', 'rgba(34, 197, 94, 0.6)', 'rgba(245, 158, 11, 0.6)'],
			borderColors: ['rgba(14, 165, 233, 0.3)', 'rgba(239, 68, 68, 0.3)', 'rgba(34, 197, 94, 0.3)', 'rgba(245, 158, 11, 0.3)'],
			hoverColors: ['rgba(14, 165, 233, 0.9)', 'rgba(239, 68, 68, 0.9)', 'rgba(34, 197, 94, 0.9)', 'rgba(245, 158, 11, 0.9)']
		}
		// Clear current dataset
		chart.data.datasets = [];
		
		// Channel data contains 2 objects. Object 1 is name and id, object 2 is measurements
		for (i=0; i < data.length; i++) {
			var channelName = data[i].channelName;
			var measurements = data[i].data;
			// Create each channel as a dataset and add its data
			var newDataset = {
				label: channelName,
				data: [],
				fill: false,
				borderWidth: 2,
				pointHoverRadius: 5,
				pointHitRadius: 7,
				backgroundColor: colourPalette.backgroundColors[i],
				borderColor: colourPalette.borderColors[i],
				hoverBorderColor: colourPalette.hoverColors[i]
			};
			
			for (j = 0; j < measurements.length; j++) {
				newDataset.data.push({
					x:measurements[j].measurementTime, 
					y:measurements[j].measurement
				});
			}
			chart.data.datasets.push(newDataset);
			chart.update();
		}
	}

	function getAlarms(perPage, pageNumber, displayTableId) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				alarmsPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				deviceId: deviceId,
				function: 'loadTable_alarms'
			},
			beforeSend: function () {
				$('#loadingOverlay_alarms').show();
			},
			success: function (data) {
				$('#loadingOverlay_alarms').hide();
				var alarms = JSON.parse(data);
				console.log(alarms);

				totalCount = alarms[alarms.length - 1]['totalRows'];
				returnedCount = alarms.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber, 'alarms');

				var outputTable = '';
				for (i = 0; i < alarms.length - 1; i++) {
					var alternatingBg = '';
					if (i%2 == 0) {
						alternatingBg = 'bg-gray-100';
					} else {
						alternatingBg = 'bg-gray-50';
					}
					var dateDisplay = new Date( alarms[i].smsAlarmTime );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					var alarmDisplay = '-';
					if (alarms[i].smsAlarmHeader != null) {
						alarmDisplay = alarms[i].smsAlarmHeader;
						if (alarms[i].smsAlarmReading != null) {
							alarmDisplay += ' ' + alarms[i].smsAlarmReading;
						}
					}

					outputTable += `
					<tr class="border-b border-gray-200 h-10 `+ alternatingBg +`">
						<td class="text-left py-2 px-4 text-xs text-gray-700 font-medium">`+ alarms[i].channelName + `</td>
						<td class="text-center py-2 px-4 text-xs text-gray-700 whitespace-nowrap">`+ alarmDisplay + `</td>
						<td class="text-center py-2 px-4 text-xs text-gray-700 whitespace-nowrap">`+ dateDisplay + `</td>
					</tr>
					`;
				}
				$('#'+displayTableId).html(outputTable);
			}
		})
	}

	function getDeviceCoordinates() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getDeviceCoordinates'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function getLatestTemp(deviceCoordinates, tempSpan, locationSpan) {
		if (deviceCoordinates != 'Error') {
			// If we find coordinates...
			fetch('//api.openweathermap.org/data/2.5/weather?lat='+deviceCoordinates.latitude+'&lon='+deviceCoordinates.longitude+'&units=metric&APPID=f37101538ad0f765c18ce4538d42de2e')
			.then(response => response.json())
			.then(data => {
				var loc = data['name'] + ', ' + data['sys']['country'];
				var temperature = Math.round( data['main']['temp'] * 10 ) / 10;
				$('#'+locationSpan).html(loc);
				$('#'+tempSpan).html(temperature);
			})
		} else {
			// Show London if we don't find coordinates...
			fetch('//api.openweathermap.org/data/2.5/weather?q=London,uk&units=metric&APPID=f37101538ad0f765c18ce4538d42de2e')
			.then(response => response.json())
			.then(data => {
				var loc = data['name'];
				var temperature = Math.round( data['main']['temp'] * 10 ) / 10;
				$('#'+locationSpan).html(loc);
				$('#'+tempSpan).html(temperature);
			})
		}
	}

	// ------------------
	// Chart setup 		|
	// ------------------
	// var timeFormat = 'YYYY/MM/DD HH:mm';

    // var config = {
    //     type: 'line',
	// 	data: {
    //         datasets: []
	// 	},
	// 	options: {
	// 		scales:     {
	// 			xAxes: [{
	// 				type: "time",
	// 				time: {
	// 					parser: timeFormat,
	// 					unit: 'hour',
	// 					tooltipFormat: 'll HH:mm'
	// 				},
	// 				scaleLabel: {
	// 					display: true,
	// 					labelString: 'Time'
	// 				}
	// 			}],
	// 			yAxes: [{
	// 				scaleLabel: {
	// 					display: true,
	// 					labelString: 'Reading'
	// 				},
	// 			}]
	// 		}
	// 	}
    // };

	// var ctx = $('#canvas')[0].getContext('2d');
	// var chart1 = new Chart(ctx, config);

	// function generateChart(chart, data){
	// 	// Channel data contains 2 objects. Object 1 is name and id, object 2 is measurements
	// 	for (i=0; i < data.length; i++) {
	// 		var channelName = data[i][0].channelName;
	// 		var measurements = data[i][1];
	// 		// Create each channel as a dataset and add its data
	// 		var newDataset = {
	// 			label: channelName,
	// 			data: [],
	// 			fill: false,
	// 			borderWidth: 2,
	// 			pointHoverRadius: 5,
	// 			pointHitRadius: 7,
	// 			backgroundColor: colourPalette.backgroundColors[i],
	// 			borderColor: colourPalette.borderColors[i],
	// 			hoverBorderColor: colourPalette.hoverColors[i]
	// 		};

	// 		for (j = 0; j < measurements.length; j++) {
	// 			newDataset.data.push({
	// 				x:measurements[j].measurementTime, 
	// 				y:measurements[j].measurement
	// 			});
	// 		}
	// 		config.data.datasets.push(newDataset);
	// 		chart.update();
	// 	}
	// }

	// colourPalette = {
	// 	backgroundColors: ['rgba(14, 165, 233, 0.6)', 'rgba(239, 68, 68, 0.6)', 'rgba(34, 197, 94, 0.6)', 'rgba(245, 158, 11, 0.6)'],
	// 	borderColors: ['rgba(14, 165, 233, 0.3)', 'rgba(239, 68, 68, 0.3)', 'rgba(34, 197, 94, 0.3)', 'rgba(245, 158, 11, 0.3)'],
	// 	hoverColors: ['rgba(14, 165, 233, 0.9)', 'rgba(239, 68, 68, 0.9)', 'rgba(34, 197, 94, 0.9)', 'rgba(245, 158, 11, 0.9)']
	// }

	// $.ajax({
	// 	url: './includes/sqlSingleDevice.php',
	// 	type: 'POST',
	// 	data: {
	// 		deviceId: deviceId,
	// 		function: 'loadChart'
	// 	},
	// 	success: function (data) {
	// 		// console.log(data);
	// 		var channelData = JSON.parse(data);
	// 		generateChart(chart1, channelData);
	// 	}
	// })
	
	// $.ajax({
	// 	url: './includes/sqlSingleDevice.php',
	// 	type: 'POST',
	// 	data: {
	// 		deviceId: deviceId,
	// 		function: 'loadMap'
	// 	},
	// 	success: function (data) {
	// 		// console.log(data);
	// 		var coords = JSON.parse(data);
	// 		// console.log(coords[0].latitude)
	// 		if (coords != '') {
	// 			// Update current temperature
	// 			updateCurrentTemp(coords[0].latitude, coords[0].longitude)
				
	// 			// Setup map widget
	// 			setupMap(coords[0].longitude, coords[0].latitude)
	// 		} else {
	// 			// Update current temperature. Load London if coordinates don't exist
	// 			updateCurrentTemp('51.5074','0.1278')
				
	// 			// Setup map widget
	// 			setupMap('','')
	// 		}
	// 	}
	// })


	function showMeasurements(perPage, pageNumber) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				measurementsPerPage: perPage,
                offset: perPage * (pageNumber - 1),
                deviceId: deviceId,
				function: 'loadTable_measurements'
			},
			beforeSend: function () {
				$('#loadingOverlay_measurements').show();
			},
			success: function (data) {
                // console.log(data);
				$('#loadingOverlay_measurements').hide();
				var measurements = JSON.parse(data);
				
				totalCount = measurements[measurements.length - 1]['totalRows'];
				returnedCount = measurements.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber, 'measurements');

				var outputTable = '';
				for (i = 0; i < measurements.length - 1; i++) {
					var alternatingBg = '';
					if (i%2 == 0) {
						alternatingBg = 'bg-white'
					} else {
						alternatingBg = 'bg-bluegray-50'
					}
					var dateDisplay = new Date( measurements[i].measurementTime );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					outputTable += `
					<tr class="border-b border-gray-200 h-10 `+ alternatingBg +`">
						<td class="text-left py-2 px-4 text-xs text-bluegray-600">`+ measurements[i].channelName + `</td>
						<td class="text-center py-2 px-4 text-xs text-bluegray-600">`+ measurements[i].measurement + ` `+ measurements[i].unitName +`</td>
						<td class="text-center py-2 px-4 text-xs text-bluegray-600">`+ dateDisplay + `</td>
					</tr>
					`;
				}
				measurementsTable.html(outputTable);
			}
		})
	}

	// function generateChart(chart, data){
	// 	// Channel data contains 2 objects. Object 1 is name and id, object 2 is measurements
	// 	for (i=0; i < data.length; i++) {
	// 		var channelName = data[i][0].channelName;
	// 		var measurements = data[i][1];
	// 		// Create each channel as a dataset and add its data
	// 		var newDataset = {
	// 			label: channelName,
	// 			data: [],
	// 			fill: false,
	// 			borderWidth: 2,
	// 			pointHoverRadius: 5,
	// 			pointHitRadius: 7,
	// 			backgroundColor: colourPalette.backgroundColors[i],
	// 			borderColor: colourPalette.borderColors[i],
	// 			hoverBorderColor: colourPalette.hoverColors[i]
	// 		};

	// 		for (j = 0; j < measurements.length; j++) {
	// 			newDataset.data.push({
	// 				x:measurements[j].measurementTime, 
	// 				y:measurements[j].measurement
	// 			});
	// 		}
	// 		config.data.datasets.push(newDataset);
	// 		chart.update();
	// 	}
	// }

	function show2Alarms(perPage, pageNumber) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				measurementsPerPage: perPage,
                offset: perPage * (pageNumber - 1),
                deviceId: deviceId,
				function: 'loadTable_alarms'
			},
			beforeSend: function () {
				$('#loadingOverlay_alarms').show();
			},
			success: function (data) {
                // console.log(data);
				$('#loadingOverlay_alarms').hide();
				var alarms = JSON.parse(data);
				// console.log(alarms);

				totalCount = alarms[alarms.length - 1]['totalRows'];
				returnedCount = alarms.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber, 'alarms');

                // console.log(alarms);

				var outputTable = '';
				for (i = 0; i < alarms.length - 1; i++) {
					var alternatingBg = '';
					if (i%2 == 0) {
						alternatingBg = 'bg-white'
					} else {
						alternatingBg = 'bg-bluegray-50'
					}
					var dateDisplay = new Date( alarms[i].smsAlarmTime );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					var alarmDisplay = '-';
					if (alarms[i].smsAlarmHeader != null) {
						alarmDisplay = alarms[i].smsAlarmHeader;
						if (alarms[i].smsAlarmReading != null) {
							alarmDisplay += ' ' + alarms[i].smsAlarmReading;
						}
					}

					outputTable += `
					<tr class="border-b border-gray-200 h-10 `+ alternatingBg +`">
						<td class="text-left py-2 px-4 text-xs text-bluegray-600">`+ alarms[i].channelName + `</td>
						<td class="text-center py-2 px-4 text-xs text-bluegray-600">`+ alarmDisplay + `</td>
						<td class="text-center py-2 px-4 text-xs text-bluegray-600">`+ dateDisplay + `</td>
					</tr>
					`;
				}
				alarmsTable.html(outputTable);
			}
		})
	}

	// --------------------------
	// Map setup				|
	// --------------------------
	$.ajax({
		url: './includes/sqlSingleDevice.php',
		type: 'POST',
		data: {
			deviceId: deviceId,
			function: 'loadMap'
		},
		success: function (data) {
			// console.log(data);
			var coords = JSON.parse(data);
			// console.log(coords[0].latitude)
			if (coords != '') {
				// Update current temperature
				updateCurrentTemp(coords[0].latitude, coords[0].longitude)
				
				// Setup map widget
				setupMap(coords[0].longitude, coords[0].latitude)
			} else {
				// Update current temperature. Load London if coordinates don't exist
				updateCurrentTemp('51.5074','0.1278')
				
				// Setup map widget
				setupMap('','')
			}
		}
	})

	function setupMap(longitude, latitude) {
		mapboxgl.accessToken = 'pk.eyJ1IjoidGF1cmFzcCIsImEiOiJja2w2bzl6MmYyaXoyMm9xbzlld3dqaDJnIn0.dJGV_jlSPX-p51ZrQxaBew';
		var map = new mapboxgl.Map({
			container: 'map',
			style: 'mapbox://styles/mapbox/streets-v10',
			center: [longitude, latitude],
			zoom: 14
		});
	
		var marker = new mapboxgl.Marker()
			.setLngLat([longitude, latitude])
			.addTo(map);
	}

	// --------------------------
	// Latest reading setup		|
	// --------------------------
	function update2CurrentTemp(lat, lon) {
		// <span id="apiTemp_location">Location</span><span id="apiTemp_temp">Temperature</span><span id="apiTemp_time">Time</span>
		if (lat == '' || lon == '') {
			fetch('//api.openweathermap.org/data/2.5/weather?q=London,uk&units=metric&APPID=f37101538ad0f765c18ce4538d42de2e')
			.then(response => response.json())
			.then(data => {
				$('#apiTemp_location').html(data['name']);
				$('#apiTemp_temp').html( Math.round( data['main']['temp'] * 10 ) / 10);
			})

			.catch(err => console.log("Showing London"))
		} else {
			fetch('//api.openweathermap.org/data/2.5/weather?lat='+lat+'&lon='+lon+'&units=metric&APPID=f37101538ad0f765c18ce4538d42de2e')
				.then(response => response.json())
				.then(data => {
					$('#apiTemp_location').html(data['name'] + ', ' + data['sys']['country']);
					$('#apiTemp_temp').html(Math.round( data['main']['temp'] * 10 ) / 10);
				})

				.catch(err => console.log("Error"))
		}
	}

	// // ------------------
	// // Chart setup 		|
	// // ------------------
	// var timeFormat = 'YYYY/MM/DD HH:mm';

    // var config = {
    //     type: 'line',
	// 	data: {
    //         datasets: []
	// 	},
	// 	options: {
	// 		scales:     {
	// 			xAxes: [{
	// 				type: "time",
	// 				time: {
	// 					parser: timeFormat,
	// 					unit: 'hour',
	// 					tooltipFormat: 'll HH:mm'
	// 				},
	// 				scaleLabel: {
	// 					display: true,
	// 					labelString: 'Time'
	// 				}
	// 			}],
	// 			yAxes: [{
	// 				scaleLabel: {
	// 					display: true,
	// 					labelString: 'Reading'
	// 				},
	// 			}]
	// 		}
	// 	}
    // };

	// var ctx = $('#canvas')[0].getContext('2d');
	// var chart1 = new Chart(ctx, config);

	// colourPalette = {
	// 	backgroundColors: ['rgba(14, 165, 233, 0.6)', 'rgba(239, 68, 68, 0.6)', 'rgba(34, 197, 94, 0.6)', 'rgba(245, 158, 11, 0.6)'],
	// 	borderColors: ['rgba(14, 165, 233, 0.3)', 'rgba(239, 68, 68, 0.3)', 'rgba(34, 197, 94, 0.3)', 'rgba(245, 158, 11, 0.3)'],
	// 	hoverColors: ['rgba(14, 165, 233, 0.9)', 'rgba(239, 68, 68, 0.9)', 'rgba(34, 197, 94, 0.9)', 'rgba(245, 158, 11, 0.9)']
	// }

	// $.ajax({
	// 	url: './includes/sqlSingleDevice.php',
	// 	type: 'POST',
	// 	data: {
	// 		deviceId: deviceId,
	// 		function: 'loadChart'
	// 	},
	// 	success: function (data) {
	// 		// console.log(data);
	// 		var channelData = JSON.parse(data);
	// 		generateChart(chart1, channelData);
	// 	}
	// })

	// ---------------------------
	// Alarm messages setup		 |
	// ---------------------------
	// var alarmPageNumber = 1;
	// var alarmsPerPage = 5;
	// var alarmsTable = $('#table_alarms');
	// var totalCount = 0;

	// // Show devices on load
	// showAlarms(alarmsPerPage, alarmPageNumber);

	// $('#next_alarms').on('click', function () {
	// 	alarmPageNumber += 1;
	// 	showAlarms(alarmsPerPage, alarmPageNumber);
	// })

	// $('#previous_alarms').on('click', function () {
	// 	alarmPageNumber -= 1;
	// 	showAlarms(alarmsPerPage, alarmPageNumber);
	// })


	// --------------------------
	// Measurements table setup	|
	// --------------------------

	var measurementPageNumber = 1;
	var measurementsPerPage = 5;
	var measurementsTable = $('#table_measurements');
	var totalCount = 0;

	// Show devices on load
	showMeasurements(measurementsPerPage, measurementPageNumber);

	$('#next_measurements').on('click', function () {
		measurementPageNumber += 1;
		showMeasurements(measurementsPerPage, measurementPageNumber);
	})

	$('#previous_measurements').on('click', function () {
		measurementPageNumber -= 1;
		showMeasurements(measurementsPerPage, measurementPageNumber);
	})

	// --------------------------
	// Alarms management		|
	// --------------------------
	var kids = $('#alarmsNav').children();
	kids = kids.slice(0, kids.length-1);
	var fillerTab = $('#alarmsNav').children().last();

	// Tab behaviour
	var activeAlarmTab = 'bg-bluegray-50 cursor-default text-bluegray-800';
	var inactiveAlarmTab = 'bg-bluegray-100 border-r border-gray-200 cursor-pointer text-bluegray-500 hover:bg-bluegray-50 hover:text-bluegray-800';
	var topSiblingClass = 'border-b rounded-br-lg';
	var bottomSiblingClass = 'border-t rounded-tr-lg';

	fillerTab.addClass('bg-bluegray-100 border-r');
	kids.addClass(inactiveAlarmTab);

	// Select index 0 element on load
	kids.eq(0).removeClass(inactiveAlarmTab);
	kids.eq(0).addClass(activeAlarmTab);
	$('#alarmSections').children().hide();
	$('#alarmSections').children().eq(0).show();

	kids.eq(0).next().addClass(bottomSiblingClass);

	kids.on('click', function() {
		var index = $(this).index();

		// Only do something when a valid option is selected
		if ( index < kids.length && index >= 0) {
			// Display corresponding section
			$('#alarmSections').children().hide();
			$('#alarmSections').children().eq(index).show();

			if ($(this).attr('id') == 'newAlarm') {
				updateUsersHeight();
			}

			kids.removeClass(inactiveAlarmTab + ' ' + activeAlarmTab + ' ' + topSiblingClass + ' ' + bottomSiblingClass );
			fillerTab.removeClass(bottomSiblingClass);

			if (index == 0) {
				kids.addClass(inactiveAlarmTab);

				kids.eq(0).removeClass(inactiveAlarmTab);
				kids.eq(0).addClass(activeAlarmTab);
			
				kids.eq(0).next().addClass(bottomSiblingClass);
			} else if (index == kids.length-1) {
				kids.addClass(inactiveAlarmTab);

				kids.eq(index).removeClass(inactiveAlarmTab);
				kids.eq(index).addClass(activeAlarmTab);

				kids.eq(index).prev().addClass(topSiblingClass);
				fillerTab.addClass(bottomSiblingClass);
			} else {
				kids.addClass(inactiveAlarmTab);

				kids.eq(index).removeClass(inactiveAlarmTab);
				kids.eq(index).addClass(activeAlarmTab);

				kids.eq(index).prev().addClass(topSiblingClass);
				kids.eq(index).next().addClass(bottomSiblingClass);
			}
		}
	})

	// Show custom set alarms
	function displayCustomAlarms() {
		var fillAlarmsTable = '';
		if (alarms_global.length == 0) {
			fillAlarmsTable = '<div class="text-center">Custom set alarms will be displayed here...</div>';
		} else {
			for(i = 0; i < alarms_global.length; i++) {
				var recipientList = '';
				for (j = 0; j < alarms_global[i].recipients.length; j++) {
					recipientList += alarms_global[i].recipients[j].fullName + '<br>';
				}
				fillAlarmsTable += `
				<!-- Alarm -->
				<div class="flex flex-col">
					<!-- Slider header -->
					<div id="customAlarm_header" class="grid grid-cols-8 bg-lightblue-100 border border-lightblue-300 h-10 items-center rounded-t duration-100 ease-out">
						<div class="col-span-3 md:col-span-4 text-center font-semibold text-xs lg:text-sm whitespace-nowrap overflow-ellipsis overflow-hidden px-1">`+alarms_global[i].channelName+`</div>
						<div class="col-span-3 md:col-span-3 text-center font-semibold border-l border-r border-lightblue-300 text-xs lg:text-sm whitespace-nowrap overflow-ellipsis overflow-hidden px-1">`+alarms_global[i].operator+` `+alarms_global[i].thresholdValue+`</div>
						<div class="col-span-2 md:col-span-1 flex justify-center items-center">
							<div id="customAlarm_header_btn" class="h-full w-full rounded border border-transparent cursor-pointer p-2 flex justify-center items-center hover:text-black transition transform duration-250">
								<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
							</div>
						</div>
					</div>
	
					<!-- Slider body -->
					<div id="customAlarm_body" class="bg-white border border-t-0 border-lightblue-300 p-2 rounded-b hidden">
	
						<div class="grid grid-cols-5 py-2 items-center border-b border-gray-200">
							<div class="col-span-2 font-medium text-xs text-bluegray-600 mx-2">Channel name</div>
							<div class="col-span-3 font-semibold text-sm text-bluegray-900 whitespace-nowrap overflow-ellipsis overflow-hidden">
								`+alarms_global[i].channelName+`
							</div>
						</div>
						<div class="grid grid-cols-5 py-2 items-center bg-bluegray-100 border-b border-gray-200">
							<div class="col-span-2 font-medium text-xs text-bluegray-600 mx-2">Threshold Value</div>
							<div id="alarm_thresholdEdit" class="col-span-3 font-semibold text-sm text-bluegray-900 whitespace-nowrap overflow-ellipsis overflow-hidden">
								`+alarms_global[i].operator+` `+alarms_global[i].thresholdValue+`
							</div>
						</div>
						<div class="grid grid-cols-5 py-2 items-center border-b border-gray-200">
							<div class="col-span-2 font-medium text-xs text-bluegray-600 mx-2">Recipients</div>
							<div id="recipientList" class="col-span-3 text-sm text-bluegray-900 whitespace-nowrap overflow-ellipsis overflow-hidden">
								`+recipientList+`
							</div>
						</div>
						<div data-id="`+alarms_global[i].customAlarmId+`" class="flex justify-center items-center space-x-2 my-4">
							<!-- <button id="alarm_editButton" class="focus:outline-none text-xs md:text-sm px-2 md:px-6 h-8 text-lightblue-900 uppercase duration-100 bg-lightblue-100 border border-lightblue-500 rounded font-semibold hover:bg-lightblue-300" title="Edit">Edit</button> -->

							<!-- <button id="alarm_cancelButton" class="focus:outline-none text-xs md:text-sm px-2 md:px-6 h-8 text-bluegray-100 uppercase duration-100 bg-red-500 rounded font-semibold hover:bg-red-700 hidden" title="Cancel">Cancel</button> -->

							<!-- <button id="alarm_saveButton" class="focus:outline-none text-xs md:text-sm px-2 md:px-6 h-8 text-green-900 uppercase duration-100 bg-green-100 border border-green-500 rounded font-semibold hover:bg-green-300 hidden" title="Save">Save</button> -->
	
							<button id="alarm_deleteButton" class="focus:outline-none text-xs md:text-sm px-2 md:px-6 h-8 text-red-900 uppercase duration-100 bg-white border border-red-500 rounded font-semibold hover:bg-red-300" title="Delete">Delete</button>
						</div>
					</div>
				</div>
				<!-- End of alarm -->
				`;
			}
		}
		// Display alarms
		$('#customAlarms').html(fillAlarmsTable);
	}

	// function processAlarm(func, data) {
	// 	var customAlarmId = data;
	// 	var thisAlarm = alarms_global.find(x => x.customAlarmId == customAlarmId);
	// 	if (func == 'delete') {
	// 		$.ajax({
	// 			url: './includes/sqlSingleDevice.php',
	// 			type: 'POST',
	// 			data: {
	// 				customAlarmId: alarmId,
	// 				function: 'deleteCustomAlarm'
	// 			},
	// 			success: function (data) {
	// 				alarms_global.splice(alarms_global.findIndex(x => x.customAlarmId === alarmId), 1)
	// 				displayCustomAlarms();
	// 			}
	// 		})
	// 	} else if (func == 'edit') {
	// 		// Show appropriate buttons
	// 		$('#alarm_cancelButton').show();
	// 		$('#alarm_saveButton').show();
	// 		$('#alarm_editButton').hide();
	// 		// Choose selected field
	// 		var a = '';
	// 		var b = '';
	// 		var c = '';
	// 		var d = '';
	// 		var e = '';
	// 		switch (thisAlarm.operator) {
	// 			case '>':
	// 				a = 'selected';
	// 				break;
	// 			case '>=':
	// 				b = 'selected';
	// 				break;
	// 			case '==':
	// 				c = 'selected';
	// 				break;
	// 			case '<':
	// 				d = 'selected';
	// 				break;
	// 			case '<=':
	// 				e = 'selected';
	// 				break;
	// 			default:
	// 				a = 'selected';
	// 		}
	// 		// Change values with input fields
	// 		var thresholdField = `
	// 		<div id="alarm_thresholdEdit" class="col-span-3 flex flex-row">
	// 			<select id="alarmEdit_newOp" class="flex-auto outline-none border border-gray-200 h-8 px-4 text-xs w-16 sm:text-sm capitalize font-semibold text-bluegray-800 bg-white transition-all hover:border-gray-400 focus:border-gray-400 hover:text-bluegray-900 rounded overflow-ellipsis overflow-hidden">
	// 				<option data-id=">" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 overflow-ellipsis overflow-hidden" `+a+`>Greater than (>)</option>
	// 				<option data-id=">=" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 overflow-ellipsis overflow-hidden" `+b+`>Greater than or equals (>=)</option>
	// 				<option data-id="==" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 overflow-ellipsis overflow-hidden" `+c+`>Equals (==)</option>
	// 				<option data-id="<" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 overflow-ellipsis overflow-hidden" `+d+`>Less than (<)</option>
	// 				<option data-id="<=" class="font-medium text-sm bg-bluegray-50 text-bluegray-800 overflow-ellipsis overflow-hidden" `+e+`>Less than or equals (<=)</option>
	// 			</select>
	// 			<div>
	// 				<input type="text" id="alarmEdit_newValue" placeholder="`+thisAlarm.thresholdValue+`" value="`+thisAlarm.thresholdValue+`" required spellcheck="false" autocomplete="none" class="outline-none border border-gray-200 h-8 px-4 text-sm font-semibold text-bluegray-800 bg-white w-16 mx-1 transition-all focus:border-gray-400 rounded">
	// 			</div>
	// 		</div>`;
	// 		$('#alarm_thresholdEdit').replaceWith( thresholdField );
			
	// 		var recipientField = '<div id="recipientList" class="col-span-3 relative">';
	// 		// Add search bar
	// 		recipientField += `
	// 			<div class="flex justify-center items-center h-12">
	// 				<div class="hidden flex-none sm:flex justify-center items-center h-full bg-bluegray-100 rounded-l border border-r-0 border-gray-200">
	// 					<svg class="w-4 h-4 mx-2 text-bluegray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
	// 				</div>
	// 				<input type="text" id="alarm_searchUsers" placeholder="Search..." spellcheck="false" autocomplete="none" class="outline-none flex-auto w-full h-full px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 transition-all focus:bg-bluegray-50 rounded-r border border-gray-200">
	// 			</div>
	// 			<div id="alarm_searchDisplay" class="bg-white border border-t-0 border-gray-200 rounded-t w-full absolute whitespace-nowrap overflow-ellipsis overflow-hidden overflow-y-auto hidden" style="max-height: 150px">

	// 			</div>
	// 		`;
	// 		// Show users
	// 		recipientField += '<div id="alarmEdit_newUsers" class="grid grid-cols-1 lg:grid-cols-2 gap-2 mt-4">';
	// 		for (i = 0; i < thisAlarm.recipients.length; i++) {
	// 			recipientField += `
	// 			<div id="`+thisAlarm.recipients[i].userId+`" class="flex justify-center items-center bg-gray-100 border border-gray-200 py-1 cursor-pointer transition-all duration-100 hover:bg-red-100 hover:text-red-900 hover:border-red-300 space-x-2">
	// 				<p class="whitespace-nowrap overflow-ellipsis overflow-hidden">`+thisAlarm.recipients[i].fullName+`</p>
	// 				<svg class="flex-none w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
	// 			</div>
	// 			`
	// 		}
	// 		recipientField += '</div></div>';
	// 		$('#recipientList').replaceWith( recipientField );
	// 	} else if (func == 'cancel') {
	// 		$('#alarm_cancelButton').hide();
	// 		$('#alarm_saveButton').hide();
	// 		$('#alarm_editButton').show();
	// 		// Replace value and threshold row
	// 		$('#alarm_thresholdEdit').replaceWith(
	// 			`<div id="alarm_thresholdEdit" class="col-span-3 font-semibold text-sm text-bluegray-900 whitespace-nowrap overflow-ellipsis overflow-hidden">
	// 				`+alarms_global[0].operator+` `+alarms_global[0].thresholdValue+`
	// 			</div>`
	// 		);
	// 		// Replace recipients row
	// 		var recipientList = '';
	// 		for (j = 0; j < thisAlarm.recipients.length; j++) {
	// 			recipientList += thisAlarm.recipients[j].fullName + '<br>';
	// 		}
	// 		$('#recipientList').replaceWith(
	// 			`<div id="recipientList" class="col-span-3 text-sm text-bluegray-900 whitespace-nowrap overflow-ellipsis overflow-hidden">
	// 				`+recipientList+`
	// 			</div>`
	// 		);
	// 		// $('#recipientList').replaceWith( recipientList );
	// 	} else if (func == 'save') {
	// 		console.log('id:'+customAlarmId);
	// 		console.log('threshold:'+$('#alarmEdit_newValue').val());
	// 		console.log('op:'+ $('#alarmEdit_newOp').find(':selected').attr('data-id') );
			
	// 		console.log(getAssignedUsers());
	// 		console.log(allUsers);
	// 	}
	// }

	// function getAssignedUsers() {
	// 	var newRecipientsList = [];
	// 	$("#alarmEdit_newUsers > div").each((index, elem) => {
	// 		newRecipientsList.push(elem.id);
	// 	});
	// 	return newRecipientsList;
	// }

	// var allUsers = [];
	// function getAllUsers() {
	// 	$.ajax({
	// 		url: './includes/sqlSingleDevice.php',
	// 		type: 'POST',
	// 		data: {
	// 			deviceId: deviceId,
	// 			function: 'loadUsersList'
	// 		},
	// 		success: function (data) {
	// 			allUsers = JSON.parse(data);
	// 			// console.log(asd);
	// 			// return JSON.parse(data);
	// 		}
	// 	})
	// }

	// $('#customAlarms').delegate('#alarm_searchUsers', 'keyup', function(e) {
	// 	var searchString = e.target.value.toUpperCase();
	// })

	// $('#alarm_searchUsers').keyup( function(e) {
	// 	console.log(e);
		// var searchString = e.target.value.toUpperCase();
		// var filteredUsers = unselectedUsers.filter(user => {
		// 	return user.fullName.toUpperCase().includes(searchString) || user.email.toUpperCase().includes(searchString);
		// })
		// displayUnselectedUsers(filteredUsers);
	// })
	function deleteAlarm(alarmId) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				customAlarmId: alarmId,
				function: 'deleteCustomAlarm'
			},
			success: function (data) {
				alarms_global.splice(alarms_global.findIndex(x => x.customAlarmId === alarmId), 1)
				displayCustomAlarms();
			}
		})
	}
	$('#customAlarms').delegate('#alarm_deleteButton', 'click', function() {
		data = $(this).parent().attr('data-id');
		if (confirm("Are you sure you want to delete this alarm?")) {
			deleteAlarm(data);
		}
	})

	// $('#customAlarms').delegate('#alarm_editButton', 'click', function() {
	// 	data = $(this).parent().attr('data-id');
	// 	processAlarm('edit', data);
	// })

	// $('#customAlarms').delegate('#alarm_cancelButton', 'click', function() {
	// 	data = $(this).parent().attr('data-id');
	// 	if (confirm("Are you sure you want to cancel the changes you've made?")) {
	// 		processAlarm('cancel', data)
	// 	}
	// })

	// $('#customAlarms').delegate('#alarm_saveButton', 'click', function() {
	// 	data = $(this).parent().attr('data-id');
	// 	if (confirm("Are you sure you want to save the changes you've made?")) {
	// 		processAlarm('save', data)
	// 	}
	// })
	
	$('#customAlarms').delegate('#customAlarm_header_btn', 'mouseenter mouseleave', function() {
		$(this).parents('#customAlarm_header').toggleClass('border-lightblue-500');
	})

	$('#customAlarms').delegate('#customAlarm_header_btn', 'click', function() {
		var alarmBody = $(this).parents('#customAlarm_header').siblings('#customAlarm_body');
		if ($(alarmBody).is(':hidden')) {
			$(this).addClass('rotate-180');
			alarmBody.slideDown("fast");
		} else {
			$(this).removeClass('rotate-180');
			alarmBody.slideUp("fast");
		}
	})

	var alarms_global = [];
	$.ajax({
		url: './includes/sqlSingleDevice.php',
		type: 'POST',
		data: {
			deviceId: deviceId,
			function: 'loadSetAlarms'
		},
		success: function (data) {
			// console.log(data);
			alarms_global = JSON.parse(data);
			displayCustomAlarms();
		}
	})

	// Load channel name options
	$.ajax({
		url: './includes/sqlSingleDevice.php',
		type: 'POST',
		data: {
			deviceId: deviceId,
			function: 'loadChannelData'
		},
		success: function (data) {
			var data = JSON.parse(data);

			var fillSelect = '';
			fillSelect += `
				<option data-id="-1" selected class="font-medium text-sm bg-bluegray-50 text-bluegray-800" disabled>Channel</option>
			`;
			for (i = 0; i < data.length; i++) {
				fillSelect += `
					<option data-id="`+data[i].channelId+`" data-unit="`+data[i].unitAbbreviation+`" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">`+data[i].channelName+`</option>
				`;
			}
			$('#channelSelect').html(fillSelect);
		}
	})

	$('#unitField').hide();
	$('#channelSelect').on('change', function() {
		$('#operatorSelect').prop('selectedIndex',0);
		$('#alarmValue').val('');
		var unit = $(this).find(':selected').attr('data-unit');
		if (unit == 'null') {
			$('#unitField').hide();
		} else {
			$('#unitField > p').html(unit);
			$('#unitField').show();
		}
	})

	// Alarms functionality
	function updateUsersHeight() {
		var siblingHeight = $('#alarmForm').height();
		$('#usersDiv').css('max-height', siblingHeight+'px');
	}

	// Declare global lists
	var selectedUsers = [];
	var unselectedUsers = [];

	// Populate list of users
	function loadAllUsers() {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				deviceId: deviceId,
				function: 'loadUsersList'
			},
			success: function (data) {
				unselectedUsers = JSON.parse(data);
				displayUnselectedUsers();
			}
		})
	}
	loadAllUsers();

	$.ajax({
		url: './includes/sqlSingleDevice.php',
		type: 'POST',
		data: {
			deviceId: deviceId,
			function: 'loadDeviceData'
		},
		success: function (data) {
			var deviceData = JSON.parse(data);
			$('#deviceName').val(deviceData[0].deviceName);
		}
	})

	function displayUnselectedUsers(list) {
		// To accommodate search tool
		var users = [];
		if (list != undefined) {
			users = list;
		} else {
			users = unselectedUsers;
		}

		
		var fillUnselectedUsersTable = '';
		for(i = 0; i < users.length; i++) {
			fillUnselectedUsersTable += `
			<div class="h-10 flex relative text-xs lg:text-sm justify-start lg:justify-center items-center bg-bluegray-100 rounded border border-gray-200 transition duration-150 whitespace-nowrap overflow-ellipsis overflow-hidden pl-2">
				<div>
					<span class="font-semibold">`+users[i].fullName+`</span> <span class="text-bluegray-500">&lt;`+users[i].email+`&gt;</span>
				</div>

				<div id="addButton" data-id="`+users[i].userId+`:`+users[i].fullName+`:`+users[i].email+`" class="absolute right-0 h-8 w-10 lg:w-14 text-xs lg:text-sm rounded flex justify-center items-center mr-1 bg-white border border-gray-200 uppercase font-semibold text-bluegray-900 cursor-pointer transition duration-150 hover:bg-gray-50 hover:border-gray-500">
					<span>ADD</span>
				</div>
			</div>
			`;
		}
		// Display list of recipients
		$('#usersList').html(fillUnselectedUsersTable);
	}

	function displaySelectedUsers() {
		let users = selectedUsers;
		var fillSelectedUsersTable = '';
		for(i = 0; i < users.length; i++) {
			fillSelectedUsersTable += `
			<div id="singleUser" data-id="`+users[i].userId+`:`+users[i].fullName+`:`+users[i].email+`" class="flex justify-center items-center bg-gray-100 border border-gray-200 py-1 cursor-pointer transition-all duration-100 hover:bg-red-100 hover:text-red-900 hover:border-red-300 space-x-2">
				<p class="whitespace-nowrap overflow-ellipsis overflow-hidden">`+users[i].fullName+`</p>
				<svg class="flex-none w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
			</div>
			`;
		}
		// Display list of recipients
		$('#selectedRecipients').html(fillSelectedUsersTable);
	}

	// Check if element exists in array
	function checkIfContains(arr, search) {
		var returnAns = '';

		if (arr.length > 0) {
			for(i = 0; i < arr.length; i++) {
				if (arr[i].userId == search) {
					returnAns = true;
					break;
				} else {
					returnAns = false;
				}
			}
		} else {
			returnAns = false;
		}

		return returnAns;
	}

	// Remove row from array
	function removeFromArray(userId, array) {
		for (i = 0; i < array.length; i++) {
			if (array[i].userId == userId) {
				array.splice(i, 1)
			}
		}

		return array;
	}

	function processUsers(dataid, func) {
		var userId = dataid.split(':')[0];
		var name = dataid.split(':')[1];
		var email = dataid.split(':')[2];

		if (func == 'add') {
			// Add to selected users
			if (!checkIfContains(selectedUsers, userId) && checkIfContains(unselectedUsers, userId)) {
				// Add this user to selected users list and display it
				selectedUsers.push({userId: userId, fullName: name, email: email});
				displaySelectedUsers();

				// Delete this from unselected list and display new list
				removeFromArray(userId, unselectedUsers);
				displayUnselectedUsers();
			}
		} else if (func == 'remove') {
			// Remove from selected users
			if (checkIfContains(selectedUsers, userId) && !checkIfContains(unselectedUsers, userId)) {
				// Add the item to unselected users and display it
				unselectedUsers.push({userId: userId, fullName: name, email: email});
				displayUnselectedUsers();

				// Remove from selected users
				removeFromArray(userId, selectedUsers);
				displaySelectedUsers();
			}
		}
	}

	$('#userSearchBar').keyup( function(e) {
		var searchString = e.target.value.toUpperCase();
		var filteredUsers = unselectedUsers.filter(user => {
			return user.fullName.toUpperCase().includes(searchString) || user.email.toUpperCase().includes(searchString);
		})
		displayUnselectedUsers(filteredUsers);
	})

	$('#usersList').delegate('#addButton', 'mouseenter mouseleave', function() {
		$(this).parent().toggleClass('border-gray-400');
	})

	$('#usersList').delegate('#addButton', 'click', function() {
		var dataid = $(this).attr('data-id');
		processUsers(dataid, 'add');
		updateUsersHeight();
	})

	$('#selectedRecipients').delegate('#singleUser', 'click', function() {
		var dataid = $(this).attr('data-id');
		processUsers(dataid, 'remove');
		updateUsersHeight();
	})

	var valueCheck = false;
	$('#alarmValue').on('blur', function() {
		var val = $(this).val();
		if (val == '') {
			$(this).addClass('border-red-500');
			valueCheck = false;
		} else {
			if ($.isNumeric(val)) {
				$(this).removeClass('border-red-500');
				valueCheck = true;
			} else {
				$(this).addClass('border-red-500');
				valueCheck = false;
			}
		}
	})

	// Submit form
	$('#alarmForm').on('submit', function(e) {
        e.preventDefault();

		var submitRecipientArr = [];
        for (i=0; i<selectedUsers.length; i++) {
            submitRecipientArr.push(selectedUsers[i].userId)
        }

		if (valueCheck == true && $('#channelSelect').find(':selected').attr('data-id') != '-1' && submitRecipientArr.length > 0) {
			var channelId = $('#channelSelect').find(':selected').attr('data-id');
			var operator = $('#operatorSelect').find(':selected').attr('data-id');
			var thresholdValue = $('#alarmValue').val();
			var recipientIds = JSON.stringify(submitRecipientArr);
			
			$.post('./includes/sqlSingleDevice.php', {
				deviceId: deviceId,
				channelId: channelId,
				operator: operator,
				thresholdValue: thresholdValue,
				recipientIds: recipientIds,
                function: 'createCustomAlarm'
            }, function(data) {
                $('#alarmForm').trigger("reset");
				loadAllUsers();
				displayUnselectedUsers();
				selectedUsers = [];
				displaySelectedUsers();
            });
		}
	})
})