(function ($) { // jQuery Encapsulation
	'use strict';

	$(document).ready(function () {
		$('.asc-ais-tab-item').on('click', function (e) {
			e.preventDefault();

			const targetTab = $(this).attr('data-tab');
			const targetClass = '.asc-ais-tab-' + targetTab + '-wrapper';

			// Set active tab
			$('.asc-ais-tab-item').removeClass('asc-ais-tab-item-active');
			$(this).addClass('asc-ais-tab-item-active');

			// Show/hide active tab content
			$('.asc-ais-tab-excerpt-wrapper').hide();
			$('.asc-ais-tab-summary-wrapper').hide();
			$(targetClass).show();
		});
	});
})(jQuery);