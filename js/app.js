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
})