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
			beforeSend: function () {
				$('#loadingOverlay').show();
			},
			success: function (data) {
				$('#loadingOverlay').hide();
				// console.log(data);
				var devices = JSON.parse(data);

				totalCount = devices[devices.length - 1]['totalRows'];
				returnedCount = devices.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				console.log(devices);

				var outputTable = '';
				for (i = 0; i < devices.length - 1; i++) {
					// Display custom alias
					var display1 = devices[i].deviceName;
					var display2 = devices[i].deviceAlias;
					if (devices[i].deviceAlias == null || devices[i].deviceAlias == '') {
						display1 = devices[i].deviceName;
						display2 = '';
					} else {
						if (devices[devices.length - 1]['powerRole'] == 1 || devices[devices.length - 1]['powerRole'] == 2) {
							display1 = devices[i].deviceName;
							display2 = devices[i].deviceAlias;
						} else {
							display1 = devices[i].deviceAlias;
							display2 = devices[i].deviceName;
						}
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
							nextCalibrationDate = '<span class="bg-red-100 text-red-500 font-medium rounded-full px-2 py-1">' + formattedDate + '</span>';
						} else if ( currentDate < nextCalibrationDate && currentDate >= monthWarning ) {
							nextCalibrationDate = '<span class="text-yellow-500">' + formattedDate + '</span>';
						} else if (currentDate < monthWarning) {
							nextCalibrationDate = '<span>' + formattedDate + '</span>';
						}
					}

					// Process alarm
					var alarm = '';
					var rowFormat = 'bg-white hover:bg-gray-100';
					if (devices[i].alarmsTriggered == 0) {
						alarm = '<span class="text-green-500">None</span>';
					} else {
						alarm = '<span class="text-red-500 whitespace-nowrap font-medium">'+devices[i].alarmsTriggered+' TRIGGERED</span>';
						display1 = '<span class="text-red-500 hover:text-red-600">'+display1+'</span>';
						display2 = '<span class="text-red-400">'+display2+'</span>';
					}

					var lastReading = '-';
					if (devices[i].measurement != null && devices[i].measurementTime != null) {
						var timestamp = new Date( Date.parse(devices[i].measurementTime) );
						var timestamp = timestamp.toLocaleString('en-GB', { hour: 'numeric', minute:'numeric', day: 'numeric', month: 'long' });
						lastReading = '<span class="font-medium">'+devices[i].measurement + ' ' + devices[i].unitName + '</span><br><span class="text-gray-400 text-xs">' + timestamp + '</span>';
					}

					// Process status
					var status = '';
					if (devices[i].deviceStatus == 0) {
						status = '<span class="flex-none bg-red-100 h-8 w-8 flex justify-center items-center rounded-full font-semibold uppercase text-red-500"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3.707 2.293a1 1 0 00-1.414 1.414l6.921 6.922c.05.062.105.118.168.167l6.91 6.911a1 1 0 001.415-1.414l-.675-.675a9.001 9.001 0 00-.668-11.982A1 1 0 1014.95 5.05a7.002 7.002 0 01.657 9.143l-1.435-1.435a5.002 5.002 0 00-.636-6.294A1 1 0 0012.12 7.88c.924.923 1.12 2.3.587 3.415l-1.992-1.992a.922.922 0 00-.018-.018l-6.99-6.991zM3.238 8.187a1 1 0 00-1.933-.516c-.8 3-.025 6.336 2.331 8.693a1 1 0 001.414-1.415 6.997 6.997 0 01-1.812-6.762zM7.4 11.5a1 1 0 10-1.73 1c.214.371.48.72.795 1.035a1 1 0 001.414-1.414c-.191-.191-.35-.4-.478-.622z"></path></svg></span>';
					} else if (devices[i].deviceStatus == 1) {
						status = '<span class="flex-none bg-green-100 h-8 w-8 flex justify-center items-center rounded-full font-semibold uppercase text-green-500"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"></path></svg></span>'
					}

					outputTable += `
					<tr class="`+rowFormat+` h-12">
						<td class="text-left py-2 px-4 text-sm flex space-x-3 items-center">
							`+ status +`
							<a href="./device.php?id=`+ devices[i].deviceId + `" class="flex flex-col justify-center">
								<span title="View device" class="font-semibold whitespace-nowrap text-lightblue-500 cursor-pointer hover:text-lightblue-600">`+ display1 + `</span>
								<span class="text-gray-400">`+ display2 + `</span>
							</a>
						</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap capitalize">`+ devices[i].groupName + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+ location + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+ nextCalibrationDate + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+ lastReading + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+ alarm +`</td>
						<td class="text-center px-8 md:px-12">
							<a href="./device.php?id=`+ devices[i].deviceId + `">
								<button class="focus:outline-none text-xs text-gray-600 uppercase bg-gray-50 border border-gray-300 rounded font-medium py-1 px-2 hover:bg-gray-200" title="View device">
									View
								</button>
							</a>
						</td>
					</tr>
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
	var devicesPerPage = 10;
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

})