$(document).ready(function () {
	
	// ! Find deviceId from URL
    var deviceId = window.location.href.split('?id=')[1];
    if (deviceId == null) {
        document.location.href = 'devices.php';
    }

	// Update title page
	getDeviceData().then( function(data) {
		$(document).prop('title', data.deviceName + ' - YelCloud ');
		$('#sidePanel-deviceName').html(data.deviceName);
		session_roleId = $('#siteContent').attr('data-roleId');
		session_groupId = $('#siteContent').attr('data-groupId');

		if (session_roleId != 1 && session_roleId != 2 && session_groupId != data.groupId) {
			document.location.href = 'devices.php';
		}
	})


	// ! Function to show appropriate component
	function displayComponent(component) {
		switch (component) {
			case 'rtmu_dashboard':
				display_rtmu_dashboard();
				break;
			case 'rtmu_controlPanel':
				display_rtmu_controlPanel();
				break;
			case 'ewb_dashboard':
				display_ewb_dashboard();
				break;
			case 'ewbv2_dashboard':
				display_ewbv2_dashboard();
				break;
			case 'tilt_dashboard':
				display_tilt_dashboard();
				break;
			case 'alarms':
				display_alarms();
				break;
			case 'ewbv2_alarms':
				display_alarms('ewbv2');
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
			case 'message_history':
				display_messageHistory();
				break;
		}
	}

	// ! Data about each device
	var subNavbar = {
		'rtmu': [
			{ title: 'Dashboard', component:'rtmu_dashboard' },
			{ title: 'Control Panel', component:'rtmu_controlPanel' },
			{ title: 'Alarms', component:'alarms' },
			{ title: 'Recipients', component:'recipients' },
			{ title: 'Log', component:'log' }
		],
		'ewb': [
			{ title: 'Dashboard', component:'ewb_dashboard' },
			{ title: 'Control Panel', component:'rtmu_controlPanel' },
			{ title: 'Recipients', component:'recipients' }
		],
		'ewb v2': [
			{ title: 'Dashboard', component:'ewbv2_dashboard' },
			{ title: 'Control Panel', component:'rtmu_controlPanel' },
			{ title: 'Alarms', component: 'ewbv2_alarms' },
			{ title: 'Recipients', component:'recipients' },
			{ title: 'Log', component:'log' },
			{ title: 'Message History', component:'message_history' }
		],
		'tilt': [
			{ title: 'Dashboard', component:'tilt_dashboard' },
			{ title: 'Control Panel', component:'rtmu_controlPanel' },
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

	function displayModalChannels(channels) {
		// Displaying registered channels on the device
		var channelsOutput = '';
		for (i = 0; i < channels.length; i++) {
			// Hide units if digital
			var hiddenClass = '';
			if (channels[i].unitName == null) {
				var hiddenClass = 'hidden';
			}
			channelsOutput += `
				<div class="flex space-x-2">
					<div class="h-6 flex-auto flex bg-white rounded border border-gray-300 text-sm font-medium items-center">
						<div class="w-8 text-center border-r border-gray-300 truncate">`+channels[i].channelType+`</div>
						<div class="flex-auto pl-2 whitespace-nowrap truncate">`+channels[i].channelName+`</div>
						<div class="w-8 text-center border-l border-gray-300 `+hiddenClass+`">`+channels[i].unitName+`</div>
					</div>
				</div>
			`;
		}
		$('#deviceProfile-channels').html(channelsOutput);
		getUnits().then( function(units) {
			var unitsOutput = '';
			for (j = 0; j < units.length; j++) {
				unitsOutput += '<option data-id="'+units[j].unitId+'">'+units[j].unitName+'</option>';
			}

			$('#deviceProfile-channels').append(`
				<div class="flex">
					<div id="newChannelBtn" class="flex-none h-6 w-6 bg-green-200 hover:bg-green-400 text-green-500 hover:text-green-800 rounded-full flex justify-center items-center transition duration-200 cursor-pointer"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
					</div>
	
					<div id="newChannelInfo" class="h-6 flex-auto flex bg-white rounded border border-gray-300 text-sm font-medium items-center hidden">
						<select id="newChannel-type" class="w-10 sm:w-16 p-0 pl-1 h-full bg-white rounded-none rounded-l border-0 border-r border-gray-300 focus:ring-0 focus:border-gray-300 focus:bg-gray-100 transition-all truncate">
								<option data-id="AI" selected>AI</option>
								<option data-id="DI">DI</option>
								<option data-id="COUNTER">COUNTER</option>
						</select>

						<input id="newChannel-name" class="flex-1 h-full bg-white border-none border border-gray-300 focus:ring-inset px-2" type="text">

						<select id="newChannel-unit" class="w-10 sm:w-16 p-0 pl-1 h-full bg-white rounded-none border-0 border-l border-gray-300 focus:ring-0 focus:border-gray-300 focus:bg-gray-100 transition-all">
							`+unitsOutput+`
						</select>
	
						<div id="newChannel-save" class="w-6 h-full cursor-pointer flex justify-center items-center border-l border-gray-300 bg-green-200 hover:bg-green-400 text-green-500 hover:text-green-800 ">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
						</div>

						<div id="newChannel-close" class="w-6 h-full cursor-pointer flex justify-center items-center border-l border-gray-300 bg-red-200 hover:bg-red-400 text-red-500 hover:text-red-800">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
						</div>
					</div>
				</div>
			`);
		});
	}

	
	// Listen for cancel buttons
	$('#deviceProfile-channels').delegate('#newChannelBtn', 'click', function() {
		$('#newChannelInfo').removeClass('hidden');
		$('#newChannelBtn').hide();
	});
	$('#deviceProfile-channels').delegate('#newChannel-close', 'click', function() {
		$('#newChannelInfo').addClass('hidden');
		$('#newChannelBtn').show();
	});
	
	// Listen for channel delete
	$('#deviceProfile-channels').delegate('#deleteChannel', 'click', function() {
		var channelId = $(this).attr('data-id');
		if(confirm("Are you sure you want to delete this channel?")) {
			deleteChannel(channelId).then( function(response) {
				alert(response)
				getDeviceData().then( function(data) {
					displayModalChannels(data.channels);
				})
			})
		}
	});

	$('#deviceProfile-channels').delegate('#newChannel-type', 'change', function() {
		if( $(this).val() == 'DI' ) {
			$('#newChannel-unit').hide();
		} else {
			$('#newChannel-unit').show();
		}
	});

	// Listen for new channel save
	$('#deviceProfile-channels').delegate('#newChannel-save', 'click', function() {
		var channelType = $('#newChannel-type').find(':selected').attr('data-id');
		var channelName = $.trim($('#newChannel-name').val());
		var unitId = $('#newChannel-unit').find(':selected').attr('data-id');
		createChannel(channelType, channelName, unitId).then( function(response) {
			if (response.status == 200) {
				getDeviceData().then( function(data) {
					displayModalChannels(data.channels);
				})
			} else {
				alert(response.message);
			}
		})
	});

	$('#saveDevice').on('click', function() {
		var deviceName = $.trim($('#deviceProfile-deviceName').val());
		var devicePhone = $.trim($('#deviceProfile-devicePhone').val());
		var groupId = $('#deviceProfile-group').find(':selected').attr('data-id');
		var productId = $('#deviceProfile-product').find(':selected').attr('data-id');
		var deviceTypeId = $('#deviceProfile-deviceType').find(':selected').attr('data-id');
		updateDeviceData(deviceName, devicePhone, groupId, productId, deviceTypeId).then(function(response) {
			if (response.status == 200) {
				alert(response.message);
				$('#editDevice-modal').addClass('hidden');
				location.reload();
			} else {
				alert(response.message);
			}
		})
	})

	$('#deleteDevice').on('click', function() {
		if( confirm('Are you sure you want to delete this device?') ) {
			deleteDevice(deviceId);
			document.location.href = 'devices.php';
		}
	})

	// ! Edit device modal
	$('#editDevice').on('click', function() {
		$('#editDevice-modal').removeClass('hidden');
		getDeviceData().then( function(data) {
			$('#deviceProfile-id').html(data.deviceId);
			$('#deviceProfile-deviceName').val(data.deviceName);
			$('#deviceProfile-devicePhone').val(data.devicePhone);
			$('#deviceProfile-group').val(data.groupName);
			$('#deviceProfile-product').val(data.productName);
			$('#deviceProfile-deviceType').val(data.deviceTypeName);
			var createdAt = new Date( data.createdAt );
			createdAt = createdAt.toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
			$('#deviceProfile-createdAt').html(createdAt);
			$('#deviceProfile-createdBy').html(data.createdBy);
			displayModalChannels(data.channels);
		})
		
	})

	// Listen for close buttons
	$('#editDevice-modal').delegate('#close-device-modal, #cancelDevice', 'click', function() {
		$('#editDevice-modal').addClass('hidden');
	});

	// ! ---------------------------------
	// ! ***** Building components *****  |
	// ! ---------------------------------
	siteContent = $('#siteContent');

	// ! SHOW COMPONENT: Display rtmu dashboard
	function display_rtmu_dashboard() {
		// * Put all cards together and generate final output
		var output = `
		<div class="grid grid-cols-2 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-4 lg:gap-6">
			<!-- Card -->
			<div id="statusAndProbeCards"></div>
			<!-- End of card -->

			<!-- Card -->
			<div id="latestMeasurementsCard" class="col-span-1 card-wrapper-1 border-lightblue-400">
				<div class="card-header-1">
					<div class="card-icon-1 text-lightblue-500">
						<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" class="bi bi-thermometer-half" viewBox="0 0 16 16">
							<path d="M9.5 12.5a1.5 1.5 0 1 1-2-1.415V6.5a.5.5 0 0 1 1 0v4.585a1.5 1.5 0 0 1 1 1.415z"/>
							<path d="M5.5 2.5a2.5 2.5 0 0 1 5 0v7.55a3.5 3.5 0 1 1-5 0V2.5zM8 1a1.5 1.5 0 0 0-1.5 1.5v7.987l-.167.15a2.5 2.5 0 1 0 3.333 0l-.166-.15V2.5A1.5 1.5 0 0 0 8 1z"/>
						</svg>
					</div>
					<div class="card-title-1">
						Last reading	
					</div>
				</div>
				<div id="latestMeasurements_body" class="card-body-1 flex-col">
					<p class="italic mt-4">No channels found...</p>
				</div>
			</div>
			<!-- End of card -->
			
			<!-- Card -->
			<div id="latestTempCard" class="col-span-1 card-wrapper-1"></div>
			<!-- End of card -->

			<!-- Card -->
			<div id="totalAlarmsCard" class="col-span-1 card-wrapper-1"></div>
			<!-- End of card -->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
				<!-- Title -->
				<div class="card-header-1 relative">
					<div class="flex items-center">
						<div class="hidden sm:block text-gray-500 p-2 ml-4 mr-2 lg:mr-4">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
						</div>
						<div id="dateSelectors" class="flex justify-center items-center space-x-2 ml-2">
							<div data-id="3hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">3hr</div>
							<div data-id="12hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">12hr</div>
							<div data-id="1d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">1d</div>
							<div data-id="7d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">7d</div>
							<div data-id="30d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">30d</div>
							<div data-id="ChartCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
							</div>
						</div>
						<div id="ChartCalSelector" class="w-48 bg-white shadow rounded z-10 absolute left-28 top-10 border flex flex-col p-2 hidden">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
							<input id="chartFrom" type="date" class="bg-white text-gray-700">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
							<input id="chartTo" type="date" class="bg-white text-gray-700">
							<button class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
						</div>
					</div>
				</div>

				<!-- Chart -->
				<div class="card-body-1 p-2 lg:p-4">
					<canvas id="canvas">
						<!-- Filled with js -->
					</canvas>
				</div>
			</div>
			<!-- End of card-->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
				<div class="card-header-1 relative">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>	
					</div>
					<div class="card-title-1">
						Alarms	
					</div>

					<div id="AlarmCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800 absolute right-4">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
					</div>
					<div id="AlarmCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
						<input id="alarmFrom" type="date" class="bg-white text-gray-700">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
						<input id="alarmTo" type="date" class="bg-white text-gray-700">
						<div class="flex justify-center items-center">
							<button id="goAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
							<button id="resetAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
						</div>
					</div>
				</div>

				<!-- Table -->
				<div id="alarmsCardBody" class="flex-auto bg-gray-50 py-2 px-4">
					<div id="triggeredAlarms">
					
					</div>
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-600 whitespace-nowrap">Channel</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600 whitespace-nowrap">Alarm</th>
									<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600 whitespace-nowrap">Timestamp</th>
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
							<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
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
				var statusIcon = '<svg class="w-6 h-6 text-'+statusColor+'-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path></svg>';
				
				var probeStatus = 'ON TRACK';
			} else {
				var deviceStatus = 'OFF';
				var statusColor = 'red';
				var statusIcon = '<svg class="w-6 h-6 text-'+statusColor+'-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3.707 2.293a1 1 0 00-1.414 1.414l6.921 6.922c.05.062.105.118.168.167l6.91 6.911a1 1 0 001.415-1.414l-.675-.675a9.001 9.001 0 00-.668-11.982A1 1 0 1014.95 5.05a7.002 7.002 0 01.657 9.143l-1.435-1.435a5.002 5.002 0 00-.636-6.294A1 1 0 0012.12 7.88c.924.923 1.12 2.3.587 3.415l-1.992-1.992a.922.922 0 00-.018-.018l-6.99-6.991zM3.238 8.187a1 1 0 00-1.933-.516c-.8 3-.025 6.336 2.331 8.693a1 1 0 001.414-1.415 6.997 6.997 0 01-1.812-6.762zM7.4 11.5a1 1 0 10-1.73 1c.214.371.48.72.795 1.035a1 1 0 001.414-1.414c-.191-.191-.35-.4-.478-.622z"></path></svg>'

				var probeStatus = 'OFF TRACK';
			}

			// This is not used. Defined to overcome Tailwind Purge feature
			var unused1 = 'bg-green-50';
			var unused2 = 'bg-red-50';

			var statusAndProbeCards = `
				<div class="col-span-1 flex flex-col space-y-4">
					<!-- Device status -->
					<div class="flex justify-between items-center bg-`+statusColor+`-50 border border-`+statusColor+`-300 shadow-md h-16 lg:h-20">
						<div class="flex-1 flex items-center space-x-2 pl-4">
							`+statusIcon+`
							<p class="uppercase text-xs lg:text-sm font-medium text-gray-700">Status</p>
						</div>
						<div class="flex-1 text-center">
							<p class="uppercase text-xs lg:text-sm font-medium text-`+statusColor+`-500">` + deviceStatus + `</p>
						</div>
					</div>

					<!-- Probe status -->
					<div class="flex justify-between items-center bg-`+statusColor+`-50 border border-`+statusColor+`-300 shadow-md h-16 lg:h-20">
						<div class="flex-1 flex items-center space-x-2 pl-4">
							<svg class="w-6 h-6 text-`+statusColor+`-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>
							<p class="uppercase text-xs lg:text-sm font-medium text-gray-700">Probe</p>
						</div>
						<div class="flex-1 text-center">
							<p class="uppercase text-xs lg:text-sm font-medium text-`+statusColor+`-500">`+probeStatus+`</p>
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
				if (readingsData.latestMeasurements[0] == null) {
					var latestMeasurementsCard = `
						<p class="italic mt-4">No readings found...</p>
					`;
				} else {
					var dateDisplay = new Date( readingsData.latestMeasurements[0].measurementTime );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });
					var latestMeasurementsCard = `
					<div><p class="mt-2 mb-1 text-sm font-medium text-gray-600 border-b lg:py-1">`+readingsData.latestMeasurements[0].channelName+`</p></div>
					<div><span class="text-3xl lg:text-5xl font-medium text-gray-800">`+readingsData.latestMeasurements[0].measurement+`</span><span class="">`+readingsData.latestMeasurements[0].unitName+`</span></div>
					<div><p class="text-xs text-gray-400 italic mt-1 mb-2">`+dateDisplay+`</p></div>
					`;
				}

				// * Latest temp card
				getDeviceCoordinates().then( function(deviceCoordinates) {
					// * latestTempCard
					//#region 
					var latestTempCard = `
						<div class="card-header-1">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="card-title-1">
								Current temp
							</div>
						</div>
		
						<div class="card-body-1 flex-col space-y-2 py-4">
							<p class="font-medium text-gray-800"><span id="api_temp" class="text-5xl tracking-tighter"></span> <span class="">&#176;C</span></p>
							<p id="api_loc" class="text-xs text-gray-400"></p>
						</div>
						`;
					// Update values with latest temp
					$('#latestTempCard').html(latestTempCard);
					getLatestTemp(deviceCoordinates, 'api_temp', 'api_loc');
					//#endregion
				})
			}

			if (readingsData.numberOfAI == 2) {
				if (readingsData.latestMeasurements[0] == null) {
					var latestMeasurementsCard = `
						<p class="italic mt-4">No readings found...</p>
					`;
				} else {
					var dateDisplay = new Date( readingsData.latestMeasurements[0].measurementTime );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });
					var latestMeasurementsCard = `
					<div><p class="mt-2 mb-1 text-sm font-medium text-gray-600 border-b lg:py-1">`+readingsData.latestMeasurements[0].channelName+`</p></div>
					<div><span class="text-3xl lg:text-5xl font-medium text-gray-800">`+readingsData.latestMeasurements[0].measurement+`</span> <span class="">`+readingsData.latestMeasurements[0].unitName+`</span></div>
					<div><p class="text-xs text-gray-400 italic mt-1 mb-2">`+dateDisplay+`</p></div>
					`;
				}

				if (readingsData.latestMeasurements[1] == null) {
					// * latestTempCard
					//#region 
					var latestTempCard = `
						<div class="card-header-1">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="card-title-1">
								Latest Temp
							</div>
						</div>
		
						<div class="card-body-1">
							<p class="italic">No readings found...</p>
						</div>
						`;
					// Update values with latest temp
					$('#latestTempCard').html(latestTempCard);
					//#endregion

				} else {
					// * latestTempCard
					//#region
					var dateDisplay = new Date( readingsData.latestMeasurements[1].measurementTime );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });
					var latestTempCard = `
						<div id="ai2nd" class="card-header-1">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="card-title-1">
								`+readingsData.latestMeasurements[1].channelName+`
							</div>
						</div>
		
						<div class="card-body-1 flex-col space-y-2 py-4">
							<p class="font-medium text-gray-800"><span class="text-5xl tracking-tighter">`+readingsData.latestMeasurements[1].measurement+`</span> <span class="">`+readingsData.latestMeasurements[1].unitName+`</span></p>
							<p class="text-xs text-gray-400 italic mt-1 mb-2">`+dateDisplay+`</p>
						</div>
						`;
					// Update values with latest temp
					$('#latestTempCard').html(latestTempCard);
					$('#ai2nd').parent().addClass('border-lightblue-400');
					//#endregion
				}
			}

			$('#latestMeasurements_body').html(latestMeasurementsCard);
			//#endregion
			
			// * Total alarms widget card
			//#region
			var timestampDisplay = '';
			if (readingsData.latestAlarmSent != '') {
				var dateDisplay = new Date( readingsData.latestAlarmSent );
				dateDisplay.setHours(dateDisplay.getHours() + 1);
				dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });
				timestampDisplay = 'Latest: '+dateDisplay;
			}
			var totalAlarmsCard = `
			<div class="card-header-1">
				<div class="card-icon-1">
					<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
				</div>
				<div class="card-title-1">
					Alarms sent
				</div>
			</div>

			<div class="card-body-1 flex-col space-y-2 py-4">
				<p class="font-medium text-gray-800"><span class="text-5xl tracking-tighter">`+readingsData.numberOfTriggeredAlarms+`</span></p>
				<p class="text-xs text-gray-400 italic">`+timestampDisplay+`</p>
			</div>
		`
		$('#totalAlarmsCard').html(totalAlarmsCard);
		//#endregion 
		})

		// * Load alarms card
		//#region 
		var alarmPageNumber = 1;
		var alarmsPerPage = 5;
		var fromDate = null;
		var toDate = null;
		getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);

		// Alarm paging
		$('#next_alarms').on('click', function () {
			alarmPageNumber += 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})
		$('#previous_alarms').on('click', function () {
			alarmPageNumber -= 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})

		$('#AlarmCal').on('click', function() {
			$('#AlarmCalSelector').toggleClass('hidden');
		})
		$('#goAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = $('#alarmFrom').val();
			toDate = $('#alarmTo').val();
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
		})

		$('#resetAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = null;
			toDate = null;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
			$('#alarmFrom, #alarmTo').val("");
		})

		function displayTriggeredAlarms(triggeredAlarms) {
			var triggeredAlarmsOutput = '';
			for (i = 0; i < triggeredAlarms.length; i++) {
				triggeredAlarmsOutput += `
				<!-- Alarm -->
				<div class="flex h-10 text-sm text-red-600 font-medium items-center pl-4 bg-red-50 border border-red-500 rounded my-2">
					<div class="hidden xl:block flex-1 whitespace-nowrap mx-2 text-xs italic">
						ACTIVE ALARM!
					</div>
					<div class="flex-1 whitespace-nowrap">
						<span class="text-xs uppercase text-red-400 mr-1">Channel:</span> `+triggeredAlarms[i].channelName+`
					</div>
					<div class="flex-1 whitespace-nowrap text-center">
						<span class="text-xs uppercase text-red-400 mr-1">Trigger:</span> `+triggeredAlarms[i].operator+` `+triggeredAlarms[i].thresholdValue+` `+triggeredAlarms[i].unitName+`
					</div>
				</div>
				<!-- End of alarm -->
				`
			}
			$('#triggeredAlarms').html(triggeredAlarmsOutput);
		}
		// Display triggered alarms
		getTriggeredAlarms().then( function(triggeredAlarms) {
			displayTriggeredAlarms(triggeredAlarms);
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

		var activeClass = 'text-gray-800 bg-white ring-1 ring-gray-300';
		$('#dateSelectors').children().first().addClass(activeClass);
		$('#dateSelectors').children().on('click', function() {
			$('#dateSelectors').children().removeClass(activeClass);
			$(this).addClass(activeClass);
			var dataid = $(this).attr('data-id');
			if (dataid != 'ChartCal') {
				getDatasets(dataid,'NOW').then( function (data) {
					drawChart(chart, data);
				})

				if ( !$('#ChartCalSelector').hasClass('hidden') ) {
					$('#ChartCalSelector').addClass('hidden');
				}
				$('#chartFrom, #chartTo').val("");
			} else {
				$('#ChartCalSelector').toggleClass('hidden');
			}
		})
		$('#ChartCalSelector > button').on('click', function() {
			getDatasets($('#chartFrom').val() , $('#chartTo').val()).then( function (data) {
				drawChart(chart, data);
			})
			$('#ChartCalSelector').addClass('hidden');
		})
		//#endregion
		
	}		
	
	// ! SHOW COMPONENT: Display control panel
	function display_rtmu_controlPanel() {
		var output = `
		<div id="controlpanel-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
			<div id="modal-box" class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
			</div>
		</div>

		<div class="grid grid-cols-2 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-4 lg:gap-6">
			<!-- Card -->
			<div class="col-span-2 md:col-span-1 lg:col-span-2 xl:col-span-1 card-wrapper-1">
				<!-- Card header -->
				<div class="card-header-1">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="card-title-1">
						Device
					</div>
				</div>

				<!-- Card body -->
				<div class="card-body-1 justify-start items-stretch flex-col p-2 divide-y divide-gray-200">
					<div class="flex py-2">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Name</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="device-name"></span></div>
					</div>
					<div class="flex py-2">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Product</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="device-product"></span></div>
					</div>
					<div class="flex py-2">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Group</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="device-group"></span></div>
					</div>
					<div class="flex py-2">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">SIM</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="device-phone"></span></div>
					</div>
					<div class="flex py-2">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Channels</div>
						<div id="device-channels" class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800 space-y-1">
							
						</div>
					</div>
				</div>
			</div>
			<!-- End of card -->

			<!-- Card -->
			<div class="col-span-2 md:col-span-1 lg:col-span-2 xl:col-span-1 card-wrapper-1 bg-gray-50">
				<!-- Card header -->
				<div class="card-header-1">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="card-title-1">
						State
					</div>
				</div>

				<!-- Card body -->
				<div class="card-body-1 justify-start items-stretch flex-col p-2 space-y-4">

					<div class="border h-28 p-2 rounded-xl flex justify-between">
						<div class="flex justify-center items-center uppercase text-sm font-semibold text-gray-800 w-24">
							STATUS
						</div>

						<div id="statusButton" class="w-36 md:w-48 lg:w-36 flex flex-col">
							
						</div>
					</div>
				</div>
			</div>
			<!-- End of card -->

			<!-- Card -->
			<div class="col-span-2 md:col-span-1 lg:col-span-2 xl:col-span-1 card-wrapper-1 bg-gray-50">
				<!-- Card header -->
				<div class="card-header-1 relative">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
					</div>

					<!-- Edit button -->
					<button id="editMaintain" class="card-icon-1 rounded focus:outline-none bg-green-200 border border-green-400 text-green-600 transition hover:bg-green-300 cursor-pointer absolute right-0 p-1.5" title="Edit">
						<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
					</button>

					<div class="card-title-1">
						Maintain
					</div>
				</div>

				<!-- Card body -->
				<div class="card-body-1 justify-start items-stretch flex-col p-2">
					<div class="flex items-center py-2">
						<div class="flex-auto text-xs uppercase text-gray-500 text-center whitespace-nowrap underline">Subscription</div>
					</div>
					<div class="flex items-center h-10 border-b">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Start</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="maintain-subStart"></span></div>
					</div>
					<div class="flex items-center h-10">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Finish</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="maintain-subFinish"></span></div>
					</div>

					<div class="flex items-center py-2">
						<div class="flex-auto text-xs uppercase text-gray-500 text-center whitespace-nowrap underline">Calibration</div>
					</div>
					<div class="flex items-center h-10 border-b">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Last</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="maintain-lastCal"></span></div>
					</div>
					<div class="flex items-center h-10">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Next</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="maintain-nextCal"></span></div>
					</div>
				</div>
			</div>
			<!-- End of card -->

			<!-- Card -->
			<div class="col-span-2 md:col-span-1 lg:col-span-2 xl:col-span-1 card-wrapper-1 bg-gray-50">
				<!-- Card header -->
				<div class="card-header-1 relative">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
					</div>

					<!-- Edit button -->
					<button id="editCustom" class="card-icon-1 rounded focus:outline-none bg-green-200 border border-green-400 text-green-600 transition hover:bg-green-300 cursor-pointer absolute right-0 p-1.5" title="Edit">
						<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
					</button>

					<div class="card-title-1">
						Custom
					</div>
				</div>

				<!-- Card body -->
				<div class="card-body-1 justify-start items-stretch flex-col p-2">					
					<div class="flex items-center h-10 border-b">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Alias</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="custom-alias"></span></div>
					</div>
					<div class="flex items-center h-10 border-b">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Location</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="custom-location"></span></div>
					</div>
					<div class="flex items-center h-10 border-b">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Latitude</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="custom-latitude"></span></div>
					</div>
					<div class="flex items-center h-10">
						<div class="flex-none w-16 text-xs uppercase text-gray-500 pl-1 whitespace-nowrap">Longitude</div>
						<div class="flex-auto text-center whitespace-nowrap truncate text-sm font-semibold text-gray-800"><span id="custom-longitude"></span></div>
					</div>
					
				</div>
			</div>
			<!-- End of card -->

			<!-- Card -->
			<div id="mapCard" class="col-span-2 lg:col-span-4 card-wrapper-1">
				<!-- Card header -->
				<div class="card-header-1">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="card-title-1">
						Device Location
					</div>
				</div>

				<!-- Card body -->
				<div class="card-body-1 p-2" style="height: 26rem;">
					<div id="map" class="w-full h-full rounded"></div>
				</div>
			</div>
			<!-- End of card -->
		</div>
		`
		siteContent.html(output);

		// * Setup info cards
		getDeviceData().then( function(data) {
			// Device info card
			$('#device-name').html(data.deviceName);
			$('#device-product').html(data.productName);
			$('#device-group').html(data.groupName);
			$('#device-phone').html(data.devicePhone);

			for (var i = 0; i < data.channels.length; i++) {
				$('#device-channels').append('<p>'+data.channels[i].channelName+'</p>');
			}

			// Status button
			function displayStatusButton(state) {
				if (state == 1) {
					$('#statusButton').html(`
					<div id="forceOn" class="h-1/2 bg-green-100 border-2 border-green-400 rounded-t-2xl flex justify-center items-center">
						<p class="text-green-600 font-medium uppercase text-sm">On</p>
					</div>
					<div id="forceOff" class="h-1/2 flex border-2 border-t-0 rounded-b-xl justify-center items-center text-gray-400 cursor-pointer">
						<p class="text-gray-400 font-medium uppercase text-sm">Off</p>
					</div>`)
				} else {
					$('#statusButton').html(`
					<div id="forceOn" class="h-1/2 border-2 border-b-0 rounded-t-2xl flex justify-center items-center cursor-pointer">
						<p class="text-gray-400 font-medium uppercase text-sm">On</p>
					</div>
					<div id="forceOff" class="h-1/2 flex border-2 border-red-500 bg-red-100 rounded-b-xl justify-center items-center text-gray-400">
						<p class="text-red-600 font-medium uppercase text-sm">Off</p>
					</div>`)
				}
			}
			displayStatusButton(data.deviceStatus);
			$('#statusButton').delegate('#forceOn', 'click', function() {
				if(data.deviceStatus == 0) {
					data.deviceStatus = 1;
					updateDeviceStatus(data.deviceStatus);
					displayStatusButton(data.deviceStatus);
				}
			})
			$('#statusButton').delegate('#forceOff', 'click', function() {
				if(data.deviceStatus == 1) {
					data.deviceStatus = 0;
					updateDeviceStatus(data.deviceStatus);
					displayStatusButton(data.deviceStatus);
				}
			})

			// Buttons and modal box
			function showModalBox(id) {
				var modalBody = '';
				if (id == 'editCustom') {
					modalBody = `
					<!-- Title/close btn -->
					<div class="flex justify-between items-center border-b pb-1 border-gray-300">
						<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">Edit Custom Data</p>
						<svg id="close-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="w-full">
						<div class="flex flex-col">
							<!-- Row -->
							<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Alias</p>
									<input id="modal-alias" type="text" value="`+ $.trim(data.deviceAlias) +`" class="border border-gray-300">
								</div>
			
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Custom Location</p>
									<input id="modal-location" type="text" value="`+ $.trim(data.customLocation) +`" class="border border-gray-300">
								</div>
							</div>
							<!-- End of row -->

							<!-- Row -->
							<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Latitude</p>
									<input id="modal-latitude" type="text" value="`+ $.trim(data.latitude) +`" class="border border-gray-300">
								</div>
			
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Longitude</p>
									<input id="modal-longitude" type="text" value="`+ $.trim(data.longitude) +`" class="border border-gray-300">
								</div>
							</div>
							<!-- End of row -->

							<div class="flex justify-end items-center mt-4 space-x-4">
								<button id="cancelBtn" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Cancel</button>
								<button id="saveCustom" class="h-10 bg-green-500 border-0 hover:border-0 px-4 rounded text-white hover:bg-green-700 transition-all focus:bg-green-700 focus:text-white">Save</button>
							</div>
						</div>
					</div>
					`;
				}

				if (id == 'editMaintain') {
					modalBody = `
					<!-- Title/close btn -->
					<div class="flex justify-between items-center border-b pb-1 border-gray-300">
						<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">Edit Maintain Data</p>
						<svg id="close-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="w-full">
						<div class="flex flex-col">

							<div class="flex flex-auto justify-center mt-4">
								<p class="text-sm uppercase font-semibold text-gray-800">Subscription</p>
							</div>

							<!-- Row -->
							<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Start</p>
									<input id="modal-subStart" value="`+ data.subStart +`" type="date" class="border border-gray-300">
								</div>
			
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Finish</p>
									<input id="modal-subFinish" value="`+ data.subFinish +`" type="date" class="border border-gray-300">
								</div>
							</div>
							<!-- End of row -->
							
							<div class="flex flex-auto justify-center mt-8">
								<p class="text-sm uppercase font-semibold text-gray-800">Calibration</p>
							</div>

							<!-- Row -->
							<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Last</p>
									<input id="modal-lastCal" value="`+ data.lastCalibration +`" type="date" class="border border-gray-300">
								</div>
			
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">Next</p>
									<input id="modal-nextCal" value="`+ data.nextCalibrationDue +`" type="date" class="border border-gray-300">
								</div>
							</div>
							<!-- End of row -->

							<div class="flex justify-end items-center mt-4 space-x-4">
								<button id="cancelBtn" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Cancel</button>
								<button id="saveMaintain" class="h-10 bg-green-500 border-0 hover:border-0 px-4 rounded text-white hover:bg-green-700 transition-all focus:bg-green-700 focus:text-white">Save</button>
							</div>
						</div>
					</div>
					`;
				}

				$('#controlpanel-modal > #modal-box').html(modalBody);
				$('#controlpanel-modal').removeClass('hidden')
			}
			$('#editMaintain').hide();

			function toggleControlPanelModal() {
				$('#controlpanel-modal').toggleClass('hidden');
			}

			// Listen for cancel buttons
			$('#controlpanel-modal').delegate('#close-modal, #cancelBtn', 'click', function() {
				toggleControlPanelModal();
			});

			// Listen for save button
			$('#controlpanel-modal').delegate('#saveMaintain, #saveCustom', 'click', function() {
				id = $(this).attr('id');
				if (id == 'saveMaintain') {
					updateMaintainDates( $('#modal-subStart').val(), $('#modal-subFinish').val(), $('#modal-lastCal').val(), $('#modal-nextCal').val() ).then( function(response) {
						if (response.status == 'OK') {
							data.subStart = $.trim($('#modal-subStart').val());
							data.subFinish = $.trim($('#modal-subFinish').val());
							data.lastCalibration = $.trim($('#modal-lastCal').val());
							data.nextCalibrationDue = $.trim($('#modal-nextCal').val());
							displayMaintainCard();
							toggleControlPanelModal();
							alert('Updated successfully!');
						}
						if (response.status == 'Error') {
							alert(response.message);
						}
					})
				}

				if (id == 'saveCustom') {
					updateCustoms( $.trim($('#modal-alias').val()), $.trim($('#modal-location').val()), $.trim($('#modal-latitude').val()), $.trim($('#modal-longitude').val()) ).then( function(response) {
						if (response.status == 'OK') {
							data.deviceAlias = $.trim($('#modal-alias').val());
							data.customLocation = $.trim($('#modal-location').val());
							data.latitude = $.trim($('#modal-latitude').val());
							data.longitude = $.trim($('#modal-longitude').val());
							displayCustomCard();
							toggleControlPanelModal();
							// Update map
							if (data.latitude == null || data.latitude == '' || data.longitude == null || data.longitude == '') {
								$('#mapCard').hide();
							} else {
								setupMap(data.longitude, data.latitude, 'map');
							}

							alert('Updated successfully!');
						}
						if (response.status == 'Error') {
							alert(response.message);
						}
					});
				}
			});

			getRoleId().then( function(roleId) {
				if(roleId == 1 || roleId == 2) {
					$('#editMaintain').show();
				}
			})

			$('#editMaintain, #editCustom').on('click', function() {
				var id = $(this).attr('id');
				showModalBox(id);
			})

			function formatDate(date) {
				let dateParts = date.split("-");
				let jsDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substr(0,2));

				let d = new Date(jsDate);
				let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
				let mo = new Intl.DateTimeFormat('en', { month: 'short' }).format(d);
				let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d);

				let formattedDate = da+' '+mo+', '+ye;
				return formattedDate;
			}
			// Functions to display card values
			function displayMaintainCard() {
				if (data.subStart != null && data.subStart != '') {
					$('#maintain-subStart').html( formatDate(data.subStart) );
				} else {
					$('#maintain-subStart').html('-');
				}
				if (data.subFinish != null && data.subFinish != '') {
					$('#maintain-subFinish').html(formatDate(data.subFinish) );
				} else {
					$('#maintain-subFinish').html('-');
				}
				if (data.lastCalibration != null && data.lastCalibration != '') {
					$('#maintain-lastCal').html( formatDate(data.lastCalibration) );
				} else {
					$('#maintain-lastCal').html('-');
				}
				if (data.nextCalibrationDue != null && data.nextCalibrationDue != '') {
					$('#maintain-nextCal').html( formatDate(data.nextCalibrationDue) );
				} else {
					$('#maintain-nextCal').html('-');
				}
			}
			displayMaintainCard();

			function displayCustomCard() {
				if (data.deviceAlias != null && data.deviceAlias != '') {
					$('#custom-alias').html(data.deviceAlias);
				} else {
					$('#custom-alias').html('-');
				}
				if (data.customLocation != null && data.customLocation != '') {
					$('#custom-location').html(data.customLocation);
				} else {
					$('#custom-location').html('-');
				}
				if (data.latitude != null && data.latitude != '') {
					$('#custom-latitude').html(data.latitude);
				} else {
					$('#custom-latitude').html('-');
				}
				if (data.longitude != null && data.longitude != '') {
					$('#custom-longitude').html(data.longitude);
				} else {
					$('#custom-longitude').html('-');
				}
			}
			displayCustomCard();

			// * Setup map
			if (data.latitude == null || data.latitude == '' || data.longitude == null || data.longitude == '') {
				$('#mapCard').hide();
			} else {
				setupMap(data.longitude, data.latitude, 'map');
			}
		})


	}

	// ! SHOW COMPONENT: Display alarms
	function display_alarms(productRestriction) {
		// * Put all cards together and generate final output
		var output = `
		<div class="grid grid-cols-2 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-4 lg:gap-6">
			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
				<!-- Card header -->
				<div class="card-header-1">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="card-title-1">
						Manage Alarms
					</div>
				</div>

				<!-- Card body -->
				<div class="card-body-1 justify-start items-stretch">
					<!-- Side nav -->
					<div id="alarmsNav" class="flex-none flex flex-col w-14 md:w-20">

						<div class="flex flex-col justify-center items-center py-2 border-gray-200">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
							<p class="text-xs uppercase font-medium text-center mt-1">Alarms</p>
						</div>

						<div id="newAlarm" class="flex flex-col justify-center items-center py-2 border-gray-200">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path></svg>	<p class="text-xs uppercase font-medium text-center mt-1">New</p>
						</div>

						<div class="flex-auto border-bluegray-200 rounded-bl-xl" style="min-height: 8rem">
						<!-- Filler -->
						</div>
					</div>
					<!-- End of sidenav -->

					<!-- Card content -->
					<div id="alarmSections" class="flex-auto flex flex-col">
						<!-- List of alarms -->
						<div id="alarmTriggersDiv" class="px-4 pt-1 pb-4 space-y-2">
						</div>
						<!-- End of alarms list -->

						<!-- New alarm -->
						<div id="newAlarmDiv">	
							
						</div>
						<!-- End of new alarm -->
					</div>
					<!-- End of card content -->
				</div>
			</div>
			<!-- End of card-->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
				<!-- Card header -->
				<div class="card-header-1 relative">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>	
					</div>
					<div class="card-title-1">
						Alarms	
					</div>

					<div id="AlarmCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800 ring-gray-300 hover:ring-1 absolute right-4">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
					</div>
					<div id="AlarmCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
						<input id="alarmFrom" type="date" class="bg-white text-gray-700">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
						<input id="alarmTo" type="date" class="bg-white text-gray-700">
						<div class="flex justify-center items-center">
							<button id="goAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
							<button id="resetAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
						</div>
					</div>
				</div>

				<!-- Table -->
				<div id="alarmsCardBody" class="card-body-1 justify-start items-stretch block py-2 px-4">
					<div id="triggeredAlarms">
					
					</div>
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-600">Channel</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600">Alarm</th>
									<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600">Timestamp</th>
								</tr>
							</thead>
							<tbody id="table_alarms">
								<!-- This area gets filled via js -->
							</tbody>
						</table>
					</div>
					<div id="loadingOverlay_alarms" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
						<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
						<p>Loading...</p>
					</div>

					<div class="flex flex-col items-center justify-center py-4">
						<div class="flex">
							<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
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

		// * Tab behaviour
		//#region 
		var kids = $('#alarmsNav').children();
		kids = kids.slice(0, kids.length-1);
		var fillerTab = $('#alarmsNav').children().last();

		// Tab behaviour
		var activeAlarmTab = 'bg-gray-50 cursor-default text-lightblue-500';
		var inactiveAlarmTab = 'bg-gray-100 border-r cursor-pointer text-gray-500 hover:text-gray-800';
		var topSiblingClass = 'border-b rounded-br-lg';
		var bottomSiblingClass = 'border-t rounded-tr-lg';

		fillerTab.addClass('bg-gray-100 border-r');
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
		//#endregion

		function fillAlarmTriggerTable(data) {
			var alarmTriggersDiv = `
				<div class="flex h-10 text-xs font-medium text-gray-400 items-center border-b sm:pl-4">
					<div class="flex-1 mx-2">
						CHANNEL
					</div>
					<div class="flex-1 mx-2">
						TRIGGER <span class="hidden">VALUE</span>
					</div>
					<div class="flex-1 mx-2">
						ALARM
					</div>
					<div class="flex-1 mx-2">
						
					</div>
				</div>
				`;
				
			if ( data.alarmTriggers != 'EMPTY') {
				for (i = 0; i < data.alarmTriggers.length; i++) {
					if (data.alarmTriggers[i].alarmDescription == null) { data.alarmTriggers[i].alarmDescription = '-'}
					alarmTriggersDiv += `
						<!-- Alarm -->
						<div class="flex h-10 text-xs sm:text-sm text-gray-700 font-medium items-center sm:pl-4 lg:pl-0 2xl:pl-4 border bg-gray-100">
							<div class="flex-1 whitespace-nowrap mx-2">
								`+data.alarmTriggers[i].channelName+`
							</div>
							<div class="flex-1 mx-2">
								`+data.alarmTriggers[i].operator+``+data.alarmTriggers[i].thresholdValue+``+data.alarmTriggers[i].unitName+`
							</div>
							<div class="flex-1 mx-2 truncate">
								`+data.alarmTriggers[i].alarmDescription+`
							</div>
							<div class="flex-1 flex justify-center sm:mx-2">
								<div id="deleteTrigger" data-id="`+data.alarmTriggers[i].triggerId+`" class="flex items-center bg-red-500 shadow text-white py-1 rounded px-2 cursor-pointer hover:bg-red-600" title="Delete trigger">
									<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
									<p class="hidden sm:block lg:hidden xl:block ml-2 uppercase text-xs">Delete</p>
								</div>
							</div>
						</div>
						<!-- End of alarm -->
					`;
				}
			}
			$('#alarmTriggersDiv').html(alarmTriggersDiv);
		}

		getAlarmTriggers().then( function(data) {
			// * Fill newAlarm div
			//#region 
			var newAlarmDivOutput = `
			<div class="px-2 lg:px-4 border-r rounded-b-xl border-gray-200">
				<!-- Form -->
				<form id="alarmForm" method="post" autocomplete="off" class="flex flex-col max-w-4xl">
					<!-- Row -->
					<p class="form-field-title text-center border-b">New Alarm</p>
					<!-- End of row -->

					<!-- Row -->
					<div class="flex my-1">
						<div class="flex flex-col flex-1">
							<p class="form-field-title">Device<span class="text-red-500">*</span></p>
							<input type="text" value="`+data.deviceName+`" disabled id="deviceName" required class="text-field-input">
						</div>
					</div>
					<!-- End of Row -->

					<!-- Row -->
					<div class="flex my-1">
						<div class="flex flex-col flex-1">
							<p class="form-field-title">Channel<span class="text-red-500">*</span></p>
							<select id="channelSelect" class="select-field-input">
							<option data-id="-1" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected disabled>Select channel</option>`;
			
			for (i = 0; i < data.channels.length; i++) {
				newAlarmDivOutput += '<option data-id="'+data.channels[i].channelId+'" data-unit="'+data.channels[i].unitName+'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">'+data.channels[i].channelName+'</option>';
			}

			newAlarmDivOutput += `
							</select>
						</div>
					</div>
					<!-- End of Row -->

					<!-- Row -->
					<div class="flex flex-col lg:flex-row">
						<div class="flex flex-col flex-1 my-1">
							<p class="form-field-title">Description<span class="text-red-500">*</span></p>
							<div class="flex flex-col justify-center text-sm font-medium space-y-2">
								<div class="flex space-x-2"><input type="radio" name="radio-desc" value="CRT(W)"><p>CRT(W)</p></div>
								<div class="flex space-x-2"><input type="radio" name="radio-desc" value="CRT(30/60)"><p>CRT(30/60)</p></div>
								<div class="flex space-x-2"><input type="radio" name="radio-desc" value="CRT(20)"><p>CRT(20)</p></div>
								<div class="flex items-center space-x-2">
									<input type="radio" name="radio-desc" value="Other">
									<input id="other-desc" type="text" class="h-8 bg-white disabled:bg-gray-100 px-2" style="max-width: 16rem;" placeholder="Other..." disabled>
								</div>
							</div>
						</div>
					</div>
					<!-- End of Row -->

					<!-- Row -->
					<div class="flex flex-col lg:flex-row">
						<div class="flex flex-col flex-1 my-1">
							<p class="form-field-title">Operator<span class="text-red-500">*</span></p>
							<select id="operatorSelect" class="select-field-input">
								<option data-id=">" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected>Greater than (>)</option>
								<option data-id=">=" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Greater than or equals (>=)</option>
								<option data-id="==" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Equals (==)</option>
								<option data-id="<" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Less than (<)</option>
								<option data-id="<=" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">Less than or equals (<=)</option>
							</select>
						</div>
						<div class="flex flex-col flex-1 lg:ml-4 my-1">
							<p class="form-field-title">Value<span class="text-red-500">*</span></p>
							<div class="flex space-x-0">
								<input type="text" id="alarmValue" placeholder="99"  required spellcheck="false" autocomplete="none" class="text-field-input rounded-r-none">
								<div id="unitField" class="flex justify-center items-center border border-l-0 h-10 px-4 text-sm font-semibold text-bluegray-800 bg-bluegray-100 rounded-r ml-2">
									<p></p>
								</div>
							</div>
						</div>
					</div>
					<!-- End of Row -->

					<!-- Row -->
					<div class="flex justify-center items-center my-8">
						<button type="submit" id="submit" name="submit" title="Create" class="focus:outline-none flex items-center justify-center border border-transparent bg-lightblue-500 transition-all hover:bg-lightblue-600 text-lightblue-100 hover:border-lightblue-500 hover:text-white space-x-2 font-semibold uppercase text-sm h-10 w-40 rounded shadow">
							<p>Create</p>
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></i>
						</button>
					</div>
					<!-- End of row -->
				</form>
			</div>`;
			//#endregion
			$('#newAlarmDiv').html(newAlarmDivOutput)
			
			// * Fill alarmTriggers div
			fillAlarmTriggerTable(data);
			
			$('#unitField').hide();
			$('#channelSelect').on('change', function() {
				if( $(this).find(':selected').attr('data-unit') != 'null') {
					$('#unitField').show();
					$('#unitField').html( $(this).find(':selected').attr('data-unit') );
				} else {
					$('#unitField').hide();
					$('#unitField').html("");
				}
			})

			$('input[type=radio][name=radio-desc]').on('change', function() {
				if ( $(this).val() == 'Other' ) {
					$('#other-desc').prop('disabled', false);
				} else {
					$('#other-desc').prop('disabled', true);
				}
			})

			$('#alarmTriggersDiv').delegate('#deleteTrigger', 'click', function(e) {
				var triggerId = $(this).attr('data-id');
				if (confirm("Are you sure you want to delete this alarm?")) {
					deleteTrigger(triggerId);
					// Update alarm triggers table
					getAlarmTriggers().then( function(data) {
						fillAlarmTriggerTable(data);
					})
				}
			})

			// Register new trigger...
			$('#alarmForm').on('submit', function(e) {
				e.preventDefault();
				var channelId = $('#channelSelect').find(':selected').attr('data-id');
				var operator = $('#operatorSelect').find(':selected').attr('data-id');
				var thresholdValue = $('#alarmValue').val();
				if ($('input[type=radio][name=radio-desc]:checked').val() == 'Other') {
					var alarmDescription = $('#other-desc').val();
				} else {
					var alarmDescription = $('input[type=radio][name=radio-desc]:checked').val();
				}
				if (registerNewTrigger(channelId, operator, thresholdValue, alarmDescription)) {
					alert('Alert has been successfully registered!');
					$('#alarmForm').trigger("reset");
					// Update alarm triggers table
					getAlarmTriggers().then( function(data) {
						fillAlarmTriggerTable(data);
					})
				} else {
					alert('Error');
				}
			})

		})

		// * Load alarms card
		//#region 
		var alarmPageNumber = 1;
		var alarmsPerPage = 5;
		var fromDate = null;
		var toDate = null;
		getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);

		// Alarm paging
		$('#next_alarms').on('click', function () {
			alarmPageNumber += 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})
		$('#previous_alarms').on('click', function () {
			alarmPageNumber -= 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})

		$('#AlarmCal').on('click', function() {
			$('#AlarmCalSelector').toggleClass('hidden');
		})
		$('#goAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = $('#alarmFrom').val();
			toDate = $('#alarmTo').val();
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
		})

		$('#resetAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = null;
			toDate = null;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
			$('#alarmFrom, #alarmTo').val("");
		})

		function displayTriggeredAlarms(triggeredAlarms) {
			var triggeredAlarmsOutput = '';
			for (i = 0; i < triggeredAlarms.length; i++) {
				triggeredAlarmsOutput += `
				<!-- Alarm -->
				<div class="flex h-10 text-sm text-red-600 font-medium items-center pl-4 bg-red-50 border border-red-500 rounded my-2">
					<div class="hidden xl:block flex-1 whitespace-nowrap mx-2 text-xs italic">
						ACTIVE ALARM!
					</div>
					<div class="flex-1 whitespace-nowrap mx-2">
						<span class="text-xs uppercase text-red-400 mr-1">Channel:</span> `+triggeredAlarms[i].channelName+`
					</div>
					<div class="flex-1 whitespace-nowrap mx-2 text-center">
						<span class="text-xs uppercase text-red-400 mr-1">Trigger:</span> `+triggeredAlarms[i].operator+` `+triggeredAlarms[i].thresholdValue+` `+triggeredAlarms[i].unitName+`
					</div>
				</div>
				<!-- End of alarm -->
				`
			}
			$('#triggeredAlarms').html(triggeredAlarmsOutput);
		}
		// Display triggered alarms
		getTriggeredAlarms().then( function(triggeredAlarms) {
			displayTriggeredAlarms(triggeredAlarms);
		})
		//#endregion
		
		if(productRestriction == 'ewbv2' ) {
			getRoleId().then(function(roleId) {
				if(roleId != 1 && roleId != 2) {
					$('#newAlarm').off('click');
					$('#newAlarm').addClass('cursor-not-allowed');
				}
			})
		}
	}

	// ! SHOW COMPONENT: Display recipients
	function display_recipients() {
		function outputAllUsers(data) {
			var allUsersDivOutput = '';
			if (data == 'ERROR') {
				allUsersDivOutput = '<p class="text-center text-sm uppercase font-semibold bg-gray-50 rounded-full py-2 text-gray-600">No users to show...</p>';
			} else {
				for (i = 0; i < data.length; i++) {
					allUsersDivOutput += `
						<!-- User -->
						<div class="h-10 pl-4 md:pl-0 flex justify-start md:justify-center max-w-xl mx-auto items-center text-xs sm:text-sm text-gray-700 font-medium border bg-gray-100 relative overflow-ellipsis overflow-hidden">
							`+data[i].fullName+` <span class="hidden md:block text-xs text-gray-400 font-normal md:ml-2 "> &lt;`+data[i].email+`&gt; </span>
	
							<div id="addRecipient" data-id="`+data[i].userId+`" class="flex items-center bg-lightblue-500 shadow text-white py-1 rounded px-2 cursor-pointer hover:bg-lightblue-600 absolute right-2" title="Add recipient">
								<svg class="w-4 sm:w-5 h-4 sm:h-5 md:pr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path></svg>
								<p class="hidden md:block pl-1 border-l-2 border-lightblue-600 text-xs">Select</p>
							</div>
						</div>
						<!-- End of user -->
					`;
				}
			}
			$('#allUsersDiv').html(allUsersDivOutput);
		}

		function outputRecipients(data) {
			var recipientsDivOutput = '';
			if (data == 'ERROR') {
				recipientsDivOutput = '<p class="text-center text-sm uppercase font-semibold bg-gray-50 rounded-full py-2 text-gray-600">No assigned recipients...</p>';
			} else {
				for (i = 0; i < data.length; i++) {
					recipientsDivOutput += `
						<!-- User -->
						<div class="text-xs sm:text-sm h-10 pl-4 md:pl-0 flex justify-start md:justify-center max-w-xl mx-auto items-center text-gray-700 font-medium border bg-gray-100 relative overflow-ellipsis overflow-hidden">
							`+data[i].fullName+` <span class="hidden md:block text-xs text-gray-400 font-normal md:ml-2 "> &lt;`+data[i].email+`&gt; </span>
	
							<div id="deleteRecipient" data-id="`+data[i].alarmRecipientId+`" class="flex items-center bg-red-500 shadow text-white py-1 rounded px-2 cursor-pointer hover:bg-red-600 absolute right-2" title="Add recipient">
								<svg class="w-4 sm:w-5 h-4 sm:h-5 md:pr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
								<p class="hidden md:block pl-1 border-l-2 border-red-600 text-xs">Remove</p>
							</div>
						</div>
						<!-- End of user -->
					`;
				}
			}
			$('#recipientsDiv').html(recipientsDivOutput);
		}

		function populateBothLists() {
			getAllUsers().then( function (userList) {
				getRecipients().then( function (recipientList) {
					// Display recipients list
					outputRecipients(recipientList);

					// Remove registered recipients from users list and display it
					for(i = 0; i < userList.length; i++) {
						for(j = 0; j < recipientList.length; j++) {
							if (userList[i].userId == recipientList[j].userId && userList[i].email == recipientList[j].email) {
								userList.splice(i, 1);
							}
						}
					}

					outputAllUsers(userList);
					updateSearchContent(userList);
				})
			})
		}

		function updateSearchContent(data) {
			$('#userSearchBar').keyup( function(e) {
				var searchString = e.target.value.toUpperCase();
				var filteredUsers = data.filter(user => {
					return user.fullName.toUpperCase().includes(searchString) || user.email.toUpperCase().includes(searchString);
				})
				outputAllUsers(filteredUsers);
			})
		}

		output = `
		<div class="grid grid-cols-2 card-wrapper-1">
			<!-- Card -->
			<div class="col-span-2 grid grid-cols-2">
				<!-- Left card -->
				<div class="flex flex-col" style="height: 36rem;">
					<!-- Card header -->
					<div class="card-header-1">
						<div class="card-icon-1">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
						</div>
						<div class="card-title-1">
							Selected recipients	
						</div>
					</div>

					<!-- Left body -->
					<div id="recipientsDiv" class="card-body-1 justify-start items-stretch block p-4 space-y-2 h-full overflow-y-auto">
						<!-- Filled via js -->
					</div>
					<!-- End of left body -->

				</div>
				<!-- End of left card -->

				<!-- Right card -->
				<div class="flex flex-col border-l border-gray-300 relative" style="height: 36rem;">
					<!-- Card header -->
					<div class="card-header-1">
						<div class="card-icon-1">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"></path><path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z"></path></svg>
						</div>
						<div class="card-title-1">
							User list
						</div>
					</div>
					
					<!-- Right body -->
					<div class="py-4 px-2 md:p-4 overflow-y-auto space-y-4 text-center h-full rounded-br-xl bg-gray-50">
					<div class="card-body-1 flex-initial justify-start items-stretch block px-2 md:p-4 h-full overflow-y-auto space-y-4">
						<input type="text" id="userSearchBar" placeholder="Search..." spellcheck="false" autocomplete="none" class="outline-none h-10 w-full max-w-xl px-4 text-sm font-semibold text-gray-800 bg-gray-100 transition-all focus:bg-gray-50 border rounded">
						
						<div class="border-b"></div>

						<div id="allUsersDiv" class="space-y-2">
						
						</div>
						<!-- Filled via js -->
					</div>
					<!-- End of right body -->
					
				</div>
				<!-- End of right card -->

			</div>
			<!-- End of card-->		
		</div>
		`;
		siteContent.html(output);

		// * Display all users on load
		populateBothLists();

		$('#allUsersDiv').delegate( '#addRecipient', 'click', function() {
			var userId = $(this).attr('data-id');
			addNewRecipient(userId).then( function() {
				populateBothLists();
			})
		})

		$('#recipientsDiv').delegate('#deleteRecipient', 'click', function() {
			var recipientId = $(this).attr('data-id');
			deleteRecipient(recipientId).then( function() {
				populateBothLists();
			})
		})
	}

	// ! SHOW COMPONENT: Display log
	function display_log() {
		var output = `
		<div class="grid grid-cols-2">
			<!-- Card -->
			<div class="col-span-2 lg:col-span-1 card-wrapper-1">
				<!-- Card header -->
				<div class="card-header-1 relative">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="card-title-1">
						Measurements log
					</div>

					<div id="logCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-50 ring-gray-300 hover:ring-1 hover:text-gray-800 absolute right-4">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
					</div>
					<div id="logCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
						<input id="logFrom" type="date" class="bg-white text-gray-700">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
						<input id="logTo" type="date" class="bg-white text-gray-700">
						<div class="flex justify-center items-center">
							<button id="goLogCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
							<button id="resetLogCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
						</div>
					</div>
				</div>

				<!-- Table -->
				<div class="card-body-1 block items-stretch justify-start py-2 px-4">
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-4/12 py-2 px-4 font-medium text-gray-600">Channel</th>
									<th class="text-center w-2/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600">Reading</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600">Timestamp</th>
								</tr>
							</thead>
							<tbody id="table_measurements">
								<!-- This area gets filled via js -->
							</tbody>
						</table>
					</div>
					<div id="loadingOverlay_measurements" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
						<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
						<p>Loading...</p>
					</div>

					<div class="flex flex-col items-center justify-center py-4">
						<div class="flex">
							<button id="previous_measurements" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_measurements" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
						</div>
						<p class="mt-4 text-xs font-semibold">Showing <span id="range_measurements"></span> of <span id="total_measurements"></span></p>
					</div>
				</div>
			</div>
		</div>
		`;
		siteContent.html(output);

		var measurementPageNumber = 1;
		var measurementsPerPage = 10;

		var measurementsFromDate = null;
		var measurementsToDate = null;
	
		// Show measurements on load
		getMeasurementsLog(measurementsPerPage, measurementPageNumber, 'table_measurements', measurementsFromDate, measurementsToDate);
	
		$('#next_measurements').on('click', function () {
			measurementPageNumber += 1;
			getMeasurementsLog(measurementsPerPage, measurementPageNumber, 'table_measurements', measurementsFromDate, measurementsToDate);
		})
	
		$('#previous_measurements').on('click', function () {
			measurementPageNumber -= 1;
			getMeasurementsLog(measurementsPerPage, measurementPageNumber, 'table_measurements', measurementsFromDate, measurementsToDate);
		})

		$('#logCal').on('click', function() {
			$('#logCalSelector').toggleClass('hidden');
		})
		$('#goLogCal').on('click', function() {
			measurementPageNumber = 1;
			measurementsFromDate = $('#logFrom').val();
			measurementsToDate = $('#logTo').val();
			getMeasurementsLog(measurementsPerPage, measurementPageNumber, 'table_measurements', measurementsFromDate, measurementsToDate);
			$('#logCalSelector').addClass('hidden');
		})

		$('#resetLogCal').on('click', function() {
			measurementPageNumber = 1;
			measurementsFromDate = null;
			measurementsToDate = null;
			getMeasurementsLog(measurementsPerPage, measurementPageNumber, 'table_measurements', measurementsFromDate, measurementsToDate);
			$('#logCalSelector').addClass('hidden');
			$('#logFrom, #logTo').val("");
		})
	}

	// ! SHOW COMPONENT: EWB Dashboard
	function display_ewb_dashboard() {
		// * Put all cards together and generate final output
		var output = `
		<div class="grid grid-cols-1 gap-4 lg:grid-cols-5 lg:gap-6">
			<!-- Left panel -->
			<div id="channelDisplay" class="lg:col-span-2 space-y-4 lg:space-y-6">
				
			</div>

			<!-- Right panel -->
			<div class="lg:col-span-3">
				<!-- Card -->
				<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
					<div class="card-header-1 relative">
						<div class="card-icon-1">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd"></path></svg>
						</div>
						<div class="card-title-1">
							Message history	
						</div>

						<div id="messageHistoryCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800 absolute right-4">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
						</div>
						<div id="messageHistoryCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
							<input id="messageHistoryFrom" type="date" class="bg-white text-gray-700">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
							<input id="messageHistoryTo" type="date" class="bg-white text-gray-700">
							<div class="flex justify-center items-center">
								<button id="gomessageHistoryCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
								<button id="resetmessageHistoryCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
							</div>
						</div>
					</div>

					<!-- Table -->
					<div id="messageHistoryCardBody" class="card-body-1 block items-stretch justify-start py-2 px-4">
						<div class="flex overflow-x-auto">
							<table class="table-fixed min-w-full">
								<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
									<tr>
										<th class="text-center w-1/12 py-4 px-4 font-medium text-gray-600 whitespace-nowrap">From</th>
										<th class="text-center w-1/12 py-4 px-4 font-medium text-gray-600 whitespace-nowrap">To</th>
										<th class="text-center w-6/12 py-4 px-4 font-medium text-gray-600 whitespace-nowrap">Message</th>
										<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-600 whitespace-nowrap">Timestamp</th>
										<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-600 whitespace-nowrap">Message Type</th>
									</tr>
								</thead>
								<tbody id="table_messageHistory">
									<!-- This area gets filled via PHP -->
								</tbody>
							</table>
						</div>
						<div id="loadingOverlay_messageHistory" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
							<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
							<p>Loading...</p>
						</div>

						<div class="flex flex-col items-center justify-center py-4">
							<div class="flex">
								<button id="previous_messageHistory" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
								<button id="next_messageHistory" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
							</div>
							<p class="mt-4 text-xs font-semibold">Showing <span id="range_messageHistory"></span> of <span id="total_messageHistory"></span></p>
						</div>
					</div>
				</div>
				<!-- End of card-->
			</div>	

			<!-- End of card-->
		</div>
		`;
		// * Update site content once its generated
		siteContent.html(output);
		// Update left side with device status and channels
		getEWBChannels().then( function(channelData) {
			channelDisplay = '';
			getDeviceData().then( function(deviceData) {
				var status = deviceData.deviceStatus
				if (status == 1) {
					channelDisplay += `
					<!-- Card -->
					<div class="col-span-1">
						<div class="bg-green-50 shadow-md border border-green-400 h-24 flex justify-center items-center relative overflow-hidden">
							<div class="text-green-300 opacity-40 transform left-0 -rotate-12 ml-4 absolute overflow-hidden">
								<svg class="w-44 h-44" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="z-10 text-center">
								<p class="font-semibold text-sm tracking-wider text-green-800">STATUS</p>
								<p class="font-bold text-3xl text-green-900">ONLINE</p>
							</div>
						</div>
					</div>
					<!-- End of card -->
					`
				} else {
					channelDisplay += `
					<!-- Card -->
					<div class="col-span-1">
						<div class="bg-red-50 shadow-md border border-red-400 h-24 flex justify-center items-center relative overflow-hidden">
							<div class="text-red-300 opacity-40 transform left-0 -rotate-12 ml-4 absolute overflow-hidden">
								<svg class="w-44 h-44" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3.707 2.293a1 1 0 00-1.414 1.414l6.921 6.922c.05.062.105.118.168.167l6.91 6.911a1 1 0 001.415-1.414l-.675-.675a9.001 9.001 0 00-.668-11.982A1 1 0 1014.95 5.05a7.002 7.002 0 01.657 9.143l-1.435-1.435a5.002 5.002 0 00-.636-6.294A1 1 0 0012.12 7.88c.924.923 1.12 2.3.587 3.415l-1.992-1.992a.922.922 0 00-.018-.018l-6.99-6.991zM3.238 8.187a1 1 0 00-1.933-.516c-.8 3-.025 6.336 2.331 8.693a1 1 0 001.414-1.415 6.997 6.997 0 01-1.812-6.762zM7.4 11.5a1 1 0 10-1.73 1c.214.371.48.72.795 1.035a1 1 0 001.414-1.414c-.191-.191-.35-.4-.478-.622z"></path></svg>
							</div>
							<div class="z-10 text-center">
								<p class="font-semibold text-sm tracking-wider text-red-800">STATUS</p>
								<p class="font-bold text-3xl text-red-900">OFFLINE</p>
							</div>
						</div>
					</div>
					<!-- End of card -->
					`
				}

				// Display DI channel
				if( channelData['DI'] != null ) {
					var dateDisplay = new Date( channelData['DI']['smsAlarmTime'] );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					// Extra processing for red warnings
					redBorder = '';
					redText = '';
					defaultColor = 'lightblue'
					if ( channelData['DI']['smsAlarmHeader'].toUpperCase() == 'FALLEN DOWN' || channelData['DI']['smsAlarmHeader'].toUpperCase() == 'FALLEN OVER') {
						redBorder = 'border border-red-400';
						redText = 'text-red-600';
						defaultColor = 'red';
					}

					channelDisplay += `
					<!-- Card -->
						<div class="col-span-1 card-wrapper-1 `+redBorder+`">
							<div class="card-header-1">
								<div class="card-icon-1 text-`+defaultColor+`-500">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
								</div>
								<div class="card-title-1 text-`+defaultColor+`-800 bg-`+defaultColor+`-100">
									`+channelData['DI']['channelName']+`
								</div>
							</div>
							<div class="card-body-1 flex-col py-4">
								<p class="text-2xl xl:text-3xl font-semibold text-gray-800 text-center uppercase whitespace-nowrap `+redText+`">`+channelData['DI']['smsAlarmHeader']+`<br>
								<p class="text-xs text-gray-400 italic mt-4">`+dateDisplay+`</p>
							</div>
						</div>
						<!-- End of card -->
					`
				} else {
					channelDisplay += `
						<!-- Card -->
							<div class="col-span-1 card-wrapper-1">
								<div class="card-header-1">
									<div class="card-icon-1">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1">
										EWB BOARD
									</div>
								</div>
								<div class="card-body-1">
									<p class="italic my-4">No alarms found...</p>
								</div>
							</div>
							<!-- End of card -->
						`
				}

				// Display AI channel
				if( channelData['AI'] != null ) {
					var dateDisplay = new Date( channelData['AI']['smsAlarmTime'] );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					unitDisplay = '';
					if (channelData['AI']['unitName'] != null) { 
						unitDisplay = channelData['AI']['unitName'];
					}

					alarmReadingDisplay = '';
					if (channelData['AI']['smsAlarmReading'] != null) { 
						alarmReadingDisplay = channelData['AI']['smsAlarmReading'];
					}

					// Extra processing for red warnings
					redBorder = '';
					redText = '';
					defaultColor = 'lightblue'
					if ( channelData['AI']['smsAlarmHeader'].toUpperCase() == 'BOARD LIGHTS OUT' ||
						channelData['AI']['smsAlarmHeader'].toUpperCase() == 'ESR BOARD LIGHTS OUT' ||
						channelData['AI']['smsAlarmHeader'].toUpperCase() == 'RECHARGE BATTERY'
					) {
						redBorder = 'border border-red-400';
						redText = 'text-red-600';
						defaultColor = 'red';
					}

					channelDisplay += `
					<!-- Card -->
						<div class="col-span-1 card-wrapper-1 `+redBorder+`">
							<div class="card-header-1">
								<div class="card-icon-1 text-`+defaultColor+`-500 lg:hidden xl:block">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
								</div>
								<div class="card-title-1 text-`+defaultColor+`-800 bg-`+defaultColor+`-100">
									VOLTAGE MONITOR
								</div>
							</div>
							<div class="card-body-1 flex-col py-4">
								<p class="text-2xl xl:text-3xl font-semibold text-gray-800 text-center uppercase whitespace-nowrap `+redText+`">`+channelData['AI']['smsAlarmHeader']+`<br>
								<span class="text-2xl font-medium lg:text-xl">`+alarmReadingDisplay+` `+unitDisplay+`</span></p>
								<p class="text-xs text-gray-400 italic mt-4">`+dateDisplay+`</p>
							</div>
						</div>
					<!-- End of card -->
					`
				} else {
					channelDisplay += `
						<!-- Card -->
							<div class="col-span-1 card-wrapper-1">
								<div class="card-header-1">
									<div class="card-icon-1">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1">
										VOLTAGE MONITOR
									</div>
								</div>
								<div class="card-body-1">
									<p class="italic my-4">No alarms found...</p>
								</div>
							</div>
							<!-- End of card -->
						`
				}

				// for (i = 0; i < channelData.length; i++) {
				// 	if ( channelData[i]['reading'] != null ) {
				// 		var dateDisplay = new Date( channelData[i]['reading']['smsAlarmTime'] );
				// 		dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });
						
				// 		unitDisplay = '';
				// 		if (channelData[i]['reading']['unitName'] != null) { 
				// 			unitDisplay = channelData[i]['reading']['unitName'];
				// 		}
	
				// 		alarmReadingDisplay = '';
				// 		if (channelData[i]['reading']['smsAlarmReading'] != null) { 
				// 			alarmReadingDisplay = channelData[i]['reading']['smsAlarmReading'];
				// 		}
	
				// 		// Extra processing for red warnings
				// 		redBorder = '';
				// 		redText = '';
				// 		defaultColor = 'lightblue'
				// 		if ( channelData[i]['reading']['smsAlarmHeader'].toUpperCase() == 'FALLEN DOWN' ||
				// 			channelData[i]['reading']['smsAlarmHeader'].toUpperCase() == 'BOARD LIGHTS OUT'	
				// 		) {
				// 			redBorder = 'border border-red-400';
				// 			redText = 'text-red-600';
				// 			defaultColor = 'red';
				// 		}
	
				// 		channelDisplay += `
				// 		<!-- Card -->
				// 			<div class="col-span-1 card-wrapper-1 `+redBorder+`">
				// 				<div class="card-header">
				// 					<div class="card-icon-1 text-`+defaultColor+`-500">
				// 						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
				// 					</div>
				// 					<div class="card-title-1 text-`+defaultColor+`-800 bg-`+defaultColor+`-100">
				// 						`+channelData[i]['channelName']+`
				// 					</div>
				// 				</div>
				// 				<div class="card-body-1 py-4">
				// 					<p class="text-2xl xl:text-3xl font-semibold text-gray-800 text-center uppercase whitespace-nowrap `+redText+`">`+channelData[i]['reading']['smsAlarmHeader']+`<br>
				// 					<span class="text-2xl font-medium lg:text-xl">`+alarmReadingDisplay+` `+unitDisplay+`</span></p>
				// 					<p class="text-xs text-gray-400 italic mt-4">`+dateDisplay+`</p>
				// 				</div>
				// 			</div>
				// 			<!-- End of card -->
				// 		`
				// 	} else {
				// 		channelDisplay += `
				// 		<!-- Card -->
				// 			<div class="col-span-1 card-wrapper-1">
				// 				<div class="card-header-1">
				// 					<div class="card-icon-1">
				// 						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
				// 					</div>
				// 					<div class="card-title-1">
				// 						`+channelData[i]['channelName']+`
				// 					</div>
				// 				</div>
				// 				<div class="card-body-1">
				// 					<p class="italic my-4">No alarms found...</p>
				// 				</div>
				// 			</div>
				// 			<!-- End of card -->
				// 		`
				// 	}
				// }

				$('#channelDisplay').html(channelDisplay);

				// Display message history
				//#region 
				var messageHistoryPageNumber = 1;
				var messageHistoryPerPage = 10;
				var fromDate = null;
				var toDate = null;
				getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);

				// Alarm paging
				$('#next_messageHistory').on('click', function () {
					messageHistoryPageNumber += 1;
					getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
				})
				$('#previous_messageHistory').on('click', function () {
					messageHistoryPageNumber -= 1;
					getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
				})

				$('#messageHistoryCal').on('click', function() {
					$('#messageHistoryCalSelector').toggleClass('hidden');
				})
				$('#gomessageHistoryCal').on('click', function() {
					alarmPageNumber = 1;
					fromDate = $('#messageHistoryFrom').val();
					toDate = $('#messageHistoryTo').val();
					getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
					$('#messageHistoryCalSelector').addClass('hidden');
				})

				$('#resetmessageHistoryCal').on('click', function() {
					messageHistoryPageNumber = 1;
					fromDate = null;
					toDate = null;
					getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
					$('#messageHistoryCalSelector').addClass('hidden');
					$('#messageHistoryFrom, #messageHistoryTo').val("");
				})
				//#endregion
			})
		})
	}

	// ! SHOW COMPONENT: EWB v2 Dashboard
	function display_ewbv2_dashboard() {
		// * Put all cards together and generate final output
		var output = `
		<div class="grid grid-cols-2 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-4 lg:gap-6">
			<!-- Card -->
			<div id="ewbStatus" class="col-span-1"></div>
			<!-- End of card -->

			<!-- Card -->
			<div id="di_card" class="col-span-1">
			</div>
			<!-- End of card -->
			
			<!-- Card -->
			<div id="ai1_card" class="col-span-1"></div>
			<!-- End of card -->

			<!-- Card -->
			<div id="ai2_card" class="col-span-1"></div>
			<!-- End of card -->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
				<!-- Title -->
				<div class="card-header-1 relative">
					<div class="flex items-center">
						<div class="hidden sm:block text-gray-500 p-2 ml-4 mr-2 lg:mr-4">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
						</div>
						<div id="dateSelectors" class="flex justify-center items-center space-x-2 ml-2">
							<div data-id="3hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">3hr</div>
							<div data-id="12hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">12hr</div>
							<div data-id="1d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">1d</div>
							<div data-id="7d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">7d</div>
							<div data-id="30d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">30d</div>
							<div data-id="ChartCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
							</div>
						</div>
						<div id="ChartCalSelector" class="w-48 bg-white shadow rounded z-10 absolute left-28 top-10 border flex flex-col p-2 hidden">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
							<input id="chartFrom" type="date" class="bg-white text-gray-700">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
							<input id="chartTo" type="date" class="bg-white text-gray-700">
							<button class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
						</div>
					</div>
				</div>

				<!-- Chart -->
				<div class="card-body-1 p-2 lg:p-4">
					<canvas id="canvas">
						<!-- Filled with js -->
					</canvas>
				</div>
			</div>
			<!-- End of card-->

			<!-- Card -->
			<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
				<div class="card-header-1 relative">
					<div class="card-icon-1">
						<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>	
					</div>
					<div class="card-title-1">
						Alarms	
					</div>

					<div id="AlarmCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white ring-gray-300 hover:ring-1 hover:text-gray-800 absolute right-4">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
					</div>
					<div id="AlarmCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
						<input id="alarmFrom" type="date" class="bg-white text-gray-700">
						<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
						<input id="alarmTo" type="date" class="bg-white text-gray-700">
						<div class="flex justify-center items-center">
							<button id="goAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
							<button id="resetAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
						</div>
					</div>
				</div>

				<!-- Table -->
				<div id="alarmsCardBody" class="card-body-1 items-stretch justify-start block py-2 px-4">
					<div id="triggeredAlarms">
					
					</div>
					<div class="flex overflow-x-auto">
						<table class="table-fixed min-w-full">
							<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
								<tr>
									<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-600 whitespace-nowrap">Channel</th>
									<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600 whitespace-nowrap">Alarm</th>
									<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-600 whitespace-nowrap">Timestamp</th>
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
							<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
							<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
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

		// * Status and EWB BOARD channel
		ewbv2_getLatestReadings().then( function (ewbChannels) {
			getDeviceData().then( function(deviceData ) {
				var status = deviceData.deviceStatus;
				// STATUS processing
				if (status == 1) {
					statusHTML = `
					<!-- Card -->
					<div class="col-span-1">
						<div class="bg-green-50 border border-green-400 shadow-md h-40 flex justify-center items-center relative overflow-hidden">
							<div class="text-green-300 opacity-40 transform left-0 -rotate-12 -ml-6 absolute overflow-hidden">
								<svg class="w-44 h-44" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="z-10 text-center">
								<p class="font-semibold text-sm tracking-wider text-green-800">STATUS</p>
								<p class="font-bold text-3xl text-green-900">ONLINE</p>
							</div>
						</div>
					</div>
					<!-- End of card -->
					`;
					$('#ewbStatus').html(statusHTML);
				} else {
					statusHTML = `
					<!-- Card -->
					<div class="col-span-1">
						<div class="bg-red-50 border border-red-400 shadow-md h-40 flex justify-center items-center relative overflow-hidden">
							<div class="text-red-300 opacity-40 transform left-0 -rotate-12 -ml-6 absolute overflow-hidden">
								<svg class="w-44 h-44" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3.707 2.293a1 1 0 00-1.414 1.414l6.921 6.922c.05.062.105.118.168.167l6.91 6.911a1 1 0 001.415-1.414l-.675-.675a9.001 9.001 0 00-.668-11.982A1 1 0 1014.95 5.05a7.002 7.002 0 01.657 9.143l-1.435-1.435a5.002 5.002 0 00-.636-6.294A1 1 0 0012.12 7.88c.924.923 1.12 2.3.587 3.415l-1.992-1.992a.922.922 0 00-.018-.018l-6.99-6.991zM3.238 8.187a1 1 0 00-1.933-.516c-.8 3-.025 6.336 2.331 8.693a1 1 0 001.414-1.415 6.997 6.997 0 01-1.812-6.762zM7.4 11.5a1 1 0 10-1.73 1c.214.371.48.72.795 1.035a1 1 0 001.414-1.414c-.191-.191-.35-.4-.478-.622z"></path></svg>
							</div>
							<div class="z-10 text-center">
								<p class="font-semibold text-sm tracking-wider text-red-800">STATUS</p>
								<p class="font-bold text-3xl text-red-900">OFFLINE</p>
							</div>
						</div>
					</div>
					<!-- End of card -->
					`;
					$('#ewbStatus').html(statusHTML);
				}

				// Display DI channel
				if( ewbChannels['DI']['alarm'] != null ) {
					var dateDisplay = new Date( ewbChannels['DI']['alarm']['smsAlarmTime'] );
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					// Extra processing for red warnings
					redBorder = '';
					redText = '';
					defaultColor = 'lightblue'
					if ( ewbChannels['DI']['alarm']['smsAlarmHeader'].toUpperCase() == 'FALLEN DOWN' || ewbChannels['DI']['alarm']['smsAlarmHeader'].toUpperCase() == 'FALLEN OVER') {
						redBorder = 'border border-red-400';
						redText = 'text-red-600';
						defaultColor = 'red';
					}

					ewbBoardHTML = `
					<!-- Card -->
						<div class="col-span-1 h-40 card-wrapper-1 `+redBorder+`">
							<div class="card-header-1">
								<div class="card-icon-1 text-`+defaultColor+`-500">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
								</div>
								<div class="card-title-1 text-`+defaultColor+`-800 bg-`+defaultColor+`-100">
									`+ewbChannels['DI']['alarm']['channelName']+`
								</div>
							</div>
							<div class="card-body-1 flex-col py-4">
								<p class="text-xl xl:text-2xl font-semibold text-gray-800 text-center uppercase whitespace-nowrap `+redText+`">`+ewbChannels['DI']['alarm']['smsAlarmHeader']+`<br>
								<p class="text-xs text-gray-400 italic mt-4">`+dateDisplay+`</p>
							</div>
						</div>
						<!-- End of card -->
					`;
					$('#di_card').html(ewbBoardHTML);
				} else {
					ewbBoardHTML = `
						<!-- Card -->
							<div class="col-span-1 card-wrapper-1 h-40">
								<div class="card-header-1">
									<div class="card-icon-1">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1">
										EWB BOARD
									</div>
								</div>
								<div class="card-body-1">
									<p class="italic my-4">No alarms found...</p>
								</div>
							</div>
							<!-- End of card -->
						`;
					$('#di_card').html(ewbBoardHTML);
				}

				function compareToThreshold(reading, operator, thresholdValue) {
					reading = parseFloat(reading);
					thresholdValue = parseFloat(thresholdValue);
					response = false;
					switch(operator) {
						case '>':
							if (reading > thresholdValue) { response = true; }
							break;
						case '>=':
							if (reading >= thresholdValue) { response = true; }
							break;
						case '<':
							if (reading < thresholdValue) { response = true; }
							break;
						case '<=':
							if (reading <= thresholdValue) { response = true; }
							break;
						case '==':
							if (reading == thresholdValue) { response = true; }
							break;
					}
					return response;
				}

				function generateRedWarningCard(type, jsonDataRow, alarmList) {
					returnCardHTML = '';

					unitDisplay = '';
					if (jsonDataRow['unit'] != null) { 
						unitDisplay = jsonDataRow['unit'];
					}

					if ( type == 'alarm' ) {
						var dateDisplay = new Date( jsonDataRow['alarm']['smsAlarmTime'] );
						dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

						alarmReadingDisplay = '';
						if (jsonDataRow['alarm']['smsAlarmReading'] != null) { 
							alarmReadingDisplay = jsonDataRow['alarm']['smsAlarmReading'];
						}

						returnCardHTML = `
						<!-- Card -->
							<div class="col-span-1 card-wrapper-1 border border-red-400">
								<div class="card-header-1">
									<div class="card-icon-1 text-red-500">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1 text-red-800 bg-red-100 whitespace-nowrap truncate">
										`+jsonDataRow['channelName']+`
									</div>
								</div>
								<div class="card-body-1 flex-col py-4">
									<p class="text-base xl:text-xl font-semibold text-center uppercase text-red-600 ">`+jsonDataRow['alarm']['smsAlarmHeader']+`<br>
									<span class="text-lg font-medium lg:text-xl">`+alarmReadingDisplay+` `+unitDisplay+`</span></p>
									<p class="text-xs text-gray-400 italic mt-2">`+dateDisplay+`</p>
								</div>
							</div>
						<!-- End of card -->
						`;
						
					} else if ( type == 'reading' ) {
						var dateDisplay = new Date( jsonDataRow['reading']['measurementTime'] );
						dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

						alarmOutput = '';
						if (alarmList != null) {
							alarmList.forEach(alarm => {
								alarmOutput += alarm + '<br>';
							});
						}

						returnCardHTML = `
						<!-- Card -->
							<div class="col-span-1 card-wrapper-1 border border-red-400">
								<div class="card-header-1">
									<div class="card-icon-1 text-red-500">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1 text-red-800 bg-red-100 whitespace-nowrap truncate">
										`+jsonDataRow['channelName']+`
									</div>
								</div>
								<div class="card-body-1 flex-col py-4">
									<p class="text-base xl:text-xl font-semibold text-center uppercase text-red-600 ">`+alarmOutput+`
									<span class="text-lg font-medium lg:text-xl">`+jsonDataRow['reading']['measurement']+` `+unitDisplay+`</span></p>
									<p class="text-xs text-gray-400 italic mt-2">`+dateDisplay+`</p>
								</div>
							</div>
						<!-- End of card -->
						`;
					}

					return returnCardHTML;
				}

				function generateBlueRegularCard(type, jsonDataRow ) {
					returnCardHTML = '';

					unitDisplay = '';
					if (jsonDataRow['unit'] != null) { 
						unitDisplay = jsonDataRow['unit'];
					}

					if (type == 'reading') {
						var dateDisplay = new Date( jsonDataRow['reading']['measurementTime'] );
						dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

						returnCardHTML = `
						<!-- Card -->
							<div class="col-span-1 h-40 card-wrapper-1">
								<div class="card-header-1">
									<div class="card-icon-1">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1 whitespace-nowrap truncate">
										`+jsonDataRow['channelName']+`
									</div>
								</div>
								<div class="card-body-1 flex-col py-4">
									<p class="text-2xl xl:text-2xl font-semibold text-gray-800 text-center uppercase whitespace-nowrap">`+jsonDataRow['reading']['measurement']+` `+unitDisplay+`<br>
									<p class="text-xs text-gray-400 italic mt-4">`+dateDisplay+`</p>
								</div>
							</div>
						<!-- End of card -->
						`
					}
					return returnCardHTML;
				}

				// Function to workout how each analog card should be displayed
				function calculateCardDisplay(data) {
					returnCard = '';
					if ( data['reading'] == null && data['alarm'] == null ) {
						// Return card saying no readings if received yet if doesn't have an alarm or reading
						returnCard = `
						<!-- Card -->
							<div class="col-span-1 card-wrapper-1 h-40">
								<div class="card-header-1">
									<div class="card-icon-1">
										<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
									</div>
									<div class="card-title-1 whitespace-nowrap truncate">
										`+data['channelName']+`
									</div>
								</div>
								<div class="card-body-1">
									<p class="italic my-4 text-center">No readings received yet...</p>
								</div>
							</div>
						<!-- End of card -->
						`;
					} else if ( ( (data['reading'] == null) && !(data['alarm'] == null) ) || ( !(data['reading'] == null) && (data['alarm'] == null) ) ) {
						// XOR gate. Either reading is not null or alarm is not null. We display the one that's not null
						// But we must find the one that's not null to work out card processing
						if (data['reading'] == null && data['alarm'] != null) {
							// If reading is null and alarm isn't, then we must display the card highlighted in red, because it's in alarm mode.
							returnCard = generateRedWarningCard('alarm', data);

						} else {
							// This will pass when reading is not null and alarm is null. We must check the which one is the latter and compare against the thresholds and see if we need to highlight it in red
							const displayAlarms = [];
							if (data['thresholds'] != null) {
								data['thresholds'].forEach(threshold => {
									if ( compareToThreshold( data['reading']['measurement'], threshold['operator'], threshold['thresholdValue'] ) ) {
										displayAlarms.push( threshold['alarmDescription'] )
									}
								});
							}

							if( displayAlarms.length == 0) {
								// no alarms triggered so display simple blue card with the reading
								returnCard = generateBlueRegularCard('reading', data);
							} else {
								// show triggered alarms with the reading underneath
								alarmOutput = '';
								displayAlarms.forEach(alarm => {
									alarmOutput += alarm + '<br>';
								});
								returnCard = generateRedWarningCard('reading', data, displayAlarms);
							}
						}
					} else {
						// This will pass when both alarm and reading are not null
						// We must first find out which came latest - reading or the alarm. If it's the alarm, we display the alarm, if it's reading, we check the reading with thresholds
						measurementDate = new Date( data['reading']['measurementTime'] );
						alarmDate = new Date( data['alarm']['smsAlarmTime'] );
						if ( measurementDate >= alarmDate ) {
							// Reading date is more recent...
							const displayAlarms = [];
							if (data['thresholds'] != null) {
								data['thresholds'].forEach(threshold => {
									if ( compareToThreshold( data['reading']['measurement'], threshold['operator'], threshold['thresholdValue'] ) ) {
										displayAlarms.push( threshold['alarmDescription'] )
									}
								});
							}

							if( displayAlarms.length == 0) {
								// no alarms triggered so display simple blue card with the reading
								returnCard = generateBlueRegularCard('reading', data);
							} else {
								// show triggered alarms with the reading underneath
								alarmOutput = '';
								displayAlarms.forEach(alarm => {
									alarmOutput += alarm + '<br>';
								});
								returnCard = generateRedWarningCard('reading', data, displayAlarms);
							}
						} else {
							// Alarm date is more recent...
							returnCard = generateRedWarningCard('alarm', data);
						}
					}

					return returnCard;
				}

				// Display AI 1 channel
				if (ewbChannels.hasOwnProperty('AI1') ) {
					$('#ai1_card').html( calculateCardDisplay(ewbChannels['AI1']) );
				} else {
					ai1HTML = `
					<!-- Card -->
					<div class="col-span-1 card-wrapper-1 h-40">
						<div class="card-header-1">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="card-title-1">
								Analog Channel #1
							</div>
						</div>
						<div class="card-body-1">
							<p class="italic my-4 text-center">Analog channel #1 not registered</p>
						</div>
					</div>
					<!-- End of card -->
					`;
					$('#ai1_card').html(ai1HTML);
				}


				// Display AI 2 channel
				if (ewbChannels.hasOwnProperty('AI2') ) {
					$('#ai2_card').html( calculateCardDisplay(ewbChannels['AI2']) );
				} else {
					ai2HTML = `
					<!-- Card -->
					<div class="col-span-1 card-wrapper-1 h-40">
						<div class="card-header-1">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
							</div>
							<div class="card-title-1">
								Analog Channel #2
							</div>
						</div>
						<div class="card-body-1">
							<p class="italic my-4 text-center">Analog channel #2 not registered</p>
						</div>
					</div>
					<!-- End of card -->
					`;
					$('#ai2_card').html(ai2HTML);
				}
			})
		})


		// * Load alarms card
		//#region 
		var alarmPageNumber = 1;
		var alarmsPerPage = 5;
		var fromDate = null;
		var toDate = null;
		getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);

		// Alarm paging
		$('#next_alarms').on('click', function () {
			alarmPageNumber += 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})
		$('#previous_alarms').on('click', function () {
			alarmPageNumber -= 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})

		$('#AlarmCal').on('click', function() {
			$('#AlarmCalSelector').toggleClass('hidden');
		})
		$('#goAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = $('#alarmFrom').val();
			toDate = $('#alarmTo').val();
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
		})

		$('#resetAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = null;
			toDate = null;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
			$('#alarmFrom, #alarmTo').val("");
		})

		function displayTriggeredAlarms(triggeredAlarms) {
			var triggeredAlarmsOutput = '';
			for (i = 0; i < triggeredAlarms.length; i++) {
				triggeredAlarmsOutput += `
				<!-- Alarm -->
				<div class="flex h-10 text-sm text-red-600 font-medium items-center pl-4 bg-red-50 border border-red-500 rounded my-2">
					<div class="hidden xl:block flex-1 whitespace-nowrap mx-2 text-xs italic">
						ACTIVE ALARM!
					</div>
					<div class="flex-1 whitespace-nowrap">
						<span class="text-xs uppercase text-red-400 mr-1">Channel:</span> `+triggeredAlarms[i].channelName+`
					</div>
					<div class="flex-1 whitespace-nowrap text-center">
						<span class="text-xs uppercase text-red-400 mr-1">Trigger:</span> `+triggeredAlarms[i].operator+` `+triggeredAlarms[i].thresholdValue+` `+triggeredAlarms[i].unitName+`
					</div>
				</div>
				<!-- End of alarm -->
				`
			}
			$('#triggeredAlarms').html(triggeredAlarmsOutput);
		}
		// Display triggered alarms
		getTriggeredAlarms().then( function(triggeredAlarms) {
			displayTriggeredAlarms(triggeredAlarms);
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

		var activeClass = 'text-gray-800 bg-white ring-gray-300 ring-1';
		$('#dateSelectors').children().first().addClass(activeClass);
		$('#dateSelectors').children().on('click', function() {
			$('#dateSelectors').children().removeClass(activeClass);
			$(this).addClass(activeClass);
			var dataid = $(this).attr('data-id');
			if (dataid != 'ChartCal') {
				getDatasets(dataid,'NOW').then( function (data) {
					drawChart(chart, data);
				})

				if ( !$('#ChartCalSelector').hasClass('hidden') ) {
					$('#ChartCalSelector').addClass('hidden');
				}
				$('#chartFrom, #chartTo').val("");
			} else {
				$('#ChartCalSelector').toggleClass('hidden');
			}
		})
		$('#ChartCalSelector > button').on('click', function() {
			getDatasets($('#chartFrom').val() , $('#chartTo').val()).then( function (data) {
				drawChart(chart, data);
			})
			$('#ChartCalSelector').addClass('hidden');
		})
		//#endregion
	
	}

	// ! SHOW COMPONENT: Message history
	function display_messageHistory() {
		// * Put all cards together and generate final output
		var output = `
		<div class="grid grid-cols-1">

			<!-- Left panel -->
			<div class="col-span-1">
				<!-- Card -->
				<div class="col-span-2 md:col-span-2 lg:col-span-2 card-wrapper-1">
					<div class="card-header-1 relative">
						<div class="card-icon-1">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd"></path></svg>
						</div>
						<div class="card-title-1">
							Message history	
						</div>

						<div id="messageHistoryCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white ring-gray-300 hover:ring-1 hover:text-gray-800 absolute right-4">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
						</div>
						<div id="messageHistoryCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
							<input id="messageHistoryFrom" type="date" class="bg-white text-gray-700">
							<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
							<input id="messageHistoryTo" type="date" class="bg-white text-gray-700">
							<div class="flex justify-center items-center">
								<button id="gomessageHistoryCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
								<button id="resetmessageHistoryCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
							</div>
						</div>
					</div>

					<!-- Table -->
					<div id="messageHistoryCardBody" class="card-body-1 block items-stretch justify-start py-2 px-4">
						<div class="flex overflow-x-auto">
							<table class="table-fixed min-w-full">
								<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
									<tr>
										<th class="text-center w-1/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">From</th>
										<th class="text-center w-1/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">To</th>
										<th class="text-center w-6/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Message</th>
										<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Timestamp</th>
										<th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Message Type</th>
									</tr>
								</thead>
								<tbody id="table_messageHistory">
									<!-- This area gets filled via PHP -->
								</tbody>
							</table>
						</div>
						<div id="loadingOverlay_messageHistory" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
							<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
							<p>Loading...</p>
						</div>

						<div class="flex flex-col items-center justify-center py-4">
							<div class="flex">
								<button id="previous_messageHistory" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
								<button id="next_messageHistory" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
							</div>
							<p class="mt-4 text-xs font-semibold">Showing <span id="range_messageHistory"></span> of <span id="total_messageHistory"></span></p>
						</div>
					</div>
				</div>
				<!-- End of card-->
			</div>	

			<!-- End of card-->
		</div>
		`;
		// * Update site content once its generated
		siteContent.html(output);

		// Display message history
		//#region 
		var messageHistoryPageNumber = 1;
		var messageHistoryPerPage = 10;
		var fromDate = null;
		var toDate = null;
		getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);

		// Alarm paging
		$('#next_messageHistory').on('click', function () {
			messageHistoryPageNumber += 1;
			getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
		})
		$('#previous_messageHistory').on('click', function () {
			messageHistoryPageNumber -= 1;
			getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
		})

		$('#messageHistoryCal').on('click', function() {
			$('#messageHistoryCalSelector').toggleClass('hidden');
		})
		$('#gomessageHistoryCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = $('#messageHistoryFrom').val();
			toDate = $('#messageHistoryTo').val();
			getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
			$('#messageHistoryCalSelector').addClass('hidden');
		})

		$('#resetmessageHistoryCal').on('click', function() {
			messageHistoryPageNumber = 1;
			fromDate = null;
			toDate = null;
			getMessageHistory(messageHistoryPerPage, messageHistoryPageNumber, 'table_messageHistory', fromDate, toDate);
			$('#messageHistoryCalSelector').addClass('hidden');
			$('#messageHistoryFrom, #messageHistoryTo').val("");
		})
		//#endregion
	}

	// ! SHOW COMPONENT: Tilt Dashboard
	function display_tilt_dashboard() {
		// * Put all cards together and generate final output
		var output = `
			<div id="tilt_settings_modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
				<div id="modal-box" class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
					<!-- Title/close btn -->
					<div class="flex justify-between items-center border-b pb-1 border-gray-300">
						<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">Dashboard settings</p>
						<svg id="close-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
					</div>
					<div class="w-full">
						<div class="flex flex-col">
		
							<div class="flex flex-col mt-2">
								<p class="form-field-title">Image Link</p>
								<input id="settings_imageURL" value="" type="text" class="border border-gray-300">
							</div>
		
							<!-- Row -->
							<div class="flex flex-auto justify-center mt-8">
								<p class="text-sm uppercase font-semibold text-gray-800 bg-blue-200">Horizontal settings</p>
							</div>

							<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">TEXT WHEN READING LESS THAN 0</p>
									<input id="settings_horizontalBox_lt0_text" value="" type="text" class="border border-gray-300">

									<p class="form-field-title mt-2">ARROW DIRECTION WHEN READING LESS THAN 0</p>
									<div class="flex items-center justify-center space-x-12">
										<div class="flex items-center">
											<input id="" value="LEFT" type="radio" name="horizontal_lt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Left</p>
										</div>
										<div class="flex items-center">
											<input id="" value="RIGHT" type="radio" name="horizontal_lt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Right</p>
										</div>
									</div>
								</div>
			
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">TEXT WHEN READING MORE THAN 0</p>
									<input id="settings_horizontalBox_mt0_text" value="" type="text" class="border border-gray-300">

									<p class="form-field-title mt-2">ARROW DIRECTION WHEN READING MORE THAN 0</p>
									<div class="flex items-center justify-center space-x-12">
										<div class="flex items-center">
											<input id="" value="LEFT" type="radio" name="horizontal_mt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Left</p>
										</div>
										<div class="flex items-center">
											<input id="" value="RIGHT" type="radio" name="horizontal_mt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Right</p>
										</div>
									</div>
								</div>
							</div>
							<!-- End of row -->
							
							<div class="flex flex-auto justify-center mt-8">
								<p class="text-sm uppercase font-semibold text-gray-800 bg-blue-200">Vertical Settings</p>
							</div>
		
							<!-- Row -->
							<div class="flex flex-col md:flex-row md:space-x-6 flex-auto">
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">TEXT WHEN READING LESS THAN 0</p>
									<input id="settings_verticalBox_lt0_text" value="" type="text" class="border border-gray-300">

									<p class="form-field-title mt-2">ARROW DIRECTION WHEN READING LESS THAN 0</p>
									<div class="flex items-center justify-center space-x-12">
										<div class="flex items-center">
											<input id="" value="UP" type="radio" name="vertical_lt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Up</p>
										</div>
										<div class="flex items-center">
											<input id="" value="DOWN" type="radio" name="vertical_lt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Down</p>
										</div>
									</div>
								</div>
			
								<div class="flex flex-col flex-1 mt-2">
									<p class="form-field-title">TEXT WHEN READING MORE THAN 0</p>
									<input id="settings_verticalBox_mt0_text" value="" type="text" class="border border-gray-300">

									<p class="form-field-title mt-2">ARROW DIRECTION WHEN READING MORE THAN 0</p>
									<div class="flex items-center justify-center space-x-12">
										<div class="flex items-center">
											<input id="" value="UP" type="radio" name="vertical_mt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Up</p>
										</div>
										<div class="flex items-center">
											<input id="" value="DOWN" type="radio" name="vertical_mt0_arrow" class="border border-gray-300">
											<p class="text-gray-800 uppercase font-medium text-sm ml-2">Down</p>
										</div>
									</div>
								</div>
							</div>
							<!-- End of row -->
		
							<div class="flex justify-end items-center mt-4 space-x-4">
								<button id="cancelBtn" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Cancel</button>
								<button id="saveSettings" class="h-10 bg-green-500 border-0 hover:border-0 px-4 rounded text-white hover:bg-green-700 transition-all focus:bg-green-700 focus:text-white">Save</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="flex lg:space-y-0 flex-col lg:flex-row lg:space-x-6">
				<div class="flex flex-col space-y-6 w-full lg:w-1/2 lg:max-w-xl order-last lg:order-first">
					
					<div class="hidden lg:block card-wrapper-1">
						<div class="card-header-1 relative">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
							</div>

							<!-- Edit button -->
							<button id="tilt_settings_button" class="card-icon-1 border-gray-300 focus:outline-none border text-gray-500 transition hover:text-green-600 hover:border-green-400 hover:bg-green-300 cursor-pointer absolute right-0 p-1" title="Edit">
								<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
							</button>

							<div class="card-title-1">
								Site Overview
							</div>
						</div>
						<div class="card-body-1 overflow-hidden" style="height: 700px;">
								<div id="tilt_image" class="bg-auto bg-no-repeat bg-center w-full h-full relative">
									<div id="horizontalBox" data-direction="horizontal" class="absolute flex flex-col justify-center items-center p-1 text-gray-800 rounded shadow border bg-gray-50 cursor-move transition hover:bg-gray-100 hover:bg-opacity-90 select-none" style="width: 128px;">
										<div id="horizontalBox_arrow"></div>
										
										<p id="horizontalBox_reading" class="font-extrabold"></p>
										<p id="horizontalBox_text" class="text-center text-xs font-medium">Waiting for horizontal readings...</p>
										<p id="horizontalBox_timer" class="text-center text-xs text-gray-400"></p>
									</div>

									<div id="verticalBox" data-direction="vertical" class="absolute flex flex-col justify-center items-center p-1 text-gray-800 rounded shadow border bg-gray-50 cursor-move transition hover:bg-gray-100 hover:bg-opacity-90 select-none" style="width: 128px;">
										<div id="verticalBox_arrow"></div>

										<p id="verticalBox_reading" class="font-extrabold"></p>
										<p id="verticalBox_text" class="text-center text-xs font-medium">Waiting for vertical readings...</p>
										<p id="verticalBox_timer" class="text-center text-xs text-gray-400"></p>
									</div>
								</div>
						</div>
					</div>
				</div>
				
				<div id="rightPanel" class="flex-auto grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
					
					<!-- 4 AI channel cards will be added through js -->

					<!-- Card -->
					<div class="col-span-1 md:col-span-2 card-wrapper-1">
						<div class="card-header-1 relative">
							<div class="card-icon-1">
								<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>	
							</div>
							<div class="card-title-1">
								Alarms	
							</div>

							<div id="AlarmCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-gray-100 hover:text-gray-800 absolute right-4">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
							</div>
							<div id="AlarmCalSelector" class="w-48 bg-white shadow rounded z-10 absolute top-10 right-4 border flex flex-col p-2 hidden">
								<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
								<input id="alarmFrom" type="date" class="bg-white text-gray-700">
								<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
								<input id="alarmTo" type="date" class="bg-white text-gray-700">
								<div class="flex justify-center items-center">
									<button id="goAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
									<button id="resetAlarmCal" class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Reset</button>
								</div>
							</div>
						</div>

						<!-- Table -->
						<div id="alarmsCardBody" class="flex-auto bg-gray-50 py-2 px-4">
							<div id="triggeredAlarms">
							
							</div>
							<div class="flex overflow-x-auto">
								<table class="table-fixed min-w-full">
									<thead class="uppercase text-xs border-b border-gray-200 text-bluegray-900">
										<tr>
											<th class="text-left w-2/12 py-2 px-4 font-medium text-gray-500 whitespace-nowrap">Channel</th>
											<th class="text-center w-6/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500 whitespace-nowrap">Alarm</th>
											<th class="text-center w-4/12 lg:w-4/12 py-2 px-4 font-medium text-gray-500 whitespace-nowrap">Timestamp</th>
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
									<button id="previous_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
									<button id="next_alarms" class="focus:outline-none h-8 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-xs border border-gray-300 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
								</div>
								<p class="mt-4 text-xs font-semibold">Showing <span id="range_alarms"></span> of <span id="total_alarms"></span></p>
							</div>
						</div>
					</div>
					<!-- End of card-->

					<!-- Card -->
					<div class="col-span-1 md:col-span-2 card-wrapper-1">
						<!-- Title -->
						<div class="card-header-1 relative">
							<div class="flex items-center">
								<div class="hidden sm:block text-gray-500 p-2 ml-4 mr-2 lg:mr-4">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
								</div>
								<div id="dateSelectors" class="flex justify-center items-center space-x-2 ml-2">
									<div data-id="3hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">3hr</div>
									<div data-id="12hr" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">12hr</div>
									<div data-id="1d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">1d</div>
									<div data-id="7d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">7d</div>
									<div data-id="30d" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">30d</div>
									<div data-id="ChartCal" class="text-gray-400 font-medium text-sm rounded-lg py-1 px-2 cursor-pointer hover:bg-white hover:text-gray-800">
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									</div>
								</div>
								<div id="ChartCalSelector" class="w-48 bg-white shadow rounded z-10 absolute left-28 top-10 border flex flex-col p-2 hidden">
									<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">From</p>
									<input id="chartFrom" type="date" class="bg-white text-gray-700">
									<p class="text-xs uppercase font-medium text-gray-700 ml-4 my-2">To</p>
									<input id="chartTo" type="date" class="bg-white text-gray-700">
									<button class="bg-gray-50 border rounded w-16 mx-auto mt-2 hover:bg-gray-100">Go</button>
								</div>
							</div>
						</div>

						<!-- Chart -->
						<div class="card-body-1 p-2 lg:p-4">
							<canvas id="canvas">
								<!-- Filled with js -->
							</canvas>
						</div>
					</div>
					<!-- End of card-->

				</div>
			</div>
		`;
		// * Update site content once its generated
		siteContent.html(output);

		$('#tilt_settings_button, #close-modal, #cancelBtn').on('click', function() {
			$('#tilt_settings_modal').toggleClass('hidden');
		})

		// Initial load info box settings
		getTiltDashboardSettings(deviceId).then( function (settings) {
			$('#horizontalBox, #verticalBox').draggable({
				stop: function() {
					var pos_left = $(this).offset().left;
					var pos_top = $(this).offset().top;
					var direction = $(this).data('direction');

					updateTiltBoxPosition(pos_left, pos_top, direction);
				}
			});

			if (settings.horizontalBox_offset_left == null) {
				$('#horizontalBox').offset({left: $('#tilt_image').offset().left+100});
			} else {
				$('#horizontalBox').offset({left: settings.horizontalBox_offset_left});
			}
			if (settings.horizontalBox_offset_top == null) {
				$('#horizontalBox').offset({top: $('#tilt_image').offset().top+100});
			} else {
				$('#horizontalBox').offset({top: settings.horizontalBox_offset_top});
			}
			if (settings.verticalBox_offset_left == null) {
				$('#verticalBox').offset({left: $('#tilt_image').offset().left+100});
			} else {
				$('#verticalBox').offset({left: settings.verticalBox_offset_left});
			}
			if (settings.verticalBox_offset_top == null) {
				$('#verticalBox').offset({top: $('#tilt_image').offset().top+300});
			} else {
				$('#verticalBox').offset({top: settings.verticalBox_offset_top});
			}

			if (settings.horizontalBox_lt0_text == null) { $('#settings_horizontalBox_lt0_text').val('') } else { $('#settings_horizontalBox_lt0_text').val(settings.horizontalBox_lt0_text) }
			if (settings.horizontalBox_mt0_text == null) { $('#settings_horizontalBox_mt0_text').val('') } else { $('#settings_horizontalBox_mt0_text').val(settings.horizontalBox_mt0_text) }
			if (settings.verticalBox_lt0_text == null) { $('#settings_verticalBox_lt0_text').val('') } else { $('#settings_verticalBox_lt0_text').val(settings.verticalBox_lt0_text) }
			if (settings.verticalBox_mt0_text == null) { $('#settings_verticalBox_mt0_text').val('') } else { $('#settings_verticalBox_mt0_text').val(settings.verticalBox_mt0_text) }
			
			// Load horizontal arrow directions
			if (settings.horizontalBox_lt0_arrowDirection != null ) { $('input[name=horizontal_lt0_arrow][value="'+settings.horizontalBox_lt0_arrowDirection+'"]').prop("checked",true); }
			if (settings.horizontalBox_mt0_arrowDirection != null ) { $('input[name=horizontal_mt0_arrow][value="'+settings.horizontalBox_mt0_arrowDirection+'"]').prop("checked",true); }

			// Load vertical arrow directions
			if (settings.verticalBox_lt0_arrowDirection != null ) { $('input[name=vertical_lt0_arrow][value="'+settings.verticalBox_lt0_arrowDirection+'"]').prop("checked",true); }
			if (settings.verticalBox_mt0_arrowDirection != null ) { $('input[name=vertical_mt0_arrow][value="'+settings.verticalBox_mt0_arrowDirection+'"]').prop("checked",true); }

		})

		function timeSince(date) {
			var seconds = Math.floor((new Date() - date) / 1000);
			var interval = seconds / 31536000;
		  
			if (interval > 1) {
			  return Math.floor(interval) + " years";
			}
			interval = seconds / 2592000;
			if (interval > 1) {
			  return Math.floor(interval) + " months";
			}
			interval = seconds / 86400;
			if (interval > 1) {
			  return Math.floor(interval) + " days";
			}
			interval = seconds / 3600;
			if (interval > 1) {
			  return Math.floor(interval) + " hours";
			}
			interval = seconds / 60;
			if (interval > 1) {
			  return Math.floor(interval) + " minutes";
			}
			return Math.floor(seconds) + " seconds";
		}

		function updateSiteOverviewDisplay() {
			rtmu_getLatestReadings().then( function(readingsData) {
				getTiltDashboardSettings(deviceId).then( function (settings) {
					// Set image
					if (settings.imageURL == null) {
						$('#settings_imageURL').val('');
						$('#tilt_image').css('background-image', 'url("https://i.gyazo.com/d373e51cebaf9121efc1d672fdc15b17.jpg")');						
					} else {
						$('#settings_imageURL').val(settings.imageURL)
						$('#tilt_image').css('background-image', 'url("'+settings.imageURL+'")');
					}
					
					for(var i = 0; i<readingsData.latestMeasurements.length; i++) {
						if(readingsData.latestMeasurements[i].channelName == 'X AXIS') {
							var xReading = readingsData.latestMeasurements[i].measurement;
							var xReadingTime = readingsData.latestMeasurements[i].measurementTime;
							var xUnit = readingsData.latestMeasurements[i].unitName;
						}
						if(readingsData.latestMeasurements[i].channelName == 'Y AXIS') {
							var yReading = readingsData.latestMeasurements[i].measurement;
							var yReadingTime = readingsData.latestMeasurements[i].measurementTime;
							var yUnit = readingsData.latestMeasurements[i].unitName;
						}
					}

					// Define arrow direction icons
					//#region
					var leftArrow = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>';
					var rightArrow = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
					var upArrow = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>';
					var downArrow = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
					//#endregion
					
					// Load horizontal box
					$('#horizontalBox_reading').html(xReading + xUnit);
					$('#horizontalBox_timer').html(timeSince(new Date(xReadingTime)) + ' ago');
					if (xReading >= 0) {
						if (settings.horizontalBox_mt0_text == null) {
							$('#horizontalBox_text').html('Text not set');
						} else {
							$('#horizontalBox_text').html(settings.horizontalBox_mt0_text);
						}
						// Arrow
						if (settings.horizontalBox_mt0_arrowDirection == 'RIGHT') {
							$('#horizontalBox_arrow').html(rightArrow);
						} else {
							$('#horizontalBox_arrow').html(leftArrow);
						}

					} else {
						if (settings.horizontalBox_lt0_text == null) {
							$('#horizontalBox_text').html('Text not set');
						} else {
							$('#horizontalBox_text').html(settings.horizontalBox_lt0_text);
						}

						// Arrow
						if (settings.horizontalBox_lt0_arrowDirection == 'RIGHT') {
							$('#horizontalBox_arrow').html(rightArrow);
						} else {
							$('#horizontalBox_arrow').html(leftArrow);
						}
					}

					// Load vertical box
					$('#verticalBox_reading').html(yReading + yUnit);
					$('#verticalBox_timer').html(timeSince(new Date(yReadingTime)) + ' ago');
					if (yReading >= 0) {
						if (settings.verticalBox_mt0_text == null) {
							$('#verticalBox_text').html('Text not set');
						} else {
							$('#verticalBox_text').html(settings.verticalBox_mt0_text);
						}

						// Arrow
						if (settings.verticalBox_mt0_arrowDirection == 'UP') {
							$('#verticalBox_arrow').html(upArrow);
						} else {
							$('#verticalBox_arrow').html(downArrow);
						}
					} else {
						if (settings.verticalBox_lt0_text == null) {
							$('#verticalBox_text').html('Text not set');
						} else {
							$('#verticalBox_text').html(settings.verticalBox_lt0_text);
						}
						// Arrow
						if (settings.verticalBox_lt0_arrowDirection == 'UP') {
							$('#verticalBox_arrow').html(upArrow);
						} else {
							$('#verticalBox_arrow').html(downArrow);
						}
					}

				})
			})
		}
		updateSiteOverviewDisplay();

		$('#saveSettings').on('click', function() {
			var imageURL = $.trim($('#settings_imageURL').val());

			var horiz_lt0_text = $.trim($('#settings_horizontalBox_lt0_text').val());
			var horiz_mt0_text = $.trim($('#settings_horizontalBox_mt0_text').val());

			var vert_lt0_text = $.trim($('#settings_verticalBox_lt0_text').val());
			var vert_mt0_text = $.trim($('#settings_verticalBox_mt0_text').val());

			var horiz_lt0_arrow = $('input[name=horizontal_lt0_arrow]:checked').val();
			var horiz_mt0_arrow = $('input[name=horizontal_mt0_arrow]:checked').val();

			var vert_mt0_arrow = $('input[name=vertical_mt0_arrow]:checked').val();
			var vert_lt0_arrow = $('input[name=vertical_lt0_arrow]:checked').val();
			
			updateTiltSettings(imageURL, horiz_lt0_text, horiz_mt0_text, horiz_lt0_arrow, horiz_mt0_arrow, vert_lt0_text, vert_mt0_text, vert_lt0_arrow, vert_mt0_arrow);
			$('#tilt_settings_modal').addClass('hidden');
			updateSiteOverviewDisplay();
		})

		rtmu_getLatestReadings().then( function(readingsData) {
			// Display readings of the AI channels
			// We use i-- to compensate for using .prepend as that will display channels in reverse order
			for(i = readingsData.latestMeasurements.length-1; i >= 0; i--) {
				var dateDisplay = new Date( readingsData.latestMeasurements[i].measurementTime );
				dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });
				
				// Add card
				$('#rightPanel').prepend(`
					<!-- Card -->
					<div class="col-span-1 card-wrapper-1">
						<div class="card-header-1">
							<div class="card-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" class="bi bi-thermometer-half" viewBox="0 0 16 16">
									<path d="M9.5 12.5a1.5 1.5 0 1 1-2-1.415V6.5a.5.5 0 0 1 1 0v4.585a1.5 1.5 0 0 1 1 1.415z"/>
									<path d="M5.5 2.5a2.5 2.5 0 0 1 5 0v7.55a3.5 3.5 0 1 1-5 0V2.5zM8 1a1.5 1.5 0 0 0-1.5 1.5v7.987l-.167.15a2.5 2.5 0 1 0 3.333 0l-.166-.15V2.5A1.5 1.5 0 0 0 8 1z"/>
								</svg>
							</div>
							<div class="card-title-1">
								`+readingsData.latestMeasurements[i].channelName+`	
							</div>
						</div>
						<div id="latestMeasurements_body" class="card-body-1 flex-col py-4">
							<div>
								<span class="text-3xl lg:text-5xl font-medium text-gray-800">`+readingsData.latestMeasurements[i].measurement+`</span><span class="">`+readingsData.latestMeasurements[i].unitName+`</span>
							</div>
							<div>
								<p class="text-xs text-gray-400 italic mt-1 mb-2">`+dateDisplay+`</p>
							</div>
						</div>
					</div>
					<!-- End of card -->
				`);
			}
		})

		// * Load alarms card
		//#region 
		var alarmPageNumber = 1;
		var alarmsPerPage = 5;
		var fromDate = null;
		var toDate = null;
		getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);

		// Alarm paging
		$('#next_alarms').on('click', function () {
			alarmPageNumber += 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})
		$('#previous_alarms').on('click', function () {
			alarmPageNumber -= 1;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
		})

		$('#AlarmCal').on('click', function() {
			$('#AlarmCalSelector').toggleClass('hidden');
		})
		$('#goAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = $('#alarmFrom').val();
			toDate = $('#alarmTo').val();
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
		})

		$('#resetAlarmCal').on('click', function() {
			alarmPageNumber = 1;
			fromDate = null;
			toDate = null;
			getAlarms(alarmsPerPage, alarmPageNumber, 'table_alarms', fromDate, toDate);
			$('#AlarmCalSelector').addClass('hidden');
			$('#alarmFrom, #alarmTo').val("");
		})

		function displayTriggeredAlarms(triggeredAlarms) {
			var triggeredAlarmsOutput = '';
			for (i = 0; i < triggeredAlarms.length; i++) {
				triggeredAlarmsOutput += `
				<!-- Alarm -->
				<div class="flex h-10 text-sm text-red-600 font-medium items-center pl-4 bg-red-50 border border-red-500 rounded my-2">
					<div class="hidden xl:block flex-1 whitespace-nowrap mx-2 text-xs italic">
						ACTIVE ALARM!
					</div>
					<div class="flex-1 whitespace-nowrap">
						<span class="text-xs uppercase text-red-400 mr-1">Channel:</span> `+triggeredAlarms[i].channelName+`
					</div>
					<div class="flex-1 whitespace-nowrap text-center">
						<span class="text-xs uppercase text-red-400 mr-1">Trigger:</span> `+triggeredAlarms[i].operator+` `+triggeredAlarms[i].thresholdValue+` `+triggeredAlarms[i].unitName+`
					</div>
				</div>
				<!-- End of alarm -->
				`
			}
			$('#triggeredAlarms').html(triggeredAlarmsOutput);
		}
		// Display triggered alarms
		getTriggeredAlarms().then( function(triggeredAlarms) {
			displayTriggeredAlarms(triggeredAlarms);
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

		var activeClass = 'text-gray-800 bg-white ring-1 ring-gray-300';
		$('#dateSelectors').children().first().addClass(activeClass);
		$('#dateSelectors').children().on('click', function() {
			$('#dateSelectors').children().removeClass(activeClass);
			$(this).addClass(activeClass);
			var dataid = $(this).attr('data-id');
			if (dataid != 'ChartCal') {
				getDatasets(dataid,'NOW').then( function (data) {
					drawChart(chart, data);
				})

				if ( !$('#ChartCalSelector').hasClass('hidden') ) {
					$('#ChartCalSelector').addClass('hidden');
				}
				$('#chartFrom, #chartTo').val("");
			} else {
				$('#ChartCalSelector').toggleClass('hidden');
			}
		})
		$('#ChartCalSelector > button').on('click', function() {
			getDatasets($('#chartFrom').val() , $('#chartTo').val()).then( function (data) {
				drawChart(chart, data);
			})
			$('#ChartCalSelector').addClass('hidden');
		})
		//#endregion
		
	}
	
	// ! Hide loaders on load
    $('#loadingOverlay_measurements, #loadingOverlay_alarms, #loadingOverlay_messageHistory').hide();



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

	function ewbv2_getLatestReadings() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'ewbv2_getLatestReadings'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function getTriggeredAlarms() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getTriggeredAlarms'
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
			var dateTo = now.getFullYear() + "-" + (now.getMonth()+1) +  "-" + now.getDate() + " " + now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds();
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
			var dateFrom = dateFrom.getFullYear() + "-" + (dateFrom.getMonth()+1) +  "-" + dateFrom.getDate() + " " + dateFrom.getHours() + ":" + dateFrom.getMinutes() + ":" + dateFrom.getSeconds();
		}


		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					dateTo: dateTo,
					dateFrom: dateFrom,
					function: 'getDatasets'
				},
				success: function (data) {
					data = JSON.parse(data);
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

	function getAlarms(perPage, pageNumber, displayTableId, fromDate, toDate) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				alarmsPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				deviceId: deviceId,
				fromDate: fromDate,
				toDate: toDate,
				function: 'loadTable_alarms'
			},
			beforeSend: function () {
				$('#loadingOverlay_alarms').show();
			},
			success: function (data) {
				$('#loadingOverlay_alarms').hide();
				var alarms = JSON.parse(data);

				totalCount = alarms['totalCount'];
				returnedCount = alarms['alarmHistory'].length;
				pageControl(totalCount, returnedCount, perPage, pageNumber, 'alarms');

				var outputTable = '';
				for (i = 0; i < alarms['alarmHistory'].length; i++) {
					var alternatingBg = '';
					if (i%2 == 0) {
						alternatingBg = 'bg-gray-100';
					} else {
						alternatingBg = '';
					}
					var dateDisplay = new Date( alarms['alarmHistory'][i].timestampCol);
					dateDisplay.setHours(dateDisplay.getHours() + 1);
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					var alarmDisplay = '-';
					if (alarms['alarmHistory'][i]['msg1'] == null) {alarms['alarmHistory'][i]['msg1'] = '';}
					if (alarms['alarmHistory'][i]['msg2'] == null) {alarms['alarmHistory'][i]['msg2'] = '';}
					if (alarms['alarmHistory'][i]['unit'] == null) {alarms['alarmHistory'][i]['unit'] = '';}

					if (alarms['alarmHistory'][i]['type'] == 'triggeredHistory') {
						if (alarms['alarmHistory'][i]['msg3'] == null) { alarms['alarmHistory'][i]['msg3'] = ''}
						alarmDisplay = '<span class="font-semibold underline">'+alarms['alarmHistory'][i]['msg3'] + '</span> TRIGGERED BY ('+alarms['alarmHistory'][i]['msg1'] + ' ' + alarms['alarmHistory'][i]['msg2'] + ' ' + alarms['alarmHistory'][i]['unit'] + ')';
					} else if ( alarms['alarmHistory'][i]['type'] == 'smsAlarm' ) {
						alarmDisplay = alarms['alarmHistory'][i]['msg1'] + ' ' + alarms['alarmHistory'][i]['msg2'] + ' ' +  alarms['alarmHistory'][i]['unit'];
					} 
					else if ( alarms['alarmHistory'][i]['type'] == 'smsStatus') {
						alarmDisplay = alarms['alarmHistory'][i]['msg1'] + ' ' + alarms['alarmHistory'][i]['msg2'];
					}

					outputTable += `
					<tr class="border-b border-gray-200 h-10 `+ alternatingBg +`">
						<td class="text-left py-2 px-4 text-xs text-gray-700 font-medium whitespace-nowrap">`+ alarms['alarmHistory'][i].channelName + `</td>
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
		// London coordinates will be shown by default
		var showLat = 51.508530;
		var showLong = -0.076132;

		// First check if device coordinates exist, then check for group coordinates, and if both fail, display London as default
		if (deviceCoordinates.deviceLatitude == null || deviceCoordinates.deviceLongitude == null) {
			if (deviceCoordinates.groupLatitude == null || deviceCoordinates.groupLongitude == null) {
				// London will be displayed
			} else {
				showLat = deviceCoordinates.groupLatitude;
				showLong = deviceCoordinates.groupLongitude;
			}
		} else {
			showLat = deviceCoordinates.deviceLatitude;
			showLong = deviceCoordinates.deviceLongitude;
		}

		fetch('//api.openweathermap.org/data/2.5/weather?lat='+showLat+'&lon='+showLong+'&units=metric&APPID=f37101538ad0f765c18ce4538d42de2e')
			.then(response => response.json())
			.then(data => {
				var loc = data['name'] + ', ' + data['sys']['country'];
				var temperature = Math.round( data['main']['temp'] * 10 ) / 10;
				$('#'+locationSpan).html(loc);
				$('#'+tempSpan).html(temperature);
			})
	}

	function getAlarmTriggers() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getAlarmTriggers'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function registerNewTrigger(channelId, operator, thresholdValue, alarmDescription) {
		if (channelId == -1 || thresholdValue == "") {
			return false;
		}
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				deviceId: deviceId,
				channelId: channelId,
				operator: operator,
				thresholdValue: thresholdValue,
				alarmDescription: alarmDescription,
				function: 'registerNewTrigger'
			},
			success: function (data) {
			}
		})
		return true;
	}

	function deleteTrigger(triggerId) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				deviceId: deviceId,
				triggerId: triggerId,
				function: 'deleteTrigger'
			},
			success: function (data) {
				if (data == 'SUCCESS') {
					alert('Alarm deleted successfully');
				} else {
					alert('Error deleting alarm');
				}
			}
		})
	}
	
	function getAllUsers() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					function: 'getAllUsers'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function getRecipients() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getRecipients'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function addNewRecipient(userId) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					userId: userId,
					function: 'addNewRecipient'
				},
				success: function (data) {
					resolve(data);
				}
			})
		})
	}

	function deleteRecipient(recipientId) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					recipientId: recipientId,
					function: 'deleteRecipient'
				},
				success: function (data) {
					if (data == 'SUCCESS') {
						resolve('SUCCESS');
					} else {
						alert('Error deleting alarm');
					}
				}
			})
		})
	}
	
	function getMeasurementsLog(perPage, pageNumber, displayTableId, fromDate, toDate) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				measurementsPerPage: perPage,
                offset: perPage * (pageNumber - 1),
                deviceId: deviceId,
				fromDate: fromDate,
				toDate: toDate,
				function: 'loadTable_measurements'
			},
			beforeSend: function () {
				$('#loadingOverlay_measurements').show();
			},
			success: function (data) {
				$('#loadingOverlay_measurements').hide();
				var measurements = JSON.parse(data);
				
				totalCount = measurements[measurements.length - 1]['totalRows'];
				returnedCount = measurements.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber, 'measurements');

				var outputTable = '';
				var bgCounter = 0;
				var alternatingBg = 'bg-gray-100';
				for (i = 0; i < measurements.length - 1; i++) {
					// We change bg in batches of readings. E.g. if device records 4 channels, background will only change between batches of measurements instead of each line
					function changeBg() {
						if (bgCounter%2 == 0) {
							alternatingBg = '';
						} else {
							alternatingBg = 'bg-gray-100';
						}
						bgCounter++;
					}
					var newBatch = false;
					if (i != 0) {
						var currentTime = measurements[i].measurementTime;
						var previousTime = measurements[i-1].measurementTime;

						if (currentTime != previousTime) {
							newBatch = true;
							changeBg();
						} 						
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
				$('#'+displayTableId).html(outputTable);
			}
		})
	}

	function getDeviceData() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getDeviceData'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function getUnits() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					function: 'getUnits'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function setupMap(longitude, latitude, mapId) {
		mapboxgl.accessToken = 'pk.eyJ1IjoidGF1cmFzcCIsImEiOiJja2w2bzl6MmYyaXoyMm9xbzlld3dqaDJnIn0.dJGV_jlSPX-p51ZrQxaBew';
		var map = new mapboxgl.Map({
			container: mapId,
			style: 'mapbox://styles/mapbox/streets-v10',
			center: [longitude, latitude],
			zoom: 14
		});
	
		var marker = new mapboxgl.Marker()
			.setLngLat([longitude, latitude])
			.addTo(map);
	}

	function updateCustoms(alias, location, latitude, longitude) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					deviceAlias: alias,
					customLocation: location,
					latitude: latitude,
					longitude: longitude,
					function: 'updateCustoms'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function updateMaintainDates(subStart, subFinish, lastCal, nextCal) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					subStart: subStart,
					subFinish: subFinish,
					lastCal: lastCal,
					nextCal: nextCal,
					function: 'updateMaintainDates'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function updateDeviceStatus(newStatus) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				deviceId: deviceId,
				newStatus: newStatus,
				function: 'updateDeviceStatus'
			},
			success: function (data) {

			}
		})
	}

	function getRoleId() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					function: 'getRoleId'
				},
				success: function (data) {
					resolve(data);
				}
			})
		})
	}

	function deleteChannel(channelId) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					channelId: channelId,
					function: 'deleteChannel'
				},
				success: function (data) {
					resolve(data);
				}
			})
		})
	}

	function createChannel(channelType, channelName, unitId) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					channelType: channelType,
					channelName: channelName,
					unitId: unitId,
					function: 'createChannel'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function updateDeviceData(deviceName, devicePhone, groupId, productId, deviceTypeId) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					deviceName: deviceName,
					devicePhone: devicePhone,
					groupId: groupId,
					productId: productId,
					deviceTypeId: deviceTypeId,
					function: 'updateDeviceData'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function deleteDevice(deviceId) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				deviceId: deviceId,
				function: 'deleteDevice'
			},
			success: function (data) {
			}
		})
	}

	function getEWBChannels() {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getEWBChannels'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function getMessageHistory(perPage, pageNumber, displayTableId, fromDate, toDate) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				messageHistoryPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				deviceId: deviceId,
				fromDate: fromDate,
				toDate: toDate,
				function: 'loadTable_messageHistory'
			},
			beforeSend: function () {
				$('#loadingOverlay_messageHistory').show();
			},
			success: function (data) {
				$('#loadingOverlay_messageHistory').hide();
				var messageHistory = JSON.parse(data);

				totalCount = messageHistory['totalCount'];
				returnedCount = messageHistory['messageHistory'].length;
				pageControl(totalCount, returnedCount, perPage, pageNumber, 'messageHistory');

				var outputTable = '';
				for (i = 0; i < messageHistory['messageHistory'].length; i++) {
					var alternatingBg = '';
					if (i%2 == 0) {
						alternatingBg = 'bg-gray-100';
					} else {
						alternatingBg = '';
					}
					var dateDisplay = new Date( messageHistory['messageHistory'][i]['timeSent']);
					dateDisplay.setHours(dateDisplay.getHours() + 1);
					dateDisplay = dateDisplay.toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short', year: 'numeric' });

					outputTable += `
					<tr class="border-b border-gray-200 h-10 `+ alternatingBg +`">
						<td class="text-center py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+messageHistory['messageHistory'][i]['fromNumber']+`</td>
						<td class="text-center py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+messageHistory['messageHistory'][i]['toNumber']+`</td>
						<td class="text-left py-2 px-4 text-xs text-gray-600">`+messageHistory['messageHistory'][i]['textBody']+`</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+dateDisplay+`</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+messageHistory['messageHistory'][i]['messageType']+`</td>
					</tr>
					`;
				}
				$('#'+displayTableId).html(outputTable);
			}
		})
	}

	function getTiltDashboardSettings(deviceId) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					function: 'getTiltDashboardSettings'
				},
				success: function (data) {
					data = JSON.parse(data);
					resolve(data);
				}
			})
		})
	}

	function updateTiltBoxPosition(pos_left, pos_top, direction) {
		$.ajax({
			url: './includes/sqlSingleDevice.php',
			type: 'POST',
			data: {
				deviceId: deviceId,
				pos_left: pos_left,
				pos_top: pos_top,
				direction: direction,
				function: 'updateTiltBoxPosition'
			}
		})
	}

	function updateTiltSettings(imageURL, horiz_lt0_text, horiz_mt0_text, horiz_lt0_arrow, horiz_mt0_arrow, vert_lt0_text, vert_mt0_text, vert_lt0_arrow, vert_mt0_arrow) {
		return new Promise(function (resolve, reject) {
			$.ajax({
				url: './includes/sqlSingleDevice.php',
				type: 'POST',
				data: {
					deviceId: deviceId,
					imageURL: imageURL,

					horiz_lt0_text: horiz_lt0_text,
					horiz_mt0_text: horiz_mt0_text,
					horiz_lt0_arrow: horiz_lt0_arrow,
					horiz_mt0_arrow: horiz_mt0_arrow,

					vert_lt0_text: vert_lt0_text,
					vert_mt0_text: vert_mt0_text,
					vert_lt0_arrow: vert_lt0_arrow,
					vert_mt0_arrow: vert_mt0_arrow,

					function: 'updateTiltSettings'
				},
				success: function (data) {
					data = JSON.parse(data);
					if (data.status != 200) {
						alert('Something went wrong!');
					}
				}
			})
		})
	}
	
})