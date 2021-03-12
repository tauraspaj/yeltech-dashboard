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
				console.log(data);
				var devices = JSON.parse(data);

				totalCount = devices[devices.length - 1]['totalRows'];
				returnedCount = devices.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				// console.log(devices);

				var outputTable = '';
				for (i = 0; i < devices.length - 1; i++) {
					// Display custom alias
					var display1 = devices[i].deviceName;
					var display2 = devices[i].deviceAlias;
					if (devices[i].deviceAlias == null) {
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
					if (devices[i].customLocation == null) {
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
							nextCalibrationDate = '<span class="text-red-500">' + formattedDate + '</span>';
						} else if ( currentDate < nextCalibrationDate && currentDate >= monthWarning ) {
							nextCalibrationDate = '<span class="text-yellow-500">' + formattedDate + '</span>';
						} else if (currentDate < monthWarning) {
							nextCalibrationDate = '<span>' + formattedDate + '</span>';
						}
					}

					// Process alarm
					var alarm = '-';
					if (true) {
						alarm = '<span class="text-yellow-500">COMING</span>';
					}

					// Process status
					var status = '';
					if (devices[i].deviceStatus == 0) {
						status = '<span class="bg-red-400 rounded-full px-2 ml-2 font-semibold uppercase text-white text-xs">OFF</span>';
					} else if (devices[i].deviceStatus == 1) {
						status = '<span class="bg-green-400 rounded-full px-2 ml-2 font-semibold uppercase text-white text-xs">ON</span>'
					}

					outputTable += `
					<tr class="hover:bg-bluegray-100 border-b border-gray-200 h-12">
						<td class="text-left py-2 px-4 text-sm"><div class="flex items-center"><span id="select" data-id="`+ devices[i].deviceId + `" title="View device" class="font-semibold whitespace-nowrap text-lightblue-500 cursor-pointer hover:text-lightblue-600">`+ display1 + `</span>`+ status +`</div><span class="text-gray-400">`+ display2 + `</span></td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 capitalize">`+ devices[i].groupName + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ location + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ nextCalibrationDate + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ alarm +`</td>
						<td class="text-center" id="select" data-id="`+ devices[i].deviceId + `">
							<button class="focus:outline-none text-xs text-gray-600 uppercase bg-gray-50 border border-gray-300 rounded font-medium py-1 px-2 hover:bg-gray-200">
								View
							</button>
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

	// ! Start listening for clicks on any devices
	devicesTable.delegate('#select', 'click', function () {
		var id = $(this).attr('data-id');
		document.location.href = 'device.php?id='+id;
	})

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