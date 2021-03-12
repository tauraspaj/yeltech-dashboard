$(document).ready(function () {
    function showNav(typesArray) {
        var generateNav = '';
        for (i=0; i<deviceTypesNav.length; i++) {
            generateNav += '<button class="font-semibold text-bluegray-800 text-sm" value="'+deviceTypesNav[i].toUpperCase()+'">'+deviceTypesNav[i]+'</button>';
        }
        generateNav += '<div id="hidden-border" class="flex-auto border-b border-gray-200 pointer-events-none"></div>';
        $('#deviceTypesNav').html(generateNav);
        var notActive = 'outline-none focus:outline-none font-sm uppercase px-6 py-2 border-b border-gray-200 whitespace-nowrap lg:border-b-0 lg:border-r lg:py-4';
        var active = 'outline-none focus:outline-none bg-white font-sm uppercase px-6 py-2 border-b-0 border-l border-r border-t border-gray-200 whitespace-nowrap lg:border-r-0 lg:border-l lg:border-t lg:border-b lg:py-4';
    
        $('#deviceTypesNav').children().addClass(notActive);
    
        $('#deviceTypesNav').children().first().removeClass(notActive);
        $('#deviceTypesNav').children().first().addClass(active);
    
        $('#sections').children().first().show();
    
        $('#deviceTypesNav').children().on('click', function() {
            $('#deviceTypesNav').children().removeClass(active);
            $('#deviceTypesNav').children().addClass(notActive);
            $(this).removeClass(notActive);
            $(this).addClass(active);
    
            // Display appropriate section
            $('#sections').children().hide();
            $('#'+$(this).val().toUpperCase()).show();
        })
    }

    function isFieldEmpty(field, border) {
		var bool = true;
		if (field.val().length === 0) {
			field.addClass(border);
			bool = true;
		} else {
			field.removeClass(border);
			bool = false;
		}

		return bool;
	}

    function displayChannels(displayArr) {
        // console.log(displayArr);
        // console.log(displayArr.length);
        var outputString = "";
        for (i=0; i<displayArr.length; i++) {
                outputString += `
                <div data-id="`+displayArr[i].channelName+`:`+displayArr[i].channelTypeId+`" class="flex-auto max-w-xs bg-gray-50 border border-gray-300 rounded text-sm uppercase h-10 flex justify-center items-center space-x-2 cursor-pointer transition-all hover:bg-red-200 hover:text-red-900 hover:border-red-400">
                    <p>`+displayArr[i].channelName+`(`+displayArr[i].unitName+`)</p>
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
				</div>
                `;
        }
        channelsDisplay.html(outputString);
        $('#channelName').val('');
        $('#channelType').prop('selectedIndex', 0);
        $('#channelUnitSelect').prop('selectedIndex',0);

        $('#channelsDisplay').children().on('click', function() {
            var dataid = $(this).attr('data-id');
            var toDel = dataid.split(':');
            for (i=0; i<channelsArr.length; i++) {
                if (channelsArr[i].channelName == toDel[0].toUpperCase() && channelsArr[i].channelTypeId == toDel[1].toUpperCase()) {
                    channelsArr.splice(i, 1);
                }
            }
            displayChannels(channelsArr);
        })
    }

    function checkArray(testArray, testChannel, testType) {
        var returnAns = false;
        if (testArray.length != 0) {
            for (i=0; i<testArray.length; i++) {
                if (testArray[i].channelName == testChannel.toUpperCase() && testArray[i].channelTypeName == testType.toUpperCase()) {
                    returnAns = false;
                    break;
                } else {
                    returnAns = true;
                }
            }
        } else {
            returnAns = true;
        }

        return returnAns;
    }

    function checkIfExists(checkInput, inTable, inColumn) {
        var returnAns = false;
        $.post('./includes/sqlNewDevice.php', {checkInput: checkInput, inTable: inTable, inColumn: inColumn, function: 'checkIfExists'}, function(data) {
            // Returns true if device name already exists
            if (data != 0) {
                returnAns = false;
                $('#'+inColumn).removeClass('border-green-500');
                $('#'+inColumn).addClass('border-red-500');
                successChecks[inColumn] = false;
            } else {
                successChecks[inColumn] = true;
                $('#'+inColumn).removeClass('border-red-500');
            }
        })
    }

    function validateDevicePhone(field) {
        // All numbers must start with international prefix
        // Checks:
        // First char must be +
        // Length longer than 7
        // Must not contain any letters
        // Must not contain any spaces
        var check = false;
        var number = field.val();
        if (number[0] == '+' && number.length > 7) {
            // Test to only contain numbers after the first +
            var regex = /^[0-9]+$/;
            var test = regex.test( number.substr(1,number.length) );

            if (test == true) {
                // Means only allowed numbers exist
                check = true;
            } else {
                // Contains other values than legal numbers
                check = false;
            }
        } else {
            // Does not start with +
            check = false;
        }

        // Process red border if false
        if (check == false) {
            field.addClass('border-red-500');
        } else {
            field.removeClass('border-red-500');
        }

        return check;
    }
    
    // Generate nav bar and show appropriate section
    var deviceTypesNav = [];
    $("#sections > div").each((index, elem) => {
        deviceTypesNav.push(elem.id);
      });
    showNav(deviceTypesNav);

    var successChecks = {
        productType: false,
		deviceType: false, 
		deviceName: false, 
		devicePhone: false, 
		groupId: false,
        channels: false
    };

    $('#productType').on('change blur', function() {
        var dataid = $(this).find(':selected').attr('data-id');
        if (dataid != -1) {
            $(this).removeClass('border-red-500');
            successChecks.productType = true;
        } else {
            $(this).addClass('border-red-500');
            successChecks.productType = false;
        }
    })
    
    $('#deviceType').on('change blur', function() {
        var dataid = $(this).find(':selected').attr('data-id');
        if (dataid != -1) {
            $(this).removeClass('border-red-500');
            successChecks.deviceType = true;
        } else {
            $(this).addClass('border-red-500');
            successChecks.deviceType = false;
        }
    })

    $('#deviceName').on('blur', function() {
        var input = $(this).val();

        if (!isFieldEmpty($(this), 'border-red-500')) {
            checkIfExists($.trim(input), 'devices', 'deviceName');
        }
    })

    $('#devicePhone').on('blur', function() {
        var field = $(this);
        var input = $(this).val();

        if (!isFieldEmpty($(this), 'border-red-500')) {
            if (validateDevicePhone(field)) {
                checkIfExists(input, 'devices', 'devicePhone');
            }
        }
    })

    $('#groupId').on('change blur', function() {
        var dataid = $(this).find(':selected').attr('data-id');
        if (dataid != -1) {
            $(this).removeClass('border-red-500');
            successChecks.groupId = true;
        } else {
            $(this).addClass('border-red-500');
            successChecks.groupId = false;
        }
    })

    // channelsArr is an object containing data about each added channel
    var channelsArr = [];

    var channelsDisplay = $('#channelsDisplay');
    
    // Each channel contains 5 elements => id, name, unit, interface id, interface name
    // First check if name is empty or type = -1.
    // Then check if the 2d record already exists.
    // On click of the channels, display alert to confirm they want to delete channel, if so, delete 2d record from array and display channels again.
    // If channelType is AI, then display option to enter unit. Initially, this field is disabled.
    var unitField = $('#channelUnit');
    unitField.hide();

    $('#addChannel').on('click', function(e) {
        e.preventDefault();
        var channel = $('#channelName');

        var channelTypeId = $('#channelType').find(':selected').attr('data-id');
        var channelTypeName = $.trim($('#channelType').find(':selected').val());

        var unitId = $('#channelUnitSelect').find(':selected').attr('data-id');
        var unitName = $.trim($('#channelUnitSelect').find(':selected').val());
        
        if (channel != "" && channelTypeId != '-1') {
            if (channelTypeName == 'AI' && unitId == '-1') {
                // do nothing if unitid is not selected when type is AI
            } else {
                // this will pass 
                if (unitId == '-1') {
                    unitId = '';
                    unitName = '';
                };
                if (checkArray(channelsArr, channel.val(), channelTypeName)) {
                    channelsArr.push({
                        channelName: channel.val().toUpperCase(),
                        channelTypeId: channelTypeId,
                        unitId: unitId,
                        unitName: unitName,
                        channelTypeName: channelTypeName
                    })
                    $('#channelType').prop('selectedIndex',0);
                    $('#channelUnitSelect').prop('selectedIndex',0);
                    unitField.hide();
                    console.log(channelsArr);
                    displayChannels(channelsArr);
                }
            }
        } 
    })

    $('#channelType').on('change', function() {
        if ($('#channelType').find(':selected').val() == 'AI') {
            unitField.prop('selectedIndex',0);
            unitField.show();
        } else {
            unitField.hide();
            unitField.prop('selectedIndex',0);
        }
    })

    $('#calibrationCheckbox').on('change', function() {
        var labelBg = $('#calibrationLabel');
        var bubble = $('#calibrationLabel > div');
        var checkClass = 'bg-green-600 translate-x-6';
        var uncheckClas = 'bg-red-600 translate-x-0';

        if ($(this).is(':checked')) {
            labelBg.removeClass('bg-red-200');
            labelBg.addClass('bg-green-200')

            bubble.removeClass(uncheckClas);
            bubble.addClass(checkClass);
            $('#calibrationFields').slideDown('fast');
        } else {
            labelBg.removeClass('bg-green-200')
            labelBg.addClass('bg-red-200');

            bubble.removeClass(checkClass);
            bubble.addClass(uncheckClas);
            $('#calibrationFields').slideUp('fast');
            $('#nextCalibrationDate, #lastCalibrationDate').val('');
        }
    })

    $('#subscriptionCheckbox').on('change', function() {
        var labelBg = $('#subscriptionLabel');
        var bubble = $('#subscriptionLabel > div');
        var checkClass = 'bg-green-600 translate-x-6';
        var uncheckClas = 'bg-red-600 translate-x-0';

        if ($(this).is(':checked')) {
            labelBg.removeClass('bg-red-200');
            labelBg.addClass('bg-green-200')

            bubble.removeClass(uncheckClas);
            bubble.addClass(checkClass);
            $('#subscriptionFields').slideDown('fast');
        } else {
            labelBg.removeClass('bg-green-200')
            labelBg.addClass('bg-red-200');

            bubble.removeClass(checkClass);
            bubble.addClass(uncheckClas);
            $('#subscriptionFields').slideUp('fast');
            $('#subStartDate, #subFinishDate').val('');
        }
    })

    $('form').on('submit', function(e) {
        e.preventDefault();
        var nextCalibrationDate = '';
        var lastCalibrationDate = '';
        var subStart = '';
        var subFinish = '';
        if ($('#calibrationCheckbox').is(':checked')) {
            if ($('#nextCalibrationDate').val() != '') {
                nextCalibrationDate = $('#nextCalibrationDate').val();
            } else {
                nextCalibrationDate = '';
            }
            if ($('#lastCalibrationDate').val() != '') {
                lastCalibrationDate = $('#lastCalibrationDate').val();
            } else {
                lastCalibrationDate = '';
            }
        } else {
            nextCalibrationDate = '';
            lastCalibrationDate = '';
        }
        
        if($('#subscriptionCheckbox').is(':checked')) {
            if ($('#subStartDate').val() != '') {
                subStart = $('#subStartDate').val();
            } else {
                subStart = '';
            }
            if ($('#subFinishDate').val() != '') {
                subFinish = $('#subFinishDate').val();
            } else {
                subFinish = '';
            }
        } else {
            subStart = '';
            subFinish = '';
        }
        
        var submitArr = [];
        if (channelsArr.length > 0) {
            for (i=0; i<channelsArr.length; i++) {
                submitArr.push({
                    channelName: channelsArr[i].channelName,
                    channelTypeId: channelsArr[i].channelTypeId,
                    unitId: channelsArr[i].unitId
                })
            }
            successChecks.channels = true;
        } else {
            successChecks.channels = false;
        }

        console.log(successChecks);
        if (!(Object.values(successChecks).indexOf(false) > -1)) {
            // All checks passed
            var productType = $('#productType').find(':selected').attr('data-id');
            var deviceType = $('#deviceType').find(':selected').attr('data-id');
            var deviceName = $.trim($('#deviceName').val().toUpperCase());
            var devicePhone = $('#devicePhone').val();
            var groupId = $('#groupId').find(':selected').attr('data-id');
            var submitChannels = JSON.stringify(submitArr);
    
            $.post('./includes/sqlNewDevice.php', {
                productType: productType,
                deviceType: deviceType,
                deviceName: deviceName,
            	devicePhone: devicePhone,
            	groupId: groupId,
                submitChannels: submitChannels,

                nextCalibrationDate: nextCalibrationDate,
                lastCalibrationDate: lastCalibrationDate,
                subStart: subStart,
                subFinish: subFinish,

                function: 'create'
            }, function(data) {
                alert(data);
                $('form').trigger("reset");
                $('#subscriptionCheckbox, #calibrationCheckbox').prop('checked', false);
                $('#subscriptionCheckbox, #calibrationCheckbox').trigger('change');
                channelsArr = [];
                displayChannels(channelsArr);
            });
        }
    })
    
    $('#simMessage').on('click', function() {
        $.post('./includes/simulateMessage.php', {
            to: '07807165552',
            from: '+447720512900',
            textBody: 'RTMU 2671\nDWTS\n120320210905\n1,0,29.3'
  
            // textBody: 'RTMU 2671\nSTATUS MESSAGE\nON\nTOTAL:1\nSQ : ERR:0, MIN:7, MAX:7, AVG:7\nBER: ERR:1, MIN:-, MAX:-, AVG:-\nSUA:0, FUA:0\n63.446114,10.899592'
            // from: '+447231837123',
            // textBody: 'RTMU 2717\nALARM MESSAGE\nDI 1\nPROBE ON TRACK'
            // textBody: 'RTMU 2537\nDWTS\n261220192046\n2,0,7.6,8.0,30,7.6,7.8,60,7.7,8.2,90,8.1,8.2,120,8.2,8.4,150,8.2,8.2,180,7.6,8.1,210,7.3,7.3,240,7.1,7.2,270,6.8,7.0,300,6.7,7.0'
            // textBody: 'RTMU 2671\nSTATUS MESSAGE\nON\nTOTAL:1\nSQ : ERR:0, MIN:7, MAX:7, AVG:7\nBER: ERR:1, MIN:-, MAX:-, AVG:-\nSUA:0, FUA:0\n51.504935,-0.431328'
		}, function(data) {
			alert(data);
		});
    })
})