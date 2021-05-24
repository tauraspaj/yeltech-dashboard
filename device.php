<?php
session_start();
$_SESSION['activeUrl'] = 'devices.php';
include_once('header.php');
?>

<!-- Bottom right dashboard window -->
<div class="flex-auto flex-col lg:flex-row bg-gray-100 flex">
	<!-- Filters subnav -->
	<div class="block w-screen overflow-x-auto flex-none bg-gray-200 lg:h-full lg:w-44 xl:w-60 lg:rounded-tr-3xl shadow-md">
		<div class="lg:fixed h-16 lg:h-full flex flex-row lg:flex-col" style="width: inherit;">
			<!-- Subpage nav -->
			<div class="bg-gray-100 h-16 shadow-sm rounded-br-3xl item flex items-center justify-center">
				<span id="sidePanel-deviceName" class="px-2 font-semibold text-gray-700 text-sm uppercase whitespace-nowrap mx-6 lg:mx-0 truncate"></span>
			</div>
			<div id="subPageNav" class="flex flex-row px-2 space-x-8 lg:space-x-0 lg:flex-col lg:space-y-8 h-full lg:pt-8">
				<!-- Filled via js according to product type -->
			</div>
			<!-- End of subpage nav -->
		</div>
	</div>
	<!-- End of filters subnav -->

	<!-- Site content -->
	<div id="siteContent" data-groupId="<?php echo $_SESSION['groupId']?>" data-roleId="<?php echo $_SESSION['roleId']?>" class="flex-auto p-4 lg:p-6">
		<!-- Filled via js -->


	</div>
	<!-- End of site content -->

</div>
<!-- End of bottom right dashboard window -->	

<!-- Load AJAX script for this page -->
<script src="./js/ajaxSingleDevice.js"></script>

<!-- Footer -->
<?php 
include_once('./footer.php');
?>

