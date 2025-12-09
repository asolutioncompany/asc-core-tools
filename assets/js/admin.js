(function ($) { // jQuery Encapsulation
	'use strict';

	$(document).ready(function () {
		/*
		 * Tab Switcher
		 *
		 * Show/hide tabs for each tab.
		 */
		$('.asc-ct-tabs .nav-tab').on('click', function (e) {
			e.preventDefault();

			const targetTab = $(this).attr('data-tab');
			const targetClass = '.asc-ct-' + targetTab + '-tab';

			// Set active tab
			$('.asc-ct-tabs .nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');

			// Show/hide active tab content
			$('.asc-ct-tab-content').hide();
			$(targetClass).show();
		});

		/*
		 * Style Panel Switcher
		 *
		 * Show/hide style panels for each style.
		 */
		$('#asc-ct-style').on('change', function() {
			const targetStyle = $(this).val();
			const targetClass = '.asc-ct-tr-' + targetStyle;

			// Show/hide active style content
			$('.asc-ct-tr-style-row').hide();
			$(targetClass).show();
		});
	}
})(jQuery);