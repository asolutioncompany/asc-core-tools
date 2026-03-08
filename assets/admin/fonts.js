/*!
 * aS.c Core Tools Admin Javascript - Fonts
 *
 * AJAX handlers for the Features tab: Scan for fonts (lists files in wp-content/fonts)
 * and Generate CSS (builds fonts.css with @font-face rules). Sends nonce and action.
 */

(function ($) {
	'use strict';

	function escapeHtml(str) {
		if (str == null) {
			return '';
		}
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

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
					var hasFontList = false;
					if (res.success && res.data && res.data.fonts && res.data.fonts.length) {
						hasFontList = true;
					}
					if (hasFontList) {
						var heading = '';
						if (res.data && res.data.message) {
							heading = escapeHtml(res.data.message);
						}
						var safeFonts = res.data.fonts.map(function (f) { return escapeHtml(f); });
						list.html('<div><strong>' + heading + '</strong></div><ul><li>' + safeFonts.join('</li><li>') + '</li></ul>');
					} else {
						var listMsg = 'No font files found.';
						if (res.data && res.data.message) {
							listMsg = escapeHtml(res.data.message);
						}
						list.html('<p>' + listMsg + '</p>');
					}
				},
				error: function(xhr) {
					var msg = 'Request failed.';
					if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
						msg = escapeHtml(xhr.responseJSON.data.message);
					}
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
						var msg = 'Done.';
						if (res.data && res.data.message) {
							msg = escapeHtml(res.data.message);
						}
						var hasFontList = false;
						if (res.data && res.data.fonts && res.data.fonts.length) {
							hasFontList = true;
						}
						if (hasFontList) {
							var safeFonts = res.data.fonts.map(function (f) { return escapeHtml(f); });
							list.html('<p class="notice notice-success">' + msg + '</p><p><strong>Files in wp-content/fonts:</strong></p><ul><li>' + safeFonts.join('</li><li>') + '</li></ul>');
						} else {
							list.html('<p class="notice notice-success">' + msg + '</p>');
						}
					} else {
						var errMsg = 'Error.';
						if (res.data && res.data.message) {
							errMsg = escapeHtml(res.data.message);
						}
						list.html('<p class="notice notice-error">' + errMsg + '</p>');
					}
				},
				error: function(xhr) {
					var msg = 'Request failed.';
					if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
						msg = escapeHtml(xhr.responseJSON.data.message);
					}
					list.html('<p class="notice notice-error">' + msg + '</p>');
				},
				complete: function() {
					btn.prop('disabled', false);
				}
			});
		});
	});
})(jQuery);
