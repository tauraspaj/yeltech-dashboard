$(document).ready(function () {
    function getGroupCoords() {
		return new Promise(function (resolve, reject) {
			$.ajax({
                url: './includes/sqlHeader.php',
                type: 'POST',
                data: {
                    function: 'getGroupCoords'
                },
                success: function (data) {
                    data = JSON.parse(data);
					resolve(data);
                }
            })
		})
	}

    function getDeviceCoordinates() {
        return new Promise(function (resolve, reject) {
			$.ajax({
                url: './includes/sqlHeader.php',
                type: 'POST',
                data: {
                    function: 'getDeviceCoordinates'
                },
                success: function (data) {
                    data = JSON.parse(data);
					resolve(data);
                }
            })
		})
    }

    // Load the map with group coordinates. If coordinates are null, center on London
    getGroupCoords().then( function(data) {
		if (data.latitude == null || data.longitude == null) {
            data.latitude = 51.508530;
            data.longitude = -0.076132
        }
        
        // Setup map
        mapboxgl.accessToken = 'pk.eyJ1IjoidGF1cmFzcCIsImEiOiJja2w2bzl6MmYyaXoyMm9xbzlld3dqaDJnIn0.dJGV_jlSPX-p51ZrQxaBew';
        var map = new mapboxgl.Map({
            container: 'homeMap',
            style: 'mapbox://styles/mapbox/streets-v10',
            center: [data.longitude, data.latitude],
            zoom: 12
        });

        // Add devices
        getDeviceCoordinates().then( function(data) {
            for (i = 0; i < data.length; i++) {
                new mapboxgl.Marker()
                    .setLngLat([data[i].longitude, data[i].latitude])
                    .setPopup(new mapboxgl.Popup({ offset:25 })
                    .setHTML('<h2>'+data[i].deviceName+'</h2><br><p>'+data[i].latitude+', '+data[i].longitude+'</p>'))
                    .addTo(map);
            }
        })
	})


    // Get alarmed devices
    $.ajax({
        url: './includes/sqlHeader.php',
        type: 'POST',
        data: {
            function: 'getAlarmedDevices'
        },
        success: function (data) {
            var devices = JSON.parse(data);

            var output = '';
            if (devices.length > 0) {
                for (i = 0; i < devices.length; i++) {
                    // Display custom alias
                    var display1 = devices[i].deviceName;
                    var display2 = devices[i].deviceAlias;

                    if (devices[i].deviceAlias == null || devices[i].deviceAlias == '') {
                        display1 = devices[i].deviceName;
                        display2 = '';
                    } else {
                        display1 = devices[i].deviceAlias;
                        display2 = devices[i].deviceName;
                    }
    
                    output += `
                    <!-- Alarm -->
                    <div id="selectDevice" data-id="`+devices[i].deviceId+`" class="flex h-12 text-sm text-gray-700 font-medium items-center pl-2 border border-red-500 rounded bg-gray-100 cursor-pointer hover:bg-gray-200">
                        <div class="flex-1 flex items-center space-x-4 whitespace-nowrap mx-2">
                            <svg class="w-7 h-7 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            <div class="flex flex-col">
                                <p class="font-medium text-base">`+display1+`</p>
                                <p class="text-xs text-gray-400">`+display2+`</p>
                            </div>
                        </div>
                        <div class="flex-1 mx-2 flex justify-center items-center whitespace-nowrap">
                            <p class="bg-red-500 text-xs sm:text-sm px-2 py-1 rounded-full text-white font-medium whitespace-nowrap">`+devices[i].nAlarmsTriggered+` Alarms <span class="hidden sm:inline-block">Triggered!</span></p>
                        </div>
                    </div>
                    <!-- End of alarm -->
                    
                    `;
                }
            } else {
                output = '<p class="font-medium text-center">No devices are alarmed...</p>';
            }

            $('#alarmedList').html(output);
        }
    })

    // Get home data
    $.ajax({
        url: './includes/sqlHeader.php',
        type: 'POST',
        data: {
            function: 'getHomeData'
        },
        success: function (data) {
            data = JSON.parse(data);
            $('#totalDevicesDisplay').html(data['totalDevices']);
            $('#totalUsersDisplay').html(data['totalUsers']);
            $('#totalAlarmsDisplay').html(data['totalAlarms']);
        }
    })

    $('#alarmedList').delegate('#selectDevice', 'click', function() {
        var id = $(this).attr('data-id');
        document.location.href = 'device.php?id='+id;
    })
})