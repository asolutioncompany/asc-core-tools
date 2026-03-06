/*!
 * aS.c Core Tools Admin Javascript
 */

(function ($) { // jQuery Encapsulation
	'use strict';

	$(document).ready(function () {
		// Localized Ajax URL
		var ajax_url = asc_core_tools_admin.ajax_url;

		// Localized Ajax Nonce
		var ajax_nonce = asc_core_tools_admin.ajax_nonce;

		/*
		 * Tab Switcher
		 *
		 * Show/hide tabs for each tab.
		 */
		$('.asc-core-tools-tabs .nav-tab').on('click', function (e) {
			e.preventDefault();

			const targetTab = $(this).attr('data-tab');
			const targetClass = '.asc-core-tools-' + targetTab + '-tab';
			const $panel = $(targetClass);

			// Set active tab and ARIA
			$('.asc-core-tools-tabs .nav-tab').removeClass('nav-tab-active').attr('aria-selected', 'false');
			$(this).addClass('nav-tab-active').attr('aria-selected', 'true');

			// Show/hide active tab content
			$('.asc-core-tools-tab-content').hide();
			$panel.show();

			// Move focus into the panel: first focusable element or the panel's h2
			var $focusTarget = $panel.find('input:visible, select:visible, textarea:visible, button:visible, [href]:visible').first();
			if ($focusTarget.length) {
				$focusTarget.focus();
			} else {
				$panel.find('h2').first().attr('tabindex', '-1').focus();
			}

			// Hide Save Settings button on Database tab, show on others
			if (targetTab === 'database') {
				$('.asc-core-tools-save-wrap').hide();
			} else {
				$('.asc-core-tools-save-wrap').show();
			}
		});
	});
})(jQuery);
