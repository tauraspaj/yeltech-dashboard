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

	function displaySearchResults(data) {
		console.log(data);
		var output = '';
		if (data.length > 0) {
			$('#searchResults').removeClass('hidden');
			$('#clearSearchBtn').removeClass('hidden');
			for (i = 0; i < data.length; i++) {
				switch (data[i].type) {
					case 'device':
						output += `
						<div id="select" data-type="device" data-id="`+data[i].id+`" class="flex justify-start items-center text-lightblue-500 text-xs sm:text-sm space-x-2 py-4 px-2 hover:text-lightblue-600 hover:bg-gray-200 cursor-pointer font-medium">
							<svg class="w-4 h-4 flex-none" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path></svg>
							<p>`+data[i].name+`</p>
						</div>
						`;
						break;

					case 'user':
						output += `
						<div id="select" data-type="user" data-id="`+data[i].id+`" class="flex justify-start items-center text-lightblue-500 text-xs sm:text-sm space-x-2 py-4 px-2 hover:text-lightblue-600 hover:bg-gray-200 cursor-pointer font-medium">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>
							<p>`+data[i].name+`</p>
						</div>
						`;
						break;

					case 'group':
						output += `
						<div id="select" data-type="group" data-id="`+data[i].id+`" class="flex justify-start items-center text-lightblue-500 text-xs sm:text-sm space-x-2 py-4 px-2 hover:text-lightblue-600 hover:bg-gray-200 cursor-pointer font-medium">
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

	$('#searchResults').delegate('#select', 'click', function() {
		var type = $(this).attr('data-type');
		var id = $(this).attr('data-id');

		if (type == 'device') {
			document.location.href = 'device.php?id='+id;
		} else if (type == 'user') {
			// Go to user profile
		} else if (type == 'group') {
			// Go to group profile
		} else {
			// 
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
})