<?php
session_start();

if ($_SESSION['roleId'] == 4 || $_SESSION['roleId'] == 3) {
	header("location: index.php");
	exit();
}

$_SESSION['activeUrl'] = 'managegroups.php';
include_once('header.php');
?>

<!-- View group modal -->
<div id="viewgroup-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center hidden">
	<div id="modal-box" class="border border-gray-300 shadow-xl bg-gray-200 w-full mx-4 max-w-sm sm:max-w-md md:max-w-2xl overflow-hidden flex flex-col rounded p-4">
		<!-- Title/close btn -->
		<div class="flex justify-between items-center border-b pb-1 border-gray-300">
			<p class="uppercase text-gray-800 font-extrabold text-sm mx-2">Group profile</p>
			<svg id="close-group-modal" class="w-6 h-6 text-gray-400 hover:text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
		</div>

		<!-- Content -->
		<div id="groupprofile-content" class="w-full flex flex-col divide-y divide-gray-300">
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">ID</div>
				<div id="profile-groupId" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">Group Name</div>
				<input id="profile-groupName" class="flex-1 h-8 border border-gray-300" type="text">
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">Latitude</div>
				<input id="profile-latitude" class="flex-1 h-8 border border-gray-300" type="text">
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">Longitude</div>
				<input id="profile-longitude" class="flex-1 h-8 border border-gray-300" type="text">
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">SMS Left</div>
				<input id="profile-smsLeft" class="flex-1 h-8 border border-gray-300" type="text">
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">Dashboard Access</div>
				<select id="profile-dashAccess" class="flex-1 h-8 border border-gray-300">
					<option value="0">No</option>
					<option value="1">Yes</option>
				</select>
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">App Access</div>
				<select id="profile-appAccess" class="flex-1 h-8 border border-gray-300">
					<option value="0">No</option>
					<option value="1">Yes</option>
				</select>
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm"># Devices</div>
				<div id="profile-nDevices" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm"># Users</div>
				<div id="profile-nUsers" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">Created At</div>
				<div id="profile-createdAt" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
			</div>
			<div class="flex h-10 items-center">
				<div class="flex-1 text-center text-sm">Created By</div>
				<div id="profile-createdBy" class="flex-1 text-center font-semibold text-sm whitespace-nowrap truncate"></div>
			</div>
		</div>

		<!-- Buttons -->
		<div id="groupButtons" class="flex justify-end items-center mt-4 space-x-4">
			<?php
				// Only admins can access delete button
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					echo '<button id="deleteGroup" class="h-10 border-0 hover:border-0 px-4 rounded text-gray-800 hover:bg-red-500 hover:text-white transition-all focus:bg-red-500 focus:text-white">Delete</button>';

					echo '<button id="saveGroup" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-green-500 hover:bg-green-600 transition-all focus:bg-green-600 focus:text-white">Save</button>';
				}
			?>
			
			<button id="cancelBtn" class="h-10 border-0 hover:border-0 px-4 rounded text-white bg-lightblue-500 hover:bg-lightblue-600 transition-all focus:bg-lightblue-600 focus:text-white">Cancel</button>
		</div>
	</div>
</div>

