$(document).ready(function () {
	
    var deviceId = window.location.href.split('?id=')[1];
    if (deviceId == null) {
        document.location.href = 'devices.php';
    }

	// Create device object for this page
	var deviceObj = {
		name: '',
		latitude: '',
		longitude: ''
	};


	// Hide loaders
    $('#loadingOverlay_measurements').hide();
    $('#loadingOverlay_alarms').hide();

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

	function generateChart(chart, data){
		// Channel data contains 2 objects. Object 1 is name and id, object 2 is measurements
		for (i=0; i < data.length; i++) {
			var channelName = data[i][0].channelName;
			var measurements = data[i][1];
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
			config.data.datasets.push(newDataset);
			chart.update();
		}
	}

	function showAlarms(perPage, pageNumber) {
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
	// Quick overview			|
	// --------------------------
	$.ajax({
		url: './includes/sqlSingleDevice.php',
		type: 'POST',
		data: {
			deviceId: deviceId,
			function: 'loadOverviewData'
		},
		success: function (data) {
			// console.log(data);
			var data = JSON.parse(data);
			console.log(data);
			
			// Update probe status
			var probeStatus = data.probeStatus.smsAlarmHeader;
			if (probeStatus = 'PROBE ON TRACK') {
				$('#probeStatus').html( '<span class="text-2xl lg:text-4xl font-semibold text-center text-green-500 uppercase">ON TRACK</span>' );
			} else if (probeStatus = 'PROBE OFF TRACK') {
				$('#probeStatus').html( '<span class="text-2xl lg:text-4xl font-semibold text-center text-red-500 uppercase">OFF TRACK</span>' );
			} else {
				$('#probeStatus').html( '<span class="text-2xl lg:text-4xl font-semibold text-center text-bluegray-800 uppercase">UNKNOWN</span>' );
			}
			// Date of probe message
			$('#probeDate').html( data.probeStatus.smsAlarmTime );

			// Update latest measurement
			if (data.numberOfAI == 1) {
				var reading = '<span class="text-3xl lg:text-5xl font-semibold text-green-500">'+data.latestMeasurements[0].measurement+'</span><span class="text-green-500 text-xl">&#176;C</span>';

				$('#latestTempDisplay').html(`
					<div class="flex flex-col justify-center items-center">
						<p class="text-xs uppercase text-bluegray-400">`+data.latestMeasurements[0].channelName+`</p>
						<div class="flex">
							`+reading+`
						</div>
						<span class="text-xs text-bluegray-400 uppercase" id="currentTemp_date">`+data.latestMeasurements[0].measurementTime+`</span>
					</div>
				`);
			} else {
				var reading1 = '<span class="text-3xl lg:text-5xl font-semibold text-green-500">'+data.latestMeasurements[0].measurement+'</span><span class="text-green-500 text-xl">&#176;C</span>';
				var reading2 = '<span class="text-3xl lg:text-5xl font-semibold text-red-500">'+data.latestMeasurements[1].measurement+'</span><span class="text-red-500 text-xl">&#176;C</span>';

				$('#latestTempDisplay').html(`
				<div class="flex flex-col justify-center items-center">
					<p id="currentTemp_channelName" class="text-xs uppercase text-bluegray-400">`+data.latestMeasurements[0].channelName+`</p>
					<div class="flex">
						`+reading1+`
					</div>
					<span class="text-xs text-bluegray-400 uppercase" id="currentTemp_date">`+data.latestMeasurements[0].measurementTime+`</span>
				</div>
				<div class="border-t w-full"></div>
				<div class="flex flex-col justify-center items-center">
					<p id="currentTemp_channelName" class="text-xs uppercase text-bluegray-400">`+data.latestMeasurements[1].channelName+`</p>
					<div class="flex">
						`+reading2+`
					</div>
					<span class="text-xs text-bluegray-400 uppercase" id="currentTemp_date">`+data.latestMeasurements[1].measurementTime+`</span>
				</div>
				`);
			}
		}
	})

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
	function updateCurrentTemp(lat, lon) {
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

	// ------------------
	// Chart setup 		|
	// ------------------
	var timeFormat = 'YYYY/MM/DD HH:mm';

    var config = {
        type: 'line',
		data: {
            datasets: []
		},
		options: {
			scales:     {
				xAxes: [{
					type: "time",
					time: {
						parser: timeFormat,
						unit: 'hour',
						tooltipFormat: 'll HH:mm'
					},
					scaleLabel: {
						display: true,
						labelString: 'Time'
					}
				}],
				yAxes: [{
					scaleLabel: {
						display: true,
						labelString: 'Reading'
					},
				}]
			}
		}
    };

	var ctx = $('#canvas')[0].getContext('2d');
	var chart1 = new Chart(ctx, config);

	colourPalette = {
		backgroundColors: ['rgba(14, 165, 233, 0.6)', 'rgba(239, 68, 68, 0.6)', 'rgba(34, 197, 94, 0.6)', 'rgba(245, 158, 11, 0.6)'],
		borderColors: ['rgba(14, 165, 233, 0.3)', 'rgba(239, 68, 68, 0.3)', 'rgba(34, 197, 94, 0.3)', 'rgba(245, 158, 11, 0.3)'],
		hoverColors: ['rgba(14, 165, 233, 0.9)', 'rgba(239, 68, 68, 0.9)', 'rgba(34, 197, 94, 0.9)', 'rgba(245, 158, 11, 0.9)']
	}

	$.ajax({
		url: './includes/sqlSingleDevice.php',
		type: 'POST',
		data: {
			deviceId: deviceId,
			function: 'loadChart'
		},
		success: function (data) {
			// console.log(data);
			var channelData = JSON.parse(data);
			generateChart(chart1, channelData);
		}
	})

	// ---------------------------
	// Alarm messages setup		 |
	// ---------------------------
	var alarmPageNumber = 1;
	var alarmsPerPage = 5;
	var alarmsTable = $('#table_alarms');
	var totalCount = 0;

	// Show devices on load
	showAlarms(alarmsPerPage, alarmPageNumber);

	$('#next_alarms').on('click', function () {
		alarmPageNumber += 1;
		showAlarms(alarmsPerPage, alarmPageNumber);
	})

	$('#previous_alarms').on('click', function () {
		alarmPageNumber -= 1;
		showAlarms(alarmsPerPage, alarmPageNumber);
	})


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