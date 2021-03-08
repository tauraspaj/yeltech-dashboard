$(document).ready(function () {
	$('#loadingOverlay').hide();
	function pageControl(total, returned, perPage, pageNumber) {
		$('#groupsTotal').html(total);

		var offset = 0;
		if (pageNumber == 1) {
			offset = 0
		} else {
			offset = (pageNumber - 1) * perPage;
		}

		if (returned < perPage) {
			$('#groupsRange').html(eval(offset + 1) + '-' + total);
		} else {
			$('#groupsRange').html(eval(offset + 1) + '-' + eval(offset + perPage));
		}

		if (total == 0) {
			$('#groupsRange').html(total);
		}

		// Previous button should be disabled on page 1
		if (pageNumber <= 1) {
			$("#previousGroupsButton").prop('disabled', true);
		} else {
			$("#previousGroupsButton").prop('disabled', false);
		}

		// Next button should be disabled when all the rows have been returned
		if (total <= pageNumber * perPage) {
			$("#nextGroupsButton").prop('disabled', true);
		} else {
			$("#nextGroupsButton").prop('disabled', false);
		}
	}


	function showGroups(perPage, pageNumber) {
		$.ajax({
			url: './includes/sqlGroups.php',
			type: 'POST',
			data: {
				groupsPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				groupId: groupFilter,
				pageSearchString: pageSearchString,
				function: 'showGroups'
			},
			beforeSend: function () {
				$('#loadingOverlay').show();
			},
			success: function (data) {
				$('#loadingOverlay').hide();
				console.log(data);
				var groups = JSON.parse(data);
				console.log(groups);

				totalCount = groups[groups.length - 1]['totalRows'];
				returnedCount = groups.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				var outputTable = '';
				for (i = 0; i < groups.length - 1; i++) {

					if (groups[i].latitude == null) {
						groups[i].latitude = '-';
					}

					if (groups[i].longitude == null) {
						groups[i].longitude = '-';
					}

                    outputTable += `
                    <tr class="hover:bg-bluegray-100 h-12 border-b border-gray-200">
                        <td class="text-left py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+ groups[i].groupName + `</td>
                        <td class="text-center py-2 px-4 text-sm text-gray-600">`+ groups[i].latitude + `</td>
                        <td class="text-center py-2 px-4 text-sm text-gray-600">`+ groups[i].longitude + `</td>
                        <td class="text-center py-2 px-4 text-sm text-gray-600">`+ groups[i].totalUsersCount +`</td>
                        <td class="text-center py-2 px-4 text-sm text-gray-600">`+ groups[i].totalDevicesCount +`</td>
						<td class="text-center" id="select" data-id="`+ groups[i].groupId + `">
							<button class="focus:outline-none text-xs text-gray-600 uppercase bg-gray-50 border border-gray-300 rounded font-medium p-1 hover:bg-gray-200">
								Actions
							</button>
						</td>
                    </tr>
                    `;
				}
				groupsTable.html(outputTable);
			}
		})
	}

	// ------------------
	// End of functions |
	// ------------------


	var groupsPageNumber = 1;
	var groupsPerPage = 10;
	var groupsTable = $('#groupsTableBody');
	var totalCount = 0;

	// ! Initial table load settings
	var pageSearchString = '';
	var groupFilter = '`groups`.groupId';

	// Show groups on load
	showGroups(groupsPerPage, groupsPageNumber);

	// ! Start listening for clicks on any groups
	groupsTable.delegate('#select', 'click', function () {
		alert($(this).attr('data-id'));
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
			groupsPageNumber = 1;
			showGroups(groupsPerPage, groupsPageNumber);
		}, 200);
	})

	// ! Listen for changes on group filter
	$('#groupFilter').change(function () {
		groupFilter = $(this).find(':selected').attr('data-id');
		groupsPageNumber = 1;
		showGroups(groupsPerPage, groupsPageNumber);
	})

	// ! Next button
	$('#nextGroupsButton').on('click', function () {
		groupsPageNumber += 1;
		showGroups(groupsPerPage, groupsPageNumber);
	})

	// ! Previous button
	$('#previousGroupsButton').on('click', function () {
		groupsPageNumber -= 1;
		showGroups(groupsPerPage, groupsPageNumber);
	})


	// -------------------
	// Group registration |
	// -------------------
	var groupNameCheck = false;

	$('#groupName').on('blur', function() {
		var field = $(this);
		var input = $(this).val();
		field.removeClass('border-red-500');
		field.removeClass('border-green-500');
		
		if (input.length != 0) {
			$.post('./includes/sqlGroups.php', {groupName: input, function: 'checkGroupName'}, function(data) {
				// This will only return 0 if there are no group name matches
				if (data != 0) {
					// This will pass if group name already exists
					field.addClass('border-red-500');
					groupNameCheck = false;
				} else {
					// This will pass if group name does not exist
					field.addClass('border-green-500');
					groupNameCheck = true;
				}
			})
		} else {
			field.addClass('border-red-500');
			groupNameCheck = false;
		}
	})

	$('form').on('submit', function(e) {
		e.preventDefault();

		var groupName = $("#groupName").val();

		$.post('./includes/sqlGroups.php', {
			groupName: groupName,
			function: 'register'
		}, function(data) {
			if (data == 'Success') {
				// Display success message for 3 seconds
				$('#successMessage').removeClass('hidden');
				setTimeout(function() {
					$('#successMessage').addClass('hidden');
				}, 3000)
				showGroups(groupsPerPage, groupsPageNumber);

				// Remove green border from input field
				$('#groupName').removeClass('border-green-500')

				// Reset input fields
				$('form').trigger("reset");

			} else if(data == 'Error') {
				// If something fails, display error message
				// Display error message for 3 seconds
				$('#errorMessage').removeClass('hidden');
				setTimeout(function() {
					$('#errorMessage').addClass('hidden');
				}, 3000)
			}
		});

	})
})