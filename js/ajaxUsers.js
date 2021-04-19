$(document).ready(function () {
	$('#loadingOverlay').hide();
	function pageControl(total, returned, perPage, pageNumber) {
		$('#usersTotal').html(total);

		var offset = 0;
		if (pageNumber == 1) {
			offset = 0
		} else {
			offset = (pageNumber - 1) * perPage;
		}

		if (returned < perPage) {
			$('#usersRange').html(eval(offset + 1) + '-' + total);
		} else {
			$('#usersRange').html(eval(offset + 1) + '-' + eval(offset + perPage));
		}

		if (total == 0) {
			$('#usersRange').html(total);
		}

		// Previous button should be disabled on page 1
		if (pageNumber <= 1) {
			$("#previousUsersButton").prop('disabled', true);
		} else {
			$("#previousUsersButton").prop('disabled', false);
		}

		// Next button should be disabled when all the rows have been returned
		if (total <= pageNumber * perPage) {
			$("#nextUsersButton").prop('disabled', true);
		} else {
			$("#nextUsersButton").prop('disabled', false);
		}
	}


	function showUsers(perPage, pageNumber) {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				usersPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				searchString: userSearchString,
				groupId: groupFilter, 
				roles: selectedRoles,
				sendingTypes: selectedSendingTypes,
				function: 'showUsers'
			},
			beforeSend: function () {
				$('#loadingOverlay').show();
			},
			success: function (data) {
				$('#loadingOverlay').hide();
				var users = JSON.parse(data);

				totalCount = users[users.length - 1]['totalRows'];
				returnedCount = users.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				var outputTable = '';
				$role = '';
				for (i = 0; i < users.length - 1; i++) {
					// Apply formatting
					switch ((users[i].role).toUpperCase()) {
						case ('Super Admin'.toUpperCase()):
							$role = '<span class="bg-red-100 text-red-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Super Admin</span>';
							break;

						case ('Yeltech Admin'.toUpperCase()):
							$role = '<span class="bg-lightblue-100 text-lightblue-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Yeltech Admin</span>';
							break;

						case ('Group Admin'.toUpperCase()):
							$role = '<span class="bg-green-100 text-green-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Group Admin</span>';
							break;

						case ('Standard User'.toUpperCase()):
							$role = '<span class="bg-gray-100 text-gray-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Standard User</span>';
							break;

						default:
							$role = '<span class="bg-gray-100 text-gray-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Role Unknown</span>';
							break;
					}

					outputTable += `
					<tr class="hover:bg-bluegray-100 border-b border-gray-200">
						<td class="text-left py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+ users[i].fullName + `<br><span class="font-normal text-bluegray-400">` + users[i].email + `</span</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+ users[i].groupName + `</td>
						<td class="text-center py-2 px-4 cursor-default">`+ $role + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ users[i].phoneNumber + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ users[i].sendingType + `</td>
						<td class="text-center" id="select" data-id="`+ users[i].userId + `">
							<button class="focus:outline-none text-xs text-gray-600 uppercase bg-gray-50 border border-gray-300 rounded font-medium p-1 hover:bg-gray-200">
								Actions
							</button>
						</td>
					</tr>
					`;
				}
				usersTable.html(outputTable);
			}
		})
	}

	function showLatestUsers() {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				function: 'showLatestUsers'
			},
			success: function (data) {
				var data = JSON.parse(data);
				// alert(data);
				var outputString = '';
				for (i = 0; i < data.length; i++) {
					var dateDisplay = new Date( data[i].createdAt );
					dateDisplay = dateDisplay.toLocaleString('en-GB', {day: 'numeric', month: 'short', year: 'numeric' });

					outputString += `
						<div class="grid grid-cols-2 py-1">
							<div class="font-medium mx-4 lg:mx-1 xl:mx-4 whitespace-nowrap overflow-ellipsis overflow-hidden">`+data[i].fullName+`</div>
							<div class="text-right mx-4 lg:mx-1 xl:mx-4 text-gray-400 whitespace-nowrap overflow-ellipsis overflow-hidden">`+dateDisplay+`</div>
						</div>`;
				}
				$('#card_latestUsers').html(outputString);
			}
		})
	}

	// ------------------
	// End of functions |
	// ------------------


	var usersPageNumber = 1;
	var usersPerPage = 10;
	var usersTable = $('#usersTableBody');
	var totalCount = 0;

	// ! Initial table load settings
	var userSearchString = '';
	var groupFilter = 'users.groupId';
	var selectedRoles = 'users.roleId';
	var selectedSendingTypes = 'users.sendingId';

	// Display info on load
	showUsers(usersPerPage, usersPageNumber);
	showLatestUsers();

	// Start listening for clicks on any users
	usersTable.delegate('#select', 'click', function () {
		alert($(this).attr('data-id'));
	})

	// ! Slide down content functionality
	$('#groupsTitle, #rolesTitle, #sendingTypeTitle').on('click', function() {
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
	$('#userSearch').on('keyup', function() {
		userSearchString = $(this).val();

		clearTimeout(debounceTimeout);
		debounceTimeout = setTimeout(function() {
			usersPageNumber = 1;
			showUsers(usersPerPage, usersPageNumber);
		}, 200);
	})

	// ! Listen for changes on group filter
	$('#groupFilter').change(function () {
		groupFilter = $(this).find(':selected').attr('data-id');
		usersPageNumber = 1;
		showUsers(usersPerPage, usersPageNumber);
	})

	// ! Listen to changes on role checkboxes
	var roles = $('#rolesFilter').children().last().children().children();
	// alert( roles[0].attr('data-id') );
	roles.on('change', function() {
		selectedRoles = [];
		roles.each(function() {
			if ($(this).is(':checked')) {
				selectedRoles.push($(this).attr('data-id'));
			}
		})
		usersPageNumber = 1;
		showUsers(usersPerPage, usersPageNumber);
	})

	// ! Listen to changes on sending type checkboxes
	var sendingTypes = $('#sendingTypesFilter').children().last().children().children();
	// alert( roles[0].attr('data-id') );
	sendingTypes.on('change', function() {
		selectedSendingTypes = [];
		sendingTypes.each(function() {
			if ($(this).is(':checked')) {
				selectedSendingTypes.push($(this).attr('data-id'));
			}
		})
		usersPageNumber = 1;
		showUsers(usersPerPage, usersPageNumber);
	})

	// ! Next button
	$('#nextUsersButton').on('click', function () {
		usersPageNumber += 1;
		showUsers(usersPerPage, usersPageNumber);
	})

	// ! Previous button
	$('#previousUsersButton').on('click', function () {
		usersPageNumber -= 1;
		showUsers(usersPerPage, usersPageNumber);
	})

	// Modal functionality
	function toggleNewUserModal() {
		$('#newuser-modal').toggleClass('hidden');
	}
	$('#open-newuser-modal, #close-newuser-modal, #cancelBtn').on('click', function() {
		toggleNewUserModal();
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27 && !$('#newuser-modal').hasClass('hidden')) {
			toggleNewUserModal();
		}
	})

	function validatePhone(field) {
        // All numbers must start with international prefix
        // Checks:
        // First char must be +
        // Length longer than 7
        // Must not contain any letters
        // Must not contain any spaces
		
		var check = false;
		var number = field.val();
		if ( number ) {
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
		} else {
			check = true;
		}

        return check;
    }

	$('#new_phone').on('blur', function() {
		if (validatePhone( $('#new_phone')) ){
			$(this).removeClass('border-red-500');
		} else {
			$(this).addClass('border-red-500');
		}
	})

	$('form').on('submit', function (e) {
		e.preventDefault();
		// alert(validatePhone($('#new_phone')));

		// Check for empty fields
		if ( $('#new_fullName').val() != '' && $('#new_email').val() != '' && validatePhone( $('#new_phone') ) && $('new_password').val() != '' && $('#new_confpassword').val() != '' ) {
			$.ajax({
                url: './includes/sqlNewUser.php',
                type: 'POST',
                data: {
                    fullName: $('#new_fullName').val(),
					roleId: $('#new_roleId').find(':selected').attr('data-id'),
					email: $('#new_email').val(),
					groupId: $('#new_groupId').find(':selected').attr('data-id'),
					phoneNumber: $('#new_phone').val(),
					password: $('#new_password').val(),
					confPassword: $('#new_confpassword').val()
                },
                success: function (data) {
                    var response = JSON.parse(data);
					if (response.status == 'OK') {
						$('#errorResponse').addClass('hidden');
						$('#okResponse').removeClass('hidden');

						// Display new users list
						usersPageNumber = 1;
						showUsers(usersPerPage, usersPageNumber);

						// Reset form
						$('form').trigger('reset');
					} else {
						$('#errorResponse').removeClass('hidden');
						$('#errorResponse').html(response.message);
						$('#okResponse').addClass('hidden');
					}
                    console.log(response);
                }
            })
		}
	})
})