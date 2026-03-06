/*!
 * aS.c Core Tools Admin Javascript - Fonts
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		var ajax_url = asc_core_tools_admin.ajax_url;
		var ajax_nonce = asc_core_tools_admin.ajax_nonce;

		$(document).on('click', '.asc-core-tools-scan-fonts', function() {
			var btn = $(this);
			var list = $('.asc-core-tools-font-list');
			btn.prop('disabled', true);
			list.html('');

			$.ajax({
				url: ajax_url,
				type: 'POST',
				data: {
					action: 'asc_core_tools_scan_fonts',
					_ajax_nonce: ajax_nonce
				},
				dataType: 'json',
				success: function(res) {
					if (res.success && res.data && res.data.fonts && res.data.fonts.length) {
						list.html('<p><strong>' + (res.data.message || '') + '</strong></p><ul><li>' + res.data.fonts.join('</li><li>') + '</li></ul>');
					} else {
						list.html('<p>' + (res.data && res.data.message ? res.data.message : 'No font files found.') + '</p>');
					}
				},
				error: function(xhr) {
					var msg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ? xhr.responseJSON.data.message : 'Request failed.';
					list.html('<p class="notice notice-error">' + msg + '</p>');
				},
				complete: function() {
					btn.prop('disabled', false);
				}
			});
		});

		$(document).on('click', '.asc-core-tools-generate-fonts-css', function() {
			var btn = $(this);
			var list = $('.asc-core-tools-font-list');
			btn.prop('disabled', true);

			$.ajax({
				url: ajax_url,
				type: 'POST',
				data: {
					action: 'asc_core_tools_generate_fonts_css',
					_ajax_nonce: ajax_nonce
				},
				dataType: 'json',
				success: function(res) {
					if (res.success) {
						var msg = (res.data && res.data.message) ? res.data.message : 'Done.';
						if (res.data && res.data.fonts && res.data.fonts.length) {
							list.html('<p class="notice notice-success">' + msg + '</p><p><strong>Files in wp-content/fonts:</strong></p><ul><li>' + res.data.fonts.join('</li><li>') + '</li></ul>');
						} else {
							list.html('<p class="notice notice-success">' + msg + '</p>');
						}
					} else {
						list.html('<p class="notice notice-error">' + (res.data && res.data.message ? res.data.message : 'Error.') + '</p>');
					}
				},
				error: function(xhr) {
					var msg = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ? xhr.responseJSON.data.message : 'Request failed.';
					list.html('<p class="notice notice-error">' + msg + '</p>');
				},
				complete: function() {
					btn.prop('disabled', false);
				}
			});
		});
	});
})(jQuery);
