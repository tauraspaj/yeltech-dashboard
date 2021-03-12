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
			<div id="subPageNav" class="flex flex-row px-2 space-x-8 lg:space-x-0 lg:flex-col lg:space-y-8 h-full lg:pt-8">
				<!-- Filled via js according to product type -->
			</div>
			<!-- End of subpage nav -->
		</div>
	</div>
	<!-- End of filters subnav -->

	<!-- Site content -->
	<div id="siteContent" class="flex-auto p-4 lg:p-8">
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

