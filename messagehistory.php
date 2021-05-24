<?php
session_start();
$_SESSION['activeUrl'] = 'messagehistory.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto bg-gray-100 flex p-4 lg:p-8">
	<!-- Site content -->
	<div class="flex-auto grid grid-cols-1 gap-4 auto-rows-min">

    <div class="col-span-1 card-wrapper">
        <div class="card-header">
            <div class="card-header-icon">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
            </div>
            <div class="card-header-title">
                Message Archive
            </div>
        </div>
        <div class="flex-auto flex flex-col justify-center items-center bg-gray-50 rounded-b-xl">
            <div class="w-72 flex flex-col py-4">
                <p class="form-field-title">Phone Number<span class="text-red-500">*</span></p>
                <input id="searchPhone" required type="text" class="border border-gray-300">
                <button id="searchSubmit" type="submit" class="mt-4">Search</button>
            </div>
            <div class="w-full border-b my-4"></div>

            <div class="w-full flex bg-white overflow-x-auto min-w-full">
                <table class="table-fixed min-w-full">
                    <thead class="uppercase text-xs bg-bluegray-50 border-b text-bluegray-900">
                        <tr>
                            <th class="text-center w-1/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">From</th>
                            <th class="text-center w-1/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">To</th>
                            <th class="text-center w-6/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Message</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Timestamp</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Message Type</th>
                        </tr>
                    </thead>
                    <tbody id="msgTableBody">
                        <!-- This area gets filled via PHP -->
                        
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col items-center justify-center py-4">
                <div class="flex">
                    <button id="previousMsgButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
                    <button id="nextMsgButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
                </div>
                <p class="mt-4 text-sm font-semibold">Showing <span id="msgRange"></span> of <span id="msgTotal"></span></p>
            </div>
        </div>
    </div>

	</div>
	<!-- End of site content -->
</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxMessageHistory.js"></script>

<?php 
include_once('./footer.php');
?>