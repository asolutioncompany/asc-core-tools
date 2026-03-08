/*!
 * aS.c Core Tools Admin Javascript
 *
 * Handles the settings page tab switcher: toggles tab panels, updates ARIA state,
 * moves focus into the active panel, and shows/hides the Save Settings button by tab.
 */

(function ($) { // jQuery Encapsulation
	'use strict';

	$(document).ready(function () {
		// Localized Ajax URL
		var ajax_url = asc_core_tools_admin.ajax_url;

		// Localized Ajax Nonce
		var ajax_nonce = asc_core_tools_admin.ajax_nonce;

		var $tabs = $('.asc-core-tools-tabs .nav-tab');
		var tabIds = $tabs.map(function () { return $(this).attr('data-tab'); }).get();

		function activateTab($tab) {
			var targetTab = $tab.attr('data-tab');
			var targetClass = '.asc-core-tools-' + targetTab + '-tab';
			var $panel = $(targetClass);

			$tabs.removeClass('nav-tab-active').attr('aria-selected', 'false');
			$tab.addClass('nav-tab-active').attr('aria-selected', 'true');

			$('.asc-core-tools-tab-content').hide();
			$panel.show();

			var $focusTarget = $panel.find('input:visible, select:visible, textarea:visible, button:visible, [href]:visible').first();
			if ($focusTarget.length) {
				$focusTarget.focus();
			} else {
				$panel.find('h2').first().attr('tabindex', '-1').focus();
			}
		}

		$tabs.on('click', function (e) {
			e.preventDefault();
			activateTab($(this));
		});

		$('.asc-core-tools-tabs').on('keydown', function (e) {
			var $current = $(e.target);
			if (!$current.hasClass('nav-tab')) {
				return;
			}
			var idx = $tabs.index($current);
			if (idx < 0) {
				return;
			}
			var nextIdx = -1;
			if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
				e.preventDefault();
				nextIdx = idx > 0 ? idx - 1 : tabIds.length - 1;
			} else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
				e.preventDefault();
				nextIdx = idx < tabIds.length - 1 ? idx + 1 : 0;
			} else if (e.key === 'Home') {
				e.preventDefault();
				nextIdx = 0;
			} else if (e.key === 'End') {
				e.preventDefault();
				nextIdx = tabIds.length - 1;
			}
			if (nextIdx >= 0) {
				activateTab($tabs.eq(nextIdx));
				$tabs.eq(nextIdx).focus();
			}
		});
	});
})(jQuery);
