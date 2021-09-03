$(document).ready(function () {
	$('#loadingOverlay').hide();
	function pageControl(total, returned, perPage, pageNumber) {
		$('#devicesTotal').html(total);

		var offset = 0;
		if (pageNumber == 1) {
			offset = 0
		} else {
			offset = (pageNumber - 1) * perPage;
		}

		if (returned < perPage) {
			$('#devicesRange').html(eval(offset + 1) + '-' + total);
		} else {
			$('#devicesRange').html(eval(offset + 1) + '-' + eval(offset + perPage));
		}

		if (total == 0) {
			$('#devicesRange').html(total);
		}

		// Previous button should be disabled on page 1
		if (pageNumber <= 1) {
			$("#previousDevicesButton").prop('disabled', true);
		} else {
			$("#previousDevicesButton").prop('disabled', false);
		}

		// Next button should be disabled when all the rows have been returned
		if (total <= pageNumber * perPage) {
			$("#nextDevicesButton").prop('disabled', true);
		} else {
			$("#nextDevicesButton").prop('disabled', false);
		}
	}

	

	function showDevices(perPage, pageNumber) {
		$.ajax({
			url: './includes/sqlDevicesTable.php',
			type: 'POST',
			data: {
				devicesPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				groupId: groupFilter,
				pageSearchString: pageSearchString,
				selectedProducts: selectedProducts,
				function: 'showDevices'
			},
			success: function (data) {
				// console.log(data);
				var devices = JSON.parse(data);
				console.log(devices);

				totalCount = devices[devices.length - 1]['totalRows'];
				returnedCount = devices.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				var outputTable = '';
				for (i = 0; i < devices.length - 1; i++) {
					// Display custom alias
					var display1 = devices[i].deviceName;
					var display2 = devices[i].deviceAlias;
					if (devices[devices.length - 1]['powerRole'] == 1 || devices[devices.length - 1]['powerRole'] == 2) {
						display1 = devices[i].deviceName;
						display2 = '<span class="bg-lightblue-500 text-xs px-2 text-white inline font-medium rounded text-center">'+ devices[i].groupName + '</span>';
					} else {
						if (devices[i].deviceAlias == null || devices[i].deviceAlias == '') {
							display1 = devices[i].deviceName;
							display2 = '';
						} else {
							display1 = devices[i].deviceAlias;
							display2 = devices[i].deviceName;
						}
					}

					// Display group for super admins
					var groupDisplay = '';
					if (devices[devices.length - 1]['powerRole'] == 1 || devices[devices.length - 1]['powerRole'] == 2) {
						groupDisplay = `<span class="bg-lightblue-500 text-xs px-2 text-white inline font-medium rounded text-center">`+ devices[i].groupName + `</span>`
					}

					// Process location, display - if not existent. Display custom location if set
					var location = '-';
					if (devices[i].customLocation == null || devices[i].customLocation == '') {
						if (devices[i].latitude != null && devices[i].longitude != null) {
							location = devices[i].latitude + ', ' + devices[i].longitude;
						}
					} else {
						location = devices[i].customLocation;
					}

					// Process last reading
					var lastReading = '-';
					if (devices[i].measurement != null && devices[i].measurementTime != null) {
						var timestamp = new Date( Date.parse(devices[i].measurementTime) );
						var timestamp = timestamp.toLocaleString('en-GB', { hour: 'numeric', minute:'numeric', day: 'numeric', month: 'short' });

						lastReading = `
							<p class="text-center text-sm font-semibold text-gray-800">
								`+devices[i].measurement + ` ` + devices[i].unitName + `
							<p>
							<p class="text-center text-xs text-gray-500">
								`+timestamp+`
							</p>
						`
					}

					// Process alarm
					var alarmDisplay = '';
					var alarmList = '';
					if (devices[i].alarmsTriggered == 0) {
						alarmDisplay = `
						<div class="flex justify-center items-center h-8 border rounded bg-gray-300">
							<div class="bg-gray-300 h-full w-6 text-white flex justify-center items-center">
								<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
							</div>
							<p class="text-gray-500 text-xs font-medium px-2 whitespace-nowrap bg-white h-full rounded-r flex justify-center items-center">0 ALARMS</p>
						</div>`;
					} else {
						alarm = '<span class="text-red-500 whitespace-nowrap font-medium">'+devices[i].alarmsTriggered+' TRIGGERED</span>';
						if (devices[i].alarmsTriggered == 1) {
							numberOfAlarms = devices[i].alarmsTriggered + ' ALARM';
						} else {
							numberOfAlarms = devices[i].alarmsTriggered + ' ALARMS';
						}
						alarmDisplay = `
						<div class="flex justify-center items-center h-8 border rounded border-red-400">
							<div class="bg-red-400 h-full w-6 text-white flex justify-center items-center">
								<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
							</div>
							<p class="text-red-500 text-xs font-medium px-2 whitespace-nowrap bg-white h-full rounded-r flex justify-center items-center">`+numberOfAlarms+`</p>
						</div>`;

						// Display list of alarms
						for(j = 0; j < devices[i].alarms.length; j++) {
							if (devices[i].alarms[j].alarmDescription == null) {
								descriptionDisplay = '-';
							} else {
								descriptionDisplay = devices[i].alarms[j].alarmDescription;
							}
							alarmList += '<span class="bg-red-500 text-white rounded text-xs px-2 mr-2 font-medium">'+descriptionDisplay+'</span>'
						}
					}

					// Process status
					var statusDisplay = '';
					var sideStripColour = '';
					if (devices[i].deviceStatus == 0) {
						sideStripColour = 'border-red-500';
						statusDisplay = '<span class="bg-red-500 rounded px-2 text-white text-xs uppercase font-medium">Offline</span>'
					} else if (devices[i].deviceStatus == 1) {
						sideStripColour = 'border-green-500';
						statusDisplay = '<span class="bg-green-500 rounded px-2 text-white text-xs uppercase font-medium">Online</span>';
					}

					// Process next calibration date. This should go orange if you're within the last 30 days and red if you're past the date.
					var nextCalibrationDate = '-';
					if (devices[i].nextCalibrationDue != null) {
						var currentDate = new Date();
						var nextCalibrationDate = new Date( Date.parse(devices[i].nextCalibrationDue) );

						// Calculate month warning
						var monthWarning = new Date(nextCalibrationDate.getFullYear(), nextCalibrationDate.getMonth()-1, nextCalibrationDate.getDate());

						// Format to dd month yyyy
						var formattedDate = nextCalibrationDate.toLocaleString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
						if ( currentDate >= nextCalibrationDate ) {
							nextCalibrationDate = '<svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg><p class="text-red-500 text-sm font-medium">' + formattedDate + '</p>';
						} else if ( currentDate < nextCalibrationDate && currentDate >= monthWarning ) {
							nextCalibrationDate = '<svg class="w-4 h-4 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg><p class="text-yellow-500 text-sm font-medium">' + formattedDate + '</p>';
						} else if (currentDate < monthWarning) {
							nextCalibrationDate = '<p class="text-gray-800 text-sm font-medium">' + formattedDate + '</p>';
						}
					}


					outputTable += `
					<div class="flex flex-col">
						<div id="row" class="border-l-2 `+sideStripColour+` rounded-tl">
							<div class="bg-white border border-l-0 rounded-tr flex py-4 pr-6 hover:bg-gray-50">

								<div class="flex-none w-16 lg:w-24 flex justify-center lg:items-center mt-2 lg:mt-0 text-gray-600">
									<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
								</div>

								<div class="flex-auto grid grid-cols-2 md:grid-cols-5 gap-6">
									<div class="col-span-1 flex flex-col justify-center">
										<p class="text-gray-400 text-xs uppercase whitespace-nowrap">`+display2+`</p>
										<p class="text-gray-900 text-sm md:text-base font-bold">`+display1+`</p>
									</div>

									<div class="col-span-1 flex justify-center lg:justify-start items-center">
										`+alarmDisplay+`
									</div>
				
									<div class="col-span-1 flex md:justify-center lg:justify-start items-center space-x-2">
										<div>
										<p class="text-gray-400 text-xs hidden xl:block">Reading</p>
										</div>
										<div>
											`+lastReading+`
										</div>
									</div>
				
									<div class="col-span-1 flex justify-center lg:justify-start items-center space-x-2">
										<div>
											<p class="text-gray-400 text-xs hidden xl:block">Location</p>
										</div>
										<div>
											<p class="text-sm font-medium text-gray-800">
												`+location+`
											<p>
										</div>
									</div>

									<div class="col-span-2 md:col-span-1 h-full flex items-center justify-end md:justify-center">
										<a id="dashboard_button" href="./device.php?id=`+ devices[i].deviceId + `" class="flex justify-center items-center py-2 px-2 border text-gray-500 space-x-2 bg-white rounded cursor-pointer transition hover:bg-gray-100" title="Dashboard">
											<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
											<p class="text-xs font-medium md:hidden xl:block">Dashboard</p>
										</a>
									</div>
								</div>

							</div>
						</div>

						<div id="row_dropdown" class="bg-white border border-t-0 flex flex-col px-12 md:px-16 py-8 space-y-4 rounded-b hidden">
							<div class="flex items-center">
									<div class="w-32 md:w-72 text-gray-600 text-sm">Status</div>
									<div class="flex items-center">
										`+statusDisplay+`
									</div>
							</div>
							<div class="flex items-center">
									<div class="w-32 md:w-72 text-gray-600 text-sm">Next Calibration</div>
									<div class="flex items-center">`+nextCalibrationDate+`</div>
							</div>
							<div class="flex items-center">
									<div class="w-32 md:w-72 text-gray-600 text-sm">Subscription Finish</div>
									<div class="text-sm font-medium text-gray-800">31 July 2022</div>
							</div>
							<div class="flex items-center">
									<div class="w-32 md:w-72 text-gray-600 text-sm">Alarms</div>
									<div class="flex items-center">`+alarmList+`</div>
							</div>
						</div>
					</div>
					`;
				}
				devicesTable.html(outputTable);
			}
		})
	}

	// ------------------
	// End of functions |
	// ------------------


	var devicePageNumber = 1;
	var devicesPerPage = 7;
	var devicesTable = $('#devicesTableBody');
	var totalCount = 0;

	// ! Initial table load settings
	var pageSearchString = '';
	var groupFilter = 'devices.groupId';
	var selectedProducts = 'devices.productId';

	// Show devices on load
	showDevices(devicesPerPage, devicePageNumber);

	// ! Listen to changes on product checkboxes
	var products = $('#productsFilter').children().last().children().children();
	// alert( roles[0].attr('data-id') );
	products.on('change', function() {
		selectedProducts = [];
		products.each(function() {
			if ($(this).is(':checked')) {
				selectedProducts.push($(this).attr('data-id'));
			}
		})
		devicePageNumber = 1;
		showDevices(devicesPerPage, devicePageNumber);
	})

	// ! Slide down content functionality
	$('#groupsTitle, #productsTitle').on('click', function() {
		// $(this).siblings().last().slideToggle('fast');
		var icons = $(this).children('#icons');
		var body = $(this).siblings().last()
		if (body.is(':hidden')) {
			icons.children('#icon_plus').toggleClass('rotate-180')
			icons.children('#icon_plus').fadeOut(100, function() {
				icons.children('#icon_minus').fadeIn(100);
			});
			body.slideDown("fast");
		} else {
			icons.children('#icon_minus').toggleClass('rotate-180')
			icons.children('#icon_minus').fadeOut(100, function() {
				icons.children('#icon_plus').fadeIn(100);
			});
			body.slideUp("fast");
		}
	})

	// ! Listen for search string
	var debounceTimeout = null;
	$('#pageSearchBar').on('keyup', function() {
		pageSearchString = $(this).val();
		
		clearTimeout(debounceTimeout);
		debounceTimeout = setTimeout(function() {
			devicePageNumber = 1;
			showDevices(devicesPerPage, devicePageNumber);
		}, 200);
	})

	// ! Listen for changes on group filter
	$('#groupFilter').change(function () {
		groupFilter = $(this).find(':selected').attr('data-id');
		devicePageNumber = 1;
		showDevices(devicesPerPage, devicePageNumber);
	})

	// ! Next button
	$('#nextDevicesButton').on('click', function () {
		devicePageNumber += 1;
		showDevices(devicesPerPage, devicePageNumber);
	})

	// ! Previous button
	$('#previousDevicesButton').on('click', function () {
		devicePageNumber -= 1;
		showDevices(devicesPerPage, devicePageNumber);
	})

	// ! Row dropdown functionality
	$('#devicesTableBody').delegate('#row', 'click', function() {
		$(this).siblings('#row_dropdown').slideToggle();
	})

})