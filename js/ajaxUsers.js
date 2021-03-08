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
							<button class="focus:outline-none text-xs text-gray-600 uppercase bg-gray-50 border border-gray-300 rounded font-medium p-1 mr-2 hover:bg-gray-200">Actions</button>
						</td>
					</tr>
					`;
				}
				usersTable.html(outputTable);
			}
		})
	}

	function showRoleCount() {
		$.ajax({
			url: './includes/sqlUsersTable.php',
			type: 'POST',
			data: {
				function: 'roleCount'
			},
			success: function (data) {
				var data = JSON.parse(data);
				// Process
				var totalUsers = data.totalUsers;
				var totalGroupAdmins = data.totalGroupAdmins;
				var totalStdUsers = totalUsers - totalGroupAdmins;
				// Display totals
				$('#card_totalUsers').html(totalUsers)
				$('#card_groupAdmins').html(totalGroupAdmins)
				$('#card_standardUsers').html(totalStdUsers)
				// Display bars
				var percentage = 0;
				if (totalGroupAdmins == 0) {
					percentage = 0;
				} else {
					percentage = Math.ceil((totalUsers/100)*totalGroupAdmins);
				}
				$('#card_stdUsersBar').css('width', eval(100-percentage)+'%');
				$('#card_grpAdminsBar').css('width', percentage+'%');
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
	showRoleCount();
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

})