<!-- Bottom right dashboard window -->
<div class="flex-auto flex-col lg:flex-row bg-gray-100 flex">
	<!-- Filters subnav -->
	<div class="hidden lg:block flex-none bg-gray-100 lg:h-full lg:w-44 xl:w-60">
		<div class="fixed h-full flex flex-col" style="width: inherit;">
			<!-- Filters div -->
			<div class="flex flex-col space-y-8 h-full pt-8 bg-gray-200 rounded-tr-3xl shadow-md">
				<input id="pageSearchBar" type="text" class="h-10 w-40 xl:w-52 outline-none focus:outline-none bg-gray-100 rounded-lg text-gray-800 font-medium flex justify-center items-center text-sm space-x-1 mx-auto px-4 border border-gray-300 transition-all" placeholder="Filter groups...">

				<!-- Php code for groups filter -->
				<?php
				if ($_SESSION['roleId'] == 1 || $_SESSION['roleId'] == 2) {
					echo '
					<!-- Single filter -->
					<div class="flex flex-col px-4">
						<!-- Title -->
						<div id="groupsTitle" class="flex items-center cursor-pointer space-x-2">
							<div id="icons">
								<svg id="icon_plus" class="w-6 h-6 text-gray-800 transform duration-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
								<svg id="icon_minus" class="w-6 h-6 text-gray-800 transform duration-200 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
							</div>
							<p class="text-xs font-semibold uppercase text-gray-600">Group</p>
						</div>
						<!-- Separator -->
						<div class="border border-gray-300 mt-1"></div>

						<!-- Filter content -->
						<div class="mt-2 hidden">
							<select id="groupFilter" class="focus:outline-none w-full h-10 bg-gray-50 border border-gray-400 px-2 text-sm">
								<option data-id="`groups`.groupId" class="font-medium text-sm bg-bluegray-50 text-bluegray-800" selected>All Groups</option>
					';

					$sql = "SELECT groupId, groupName FROM `groups` ORDER BY groupName ASC";
					$groupResults = mysqli_query($conn, $sql);
					$resultCheck = mysqli_num_rows($groupResults);
					if ($resultCheck > 0) {
						while ($row = mysqli_fetch_assoc($groupResults)) {
							echo '<option data-id="'.$row['groupId'].'" class="font-medium text-sm bg-bluegray-50 text-bluegray-800">'.$row['groupName'].'</option> ';
						}
					}

					echo "
							</select>
						</div>
						<!-- End of filter content -->
					</div>
					<!-- End of single filter -->
					";
				}
				?>

			</div>
			<!-- End of filters -->
		</div>
	</div>
	<!-- End of filters subnav -->

    <!-- Site content -->
    <div class="flex-auto grid grid-cols-1 p-4 gap-4 md:grid-cols-2 md:gap-4 lg:grid-cols-3 lg:gap-8 lg:p-8 auto-rows-min">
        <!-- Card -->
        <div class="col-span-1 md:col-span-2 lg:col-span-1 lg:order-2 bg-white shadow-lg border rounded-b h-64">
            <div class="h-12 bg-bluegray-50 flex text-gray-400 justify-center items-center uppercase text-xs font-semibold"> 
                Register New Group
            </div>
            <div class="border-t border-gray-200">
                <!-- Form wrapper -->
                <div class="max-w-xl mx-auto px-4">
                    <form id="registerGroupForm" method="post" class="flex flex-col space-y-4 relative">
                        <div id="successMessage" class="flex justify-center items-center w-full bg-green-100 h-12 border border-green-300 uppercase font-semibold text-sm text-green-800 hidden z-20 absolute -top-6">Group has been successfully registered!</div>
                        
                        <div id="errorMessage" class="flex justify-center items-center w-full bg-red-100 h-12 border border-red-300 uppercase font-semibold text-sm text-red-800 hidden z-20 absolute -top-6">Something went wrong!</div>
						
                            <!-- Row #1: Group name -->
                            <div class="flex flex-col">
                                <p class="form-field-title">Group name<span class="text-red-500">*</span></p>
                                <input type="text" id="groupName" name="groupName" placeholder="Enter group name" value="" required spellcheck="false" autocomplete="none" class="text-field-input">
                            </div>

							<div class="flex flex-row justify-between items-center px-4 sm:px-8 lg:px-0 2xl:px-8">
								<label class="flex items-center space-x-1">
									<input id="dashAccess" type="checkbox" class="h-4 w-4" checked><span class="text-sm text-gray-600 font-medium">Dashboard Access</span>
								</label>
								<label class="flex items-center space-x-1">
									<input id="appAccess" type="checkbox" class="h-4 w-4" checked><span class="text-sm text-gray-600 font-medium">App Access</span>
								</label>
							</div>

                            <!-- Row #2: Submit button -->
                            <div class="flex justify-center">
                                <button type="submit" id="submit" name="submit" title="Create" class="focus:outline-none flex items-center justify-center border border-transparent bg-lightblue-500 transition-all hover:bg-lightblue-600 text-lightblue-100 hover:border-lightblue-500 hover:text-white space-x-2 font-semibold uppercase text-sm h-10 w-40 mb-8 rounded shadow">
                                    <p>Create</p>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></i>
                                </button>
                            </div>
                    </form>
                </div>
                <!-- End of form wrapper -->
            </div>
        </div>

		<!-- Table card -->
		<div class="col-span-1 md:col-span-2 lg:col-span-2 bg-white shadow-lg rounded-b border">
			<div class="flex bg-white overflow-x-auto min-w-full">
                <table class="table-fixed min-w-full">
					<thead class="uppercase text-xs bg-bluegray-50 border-b border-gray-200 text-bluegray-900">
						<tr>
                            <th class="text-left w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Group Name</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">SMS Left</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Lat / Long</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Users</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap">Devices</th>
                            <th class="text-center w-2/12 py-4 px-4 font-medium text-gray-400 whitespace-nowrap"></th>
						</tr>
					</thead>
					<tbody id="groupsTableBody">
						<!-- This area gets filled via PHP -->

					</tbody>
				</table>
			</div>

			<div id="loadingOverlay" class="flex flex-auto w-full block justify-center items-center space-x-2 uppercase font-semibold text-bluegray-800 py-8">
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <p>Loading...</p>
            </div>

			<div class="flex flex-col items-center justify-center py-4">
                <div class="flex">
                    <button id="previousGroupsButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Previous</button>
                    <button id="nextGroupsButton" class="focus:outline-none h-14 w-24 bg-bluegray-50 text-bluegray-600 uppercase font-semibold text-sm border border-gray-200 disabled:opacity-75 disabled:text-bluegray-400 disabled:cursor-default">Next</button>
                </div>
                <p class="mt-4 text-sm font-semibold">Showing <span id="groupsRange"></span> of <span id="groupsTotal"></span></p>
            </div>
		</div>
        <!-- End of table card -->
	</div>
	<!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxGroups.js"></script>

<!-- Footer -->
<?php 
include_once('./footer.php');
?>

