<?php
session_start();
$_SESSION['activeUrl'] = 'index.php';
include_once('header.php');
?>
<!-- Bottom right dashboard window -->
<div class="flex-auto bg-lightblue-50">
	
	<!-- Page info section -->
	<div class="bg-white border-b border-gray-200">
		<!-- Info icon grid -->
		<div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-16 p-8">
			<!-- Info #1 -->
			<div class="h-20">
				<h1 class="text-3xl font-semibold text-gray-700">Home</h1>
				<h3 class="text-sm text-lightblue-400 uppercase">Group: National Rail</h3>
			</div>

			<!-- Info #2 -->
			<div class="flex h-20 bg-yellow-50">
				<div class="h-full w-16 md:w-20 bg-yellow-400 flex justify-center items-center text-gray-100 flex-none">
					<svg class="w-8 h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
				</div>
				<div class="flex flex-col flex-auto mt-2 ml-2">
					<h4 class="text-sm uppercase text-gray-700">Group</h4>
					<h2 class="text-md font-semibold capitalize"><?php echo $_SESSION['groupName']; ?></h2>
				</div>
			</div>

			<!-- Info #3 -->
			<div class="flex h-20 bg-green-50">
				<div class="h-full w-16 md:w-20 bg-green-400 flex justify-center items-center text-gray-100 flex-none">
					<svg class="w-8 h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
				</div>
				<div class="flex flex-col flex-auto mt-2 ml-2">
					<h4 class="text-sm uppercase text-gray-700">Devices</h4>
					<h2 class="text-md font-semibold">345</h2>
				</div>
			</div>



			<!-- Info #4 -->
			<div class="flex h-20 bg-lightblue-50">
				<div class="h-full w-16 md:w-20 bg-lightblue-400 flex justify-center items-center text-gray-100 flex-none">
					<svg class="w-8 h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
				</div>
				<div class="flex flex-col flex-auto mt-2 ml-2">
					<h4 class="text-sm uppercase text-gray-700">Other Info</h4>
					<h2 class="text-md font-semibold">123</h2>
				</div>
			</div>
		</div>
		<!-- End of info icon grid -->
		<!-- Sub page navigation -->
		<div class="overflow-x-auto scrollbars-hidden mt-4">
			<div class="text-sm text-gray-500 font-semibold uppercase px-8 inline-flex space-x-12">
				<a href="#" class="py-4 whitespace-nowrap border-b-2 border-lightblue-500 text-gray-700">Overview</a>
				<a href="#" class="py-4 whitespace-nowrap border-b-2 border-transparent hover:border-lightblue-500 hover:text-gray-700">Map</a>
				<a href="#" class="py-4 whitespace-nowrap border-b-2 border-transparent hover:border-lightblue-500 hover:text-gray-700">Unacknowledged Alarms</a>
				<a href="#" class="py-4 whitespace-nowrap border-b-2 border-transparent hover:border-lightblue-500 hover:text-gray-700">One Tab</a>
				<a href="#" class="py-4 whitespace-nowrap border-b-2 border-transparent hover:border-lightblue-500 hover:text-gray-700">Another Tab</a>
			</div>
		</div>
		<!-- End of sub page navigation -->
	</div>
	<!-- End of page info section -->
	
</div>
<!-- End of bottom right dashboard window -->

<?php 
include_once('./footer.php');
?>