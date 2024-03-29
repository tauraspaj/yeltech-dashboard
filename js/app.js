$(document).ready(function () {
	$('#burger_desktop').on('click', function () {
		$('#leftSidebar_desktop').toggleClass('lg:-translate-x-28 lg:-mr-28');
	})

	// Open mobile nav on click
	$('#burger_mobile').on('click', function () {
		$('#leftSidebar_mobile, #leftSidebar_mobileInner').toggleClass('-translate-x-full');
	})

	// Close mobile nav bar on click outside the nav bar
	$('#leftSidebarFade').on('click', function () {
		$('#leftSidebar_mobile, #leftSidebar_mobileInner').toggleClass('-translate-x-full');
	})

	function saveProfileChanges(userId, fullName, sendingId, email, phoneNumber, newPassword, password) {
		$.ajax({
			url: './includes/sqlHeader.php',
			type: 'POST',
			data: {
				userId: userId,
				fullName: fullName,
				sendingId: sendingId,
				email: email,
				phoneNumber: phoneNumber,
				newPassword: newPassword,
				password: password,
				function: 'saveProfileChanges'
			},
			success: function (data) {
				data = JSON.parse(data);
				if (data.status == 'OK') {
					alert('Profile updated successfully!');
					$('#editprofile-newpassword').val('');
					$('#editprofile-password').val('');
				} else {
					alert(data.message);
				}
			}
		})
	}

	function displaySearchResults(data) {
		var output = '';
		if (data.length > 0) {
			$('#searchResults').removeClass('hidden');
			$('#clearSearchBtn').removeClass('hidden');
			for (i = 0; i < data.length; i++) {
				var alias = '';
				if (data[i].aliasEmail != null && data[i].aliasEmail != '') { alias = '<span class="text-xs text-gray-300">('+data[i].aliasEmail+')</span>';} 
				switch (data[i].type) {
					case 'device':
						output += `
						<a href="./device.php?id=`+data[i].id+`" class="border-t flex justify-start items-center text-lightblue-500 text-xs sm:text-sm space-x-2 py-4 px-2 hover:text-lightblue-600 hover:bg-gray-200 cursor-pointer font-medium">
							<svg class="w-4 h-4 flex-none" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path></svg>
							<p>`+data[i].name+` `+alias+`</p>
						</a>
						`;
						break;

					case 'user':
						output += `
						<div id="select" data-type="user" data-id="`+data[i].id+`" class="border-t flex justify-start items-center text-lightblue-500 text-xs sm:text-sm space-x-2 py-4 px-2 hover:text-lightblue-600 hover:bg-gray-200 cursor-pointer font-medium">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>
							<p>`+data[i].name+`</p>
						</div>
						`;
						break;

					case 'group':
						output += `
						<div id="select" data-type="group" data-id="`+data[i].id+`" class="border-t flex justify-start items-center text-lightblue-500 text-xs sm:text-sm space-x-2 py-4 px-2 hover:text-lightblue-600 hover:bg-gray-200 cursor-pointer font-medium">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
							<p>`+data[i].name+`</p>
						</div>
						`;
						break;
				}
			}

			$('#searchResults').html(output);
		} else {
			$('#searchResults').addClass('hidden');
			$('#clearSearchBtn').addClass('hidden');
		}
	}

	var pageSearchString = '';
	var debounceTimeout = null;
	$('#searchBarText').on('keyup', function() {
		pageSearchString = $(this).val();
		if (pageSearchString != '') {
			globalSearch(pageSearchString);
		} else {
			$('#searchResults').addClass('hidden');
			$('#clearSearchBtn').addClass('hidden');
		}
	})

	$('#clearSearchBtn').on('click', function() {
		$('#searchBarText').val('');
		pageSearchString = '';
		$('#clearSearchBtn').addClass('hidden');
		$('#searchResults').addClass('hidden');
	})

	$(document).mouseup(function(e) {
		var container = $('#searchResults');
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.addClass('hidden');
		}

		var container2 = $('#devicesNotification');
		if (!container2.is(e.target) && container2.has(e.target).length === 0) {
			container2.addClass('hidden');
		}
	})

	function globalSearch(searchString) {
		$.ajax({
			url: './includes/sqlHeader.php',
			type: 'POST',
			data: {
				searchString: searchString,
				function: 'globalSearch'
			},
			success: function (data) {
				var data = JSON.parse(data)
				displaySearchResults(data);
			}
		})
	}

	$('#notificationsButton').on('click', function() {
		$('#devicesNotification').toggleClass('hidden');
	})

	$('#devicesNotification').on('mouseleave', function() {
		$(this).addClass('hidden');
		$('#notificationsButton').blur();
	})

	// Edit profile modal
	function toggleEditProfileModal() {
		$('#editprofile-modal').toggleClass('hidden');
	}
	$('#openProfile').on('click', function() {
		toggleEditProfileModal();
	})
	$('#close-editprofile-modal, #editprofile-cancel').on('click', function() {
		toggleEditProfileModal();
	});
	$(document).keydown(function(e) {
		if (e.keyCode == 27 && !$('#editprofile-modal').hasClass('hidden')) {
			toggleEditProfileModal();
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

	// Save edits
	$('#editprofile-save').on('click', function() {
		var userId = $('#editprofile-save').attr('data-id');

		if( validatePhone( $('#editprofile-phoneNumber') )) {
			saveProfileChanges(
				userId,  
				$.trim($('#editprofile-fullName').val()), 
				$('#editprofile-sendingId').find(':selected').attr('data-id'), 
				$.trim($('#editprofile-email').val()), 
				$.trim($('#editprofile-phoneNumber').val()), 
				$('#editprofile-newpassword').val(), 
				$('#editprofile-password').val() 
			);

		} else {
			alert('Incorrect phone number');
		}
	})
})