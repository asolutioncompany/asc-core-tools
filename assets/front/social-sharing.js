/*!
 * aS.c Core Tools Front Javascript - Social Sharing
 */

(function ($) { // jQuery Encapsulation
	'use strict';

	$(document).ready(function () {
		// Localized Ajax URL
		var ajax_url = asc_core_tools_front.ajax_url;

		// Localized Ajax Nonce
		var ajax_nonce = asc_core_tools_front.ajax_nonce;

		var asc_core_tools_copy_url_clipboard = new ClipboardJS('.asc-core-tools-copy');

		asc_core_tools_copy_url_clipboard.on('success', function(e) {
			e.clearSelection();
		});

		asc_core_tools_copy_url_clipboard.on('error', function(e) {
			e.clearSelection();
		});

		function asc_core_tools_show_status_bar(status_bar) {
			status_bar.stop().css('right','50px').show().animate(
				{'right':'-=50px'}, '500', function() {
					status_bar.delay(3000).hide(0);
				}
			);
		}

		// If message is ever dynamic or translated, escape it before using .html() to avoid XSS.
		function asc_core_tools_show_success(message) {
			var status_bar = $('.asc-core-tools-share-success');
			status_bar.hide();
			status_bar.html(
				'<span class="asc-core-tools-share-alert-message">' + message + '</span>'+
				'<span class="asc-core-tools-share-alert-close"><i class="fa-solid fa-circle-xmark"></i></span>'
			);
			asc_core_tools_show_status_bar(status_bar);
		}

		$(document).on('click', '.asc-core-tools-share-success', function(e) {
			var status_bar = $('.asc-core-tools-share-success');
			status_bar.hide();

			return false;
		});

		$(document).on('click', '.asc-core-tools-copy', function(e) {
			asc_core_tools_show_success('Copied link!');

			return false;
		});

		function asc_core_tools_social_share_popup(url, window_name, win, w, h) {
			const y = win.top.outerHeight / 2 + win.top.screenY - ( h / 2);
			const x = win.top.outerWidth / 2 + win.top.screenX - ( w / 2);
			const param = 'width=' + w + ', height=' + h + ', top=' + y + ', left=' + x;

			return win.open(url, window_name, param);
		}

		$(document).on('click', '.asc-core-tools-share-icon', function(e) {
			var title = $(this).attr('title');
			var url = $(this).attr('href');

			if ('Copy Link' == title) {
				return false;
			}

			if ('Email' == title) {
				return true;
			}

			if (url) {
				asc_core_tools_social_share_popup(url, title, window, 480, 640);
			}

			return false;
		});
	});
})(jQuery);
