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
			$("#previousMsgButton").prop('disabled', true);
		} else {
			$("#previousMsgButton").prop('disabled', false);
		}

		// Next button should be disabled when all the rows have been returned
		if (total <= pageNumber * perPage) {
			$("#nextMsgButton").prop('disabled', true);
		} else {
			$("#nextMsgButton").prop('disabled', false);
		}
	}


    function showMessages(perPage, pageNumber) {
        $.ajax({
            url: './includes/sqlMessageHistory.php',
            type: 'POST',
            data: {
                msgPerPage: perPage,
				offset: perPage * (pageNumber - 1),
                searchPhone: searchPhone,
				function: 'searchMessages'
            },
            success: function (data) {
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

	$('#sendMessage').on('click', function() {
		var from = $('#sendSource').find(':selected').attr('data-id');
		var to = $('#sendDestination').val();
		var textBody = $('#sendTextBody').val();

		$.ajax({
			url: './includes/sqlMessageHistory.php',
			type: 'POST',
			data: {
				from: from,
				to: to,
				textBody: textBody,
				function: 'sendMessage'
			},
			success: function (data) {
				$('#sendDestination').val("");
				$('#sendTextBody').val("");
				alert('Message has been sent!');
			}
		})

	})

})