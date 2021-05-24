$(document).ready(function () {
    function pageControl(total, returned, perPage, pageNumber) {
		$('#msgTotal').html(total);

		var offset = 0;
		if (pageNumber == 1) {
			offset = 0
		} else {
			offset = (pageNumber - 1) * perPage;
		}

		if (returned < perPage) {
			$('#msgRange').html(eval(offset + 1) + '-' + total);
		} else {
			$('#msgRange').html(eval(offset + 1) + '-' + eval(offset + perPage));
		}

		if (total == 0) {
			$('#msgRange').html(total);
		}

		// Previous button should be disabled on page 1
		if (pageNumber <= 1) {
			$("#previousMessageButton").prop('disabled', true);
		} else {
			$("#previousMessageButton").prop('disabled', false);
		}

		// Next button should be disabled when all the rows have been returned
		if (total <= pageNumber * perPage) {
			$("#nextMessageButton").prop('disabled', true);
		} else {
			$("#nextMessageButton").prop('disabled', false);
		}
	}

    function showUsers(perPage, pageNumber) {
		$.ajax({
			url: './includes/messageHistory.php',
			type: 'POST',
			data: {
				msgPerPage: perPage,
				offset: perPage * (pageNumber - 1),
				searchString: userSearchString,
				groupId: groupFilter, 
				roles: selectedRoles,
				sendingTypes: selectedSendingTypes,
				function: 'showUsers'
			},
			success: function (data) {
				var users = JSON.parse(data);

				totalCount = users[users.length - 1]['totalRows'];
				returnedCount = users.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				var outputTable = '';
				var role = '';
				for (i = 0; i < users.length - 1; i++) {
					// Apply formatting

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

    function showMessages(perPage, pageNumber) {
        $.ajax({
            url: './includes/sqlMessageHistory.php',
            type: 'POST',
            data: {
                msgPerPage: perPage,
				offset: perPage * (pageNumber - 1),
                searchPhone: searchPhone,
            },
            success: function (data) {
                console.log(data);
                var messages = JSON.parse(data);

                totalCount = messages[messages.length - 1]['totalRows'];
				returnedCount = messages.length - 1;
				pageControl(totalCount, returnedCount, perPage, pageNumber);

				var outputTable = '';

                for (i = 0; i < messages.length - 1; i++) {
					outputTable += `
					<tr class="hover:bg-bluegray-100 border-b border-gray-200">
                        <td class="text-center py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+messages[i]['fromNumber']+`</td>
                        <td class="text-center py-2 px-4 text-sm text-lightblue-500 font-semibold whitespace-nowrap">`+messages[i]['toNumber']+`</td>
                        <td class="text-left py-2 px-4 text-xs text-gray-600">`+messages[i]['textBody']+`</td>
                        <td class="text-center py-2 px-4 text-sm text-gray-600 whitespace-nowrap">`+messages[i]['timeSent']+`</td>
                        <td class="text-center py-2 px-4 text-sm text-gray-600">`+messages[i]['messageType']+`</td>
                    </tr>
					`;
				}
				msgTable.html(outputTable);
            }
        })
    }

	var msgPageNumber = 1;
	var msgPerPage = 10;
	var msgTable = $('#msgTableBody');
	var totalCount = 0;
	var searchPhone = '';

    $('#searchSubmit').on('click', function() {
        searchPhone = $('#searchPhone').val();
        showMessages(msgPerPage, msgPageNumber);
    })

    	// ! Next button
	$('#nextMsgButton').on('click', function () {
		msgPageNumber += 1;
		showMessages(msgPerPage, msgPageNumber);
	})

	// ! Previous button
	$('#previousMsgButton').on('click', function () {
		msgPageNumber -= 1;
		showMessages(msgPerPage, msgPageNumber);
	})

})