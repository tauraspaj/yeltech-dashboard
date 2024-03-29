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
				var role = '';
				for (i = 0; i < users.length - 1; i++) {
					// Apply formatting
					switch ((users[i].role).toUpperCase()) {
						case ('Super Admin'.toUpperCase()):
							role = '<span class="bg-red-100 text-red-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Super Admin</span>';
							break;

						case ('Yeltech Admin'.toUpperCase()):
							role = '<span class="bg-lightblue-100 text-lightblue-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Yeltech Admin</span>';
							break;

						case ('Group Admin'.toUpperCase()):
							role = '<span class="bg-green-100 text-green-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Group Admin</span>';
							break;

						case ('Standard User'.toUpperCase()):
							role = '<span class="bg-gray-100 text-gray-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Standard User</span>';
							break;

						default:
							$role = '<span class="bg-gray-100 text-gray-500 rounded-full py-1 px-2 text-xs font-semibold uppercase whitespace-nowrap">Role Unknown</span>';
							break;
					}

					var phoneNumber = '-';
					if (users[i].phoneNumber != null && users[i].phoneNumber != '') {
						phoneNumber = users[i].phoneNumber;
					}

					outputTable += `
					<tr class="hover:bg-bluegray-100 border-b border-gray-200">
						<td class="text-left py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+ users[i].fullName + `<br><span class="font-normal text-bluegray-400">` + users[i].email + `</span></td>
						<td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+ users[i].groupName + `</td>
						<td class="text-center py-2 px-4 cursor-default">`+ role + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ phoneNumber + `</td>
						<td class="text-center py-2 px-4 text-sm text-gray-600">`+ users[i].sendingType + `</td>
						<td class="text-center relative px-2">
							<button id="viewUser" data-id="`+ users[i].userId + `" class="mx-auto focus:outline-none text-xs text-gray-600 uppercase bg-gray-50 border border-gray-300 rounded font-medium py-1 px-4 hover:bg-gray-200 flex justify-center items-center space-x-1">
								<p>View</p>
							</button>
						</td>
					</tr>
					`;
				}
				usersTable.html(outputTable);
			}
		})
	}

	function findUser(userId) {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				userId: userId,
				function: 'findUser'
			},
			success: function (data) {
				user = JSON.parse(data);
				showUserProfile(user);
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

	function saveUser(userId) {
		fullName = $.trim($('#profile-fullName > input').val());
		groupId = $('#profile-groupName > #profile-groupSelect').find(':selected').val();
		roleId = $('#profile-roleName > #profile-roleSelect').find(':selected').val();
		email = $.trim($('#profile-email > input').val());
		phoneNumber = $.trim($('#profile-phone > input').val());
		sendingId = $('#profile-sendingType > #profile-sendingSelect').find(':selected').val();
		
		if (fullName == '') {
			alert('Name cannot be left empty!');
		} else if (email == '') {
			alert('Email cannot be left empty!');
		} else if ( !validatePhone( $('#profile-phone > input') )) {
			alert('Phone number must start with + or be left empty!')
		} else {
			$.ajax({
				url: './includes/sqlUsersTable.php',
				type: 'POST',
				data: {
					userId: userId,
					fullName: fullName,
					groupId: groupId,
					roleId: roleId,
					email: email,
					phoneNumber: phoneNumber,
					sendingId: sendingId,
					function: 'saveUser'
				},
				success: function (data) {
					alert(data);
					usersPageNumber = 1;
					showUsers(usersPerPage, usersPageNumber);
				}
			})
		}
	}

	function deleteUser(userId) {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				userId: userId,
				function: 'deleteUser'
			},
			success: function (data) {
				alert(data);
				toggleModal('viewuser-modal');
				usersPageNumber = 1;
				showUsers(usersPerPage, usersPageNumber);
			}
		})
	}

	function resetPassword(userId) {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				userId: userId,
				function: 'resetPassword'
			},
			success: function (data) {
				alert(data);
				toggleModal('viewuser-modal');
			}
		})
	}

	function showUserProfile(user) {
		if ($('#viewuser-modal').hasClass('hidden')) {
			$('#viewuser-modal').removeClass('hidden');
		}

		getRoleId().then( function(session_roleId) {
			if(session_roleId > user.roleId) {
				$('#deleteUser').hide();
			} else {
				$('#deleteUser').show();
				if ( $('#deleteUser').length) {
					$('#deleteUser').prop('data-id', user.userId)
				}
			}

			if (user.phoneNumber == null) { user.phoneNumber = '' };

			// Only super admins can edit data
			if (session_roleId == 1) {
				$('#profile-fullName').html('<input class="flex-1 h-9 border border-gray-300" type="text" value="'+user.fullName+'">');
				$('#profile-groupName > #profile-groupSelect').val(user.groupId);
				$('#profile-roleName > #profile-roleSelect').val(user.roleId);
				$('#profile-email').html('<input class="flex-1 h-9 border border-gray-300" type="text" value="'+user.email+'">');
				$('#profile-phone').html('<input class="flex-1 h-9 border border-gray-300" type="text" value="'+user.phoneNumber+'">');
				$('#profile-sendingType > #profile-sendingSelect').val(user.sendingId);
				$('#saveUser').prop('data-id', user.userId)
			} else {
				$('#profile-fullName').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+user.fullName+'</p>');
				$('#profile-groupName').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+user.groupName+'</p>');
				$('#profile-roleName').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+user.roleName+'</p>');
				$('#profile-email').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+user.email+'</p>');
				$('#profile-phone').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+user.phoneNumber+'</p>');
				$('#profile-sendingType').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+user.sendingType+'</p>');
			}

			// Super admins and yeltech admins and reset the password
			if (session_roleId == 1 || session_roleId == 2) {
				$('#resetPassword').prop('data-id', user.userId)
			}
		})

		var createdAtDate = new Date( user.createdAt );
		createdAtDate = createdAtDate.toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
		$('#profile-createdAt').html('<p class="text-center font-semibold text-sm whitespace-nowrap">'+createdAtDate+'</p>');
	}

	$('#viewuser-buttons').delegate('#deleteUser', 'click', function() {
		var userId = $(this).prop('data-id');
		if( confirm("Are you sure you want to delete this user?") ) {
			deleteUser(userId);
		}
	})

	$('#viewuser-buttons').delegate('#saveUser', 'click', function() {
		var userId = $(this).prop('data-id');
		saveUser(userId);
	})

	// Reset password func
	$('#viewuser-buttons').delegate('#resetPassword', 'click', function () {
		var userId = $(this).prop('data-id');
		if (confirm("Are you sure you want to reset this user's password?")) {
			resetPassword(userId);
		}
	})
	
	function showLatestUsers() {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				function: 'showLatestUsers'
			},
			success: function (data) {
				var data = JSON.parse(data);
				var outputString = '';
				for (i = 0; i < data.length; i++) {
					var dateDisplay = new Date( data[i].createdAt );
					dateDisplay = dateDisplay.toLocaleString('en-GB', {day: 'numeric', month: 'short', year: 'numeric' });

					outputString += `
						<div class="grid grid-cols-3 py-1">
							<div class="col-span-2 font-medium mx-4 lg:mx-1 xl:mx-2 text-gray-800 whitespace-nowrap overflow-ellipsis overflow-hidden">`+data[i].fullName+`</div>
							<div class="col-span-1 text-right mx-4 lg:mx-1 xl:mx-2 text-gray-500 whitespace-nowrap overflow-ellipsis overflow-hidden">`+dateDisplay+`</div>
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
	usersTable.delegate('#viewUser', 'click', function () {
		var userId = $(this).attr('data-id');
		findUser(userId);
	})

	// ! Slide down content functionality
	$('#groupsTitle, #rolesTitle, #sendingTypeTitle').on('click', function() {
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
	function toggleModal(modalId) {
		$('#'+modalId).toggleClass('hidden');
	}
	$('#open-newuser-modal, #close-newuser-modal, #cancelBtn').on('click', function() {
		toggleModal('newuser-modal');
	});
	$('#close-viewuser-modal, #closeBtn').on('click', function() {
		toggleModal('viewuser-modal');
	});


	$(document).keydown(function(e) {
		if (e.keyCode == 27 && !$('#newuser-modal').hasClass('hidden')) {
			toggleModal('newuser-modal');
		}
		if (e.keyCode == 27 && !$('#viewuser-modal').hasClass('hidden')) {
			toggleModal('viewuser-modal');
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

	$('#userprofile-content').delegate('#profile-phone > input', 'blur', function() {
		if (validatePhone( $('#profile-phone > input')) ){
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
                }
            })
		}
	})
})