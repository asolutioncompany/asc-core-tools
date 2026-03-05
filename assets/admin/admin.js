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

		/*
		 * Database Functions
		 *
		 * Ajax functions to optimize WordPress database tables.
		 */

		function disable_db_buttons() {
			$('.asc-core-tools-delete-obsolete-data').prop('disabled', true);
			$('.asc-core-tools-delete-orphaned-data').prop('disabled', true);
			$('.asc-core-tools-optimize-tables').prop('disabled', true);
		}

		function enable_db_buttons() {
			$('.asc-core-tools-delete-obsolete-data').prop('disabled', false);
			$('.asc-core-tools-delete-orphaned-data').prop('disabled', false);
			$('.asc-core-tools-optimize-tables').prop('disabled', false);
		}

		$(document).on('click', '.asc-core-tools-clear-db-messages', function() {
			$('.asc-core-tools-db-status').empty();
		});

		$(document).on('click', '.asc-core-tools-delete-obsolete-data', function(e) {
			disable_db_buttons();

			var status = $('.asc-core-tools-db-status').html() || '';
			status += '<br/>Deleting obsolete data...';
			$('.asc-core-tools-db-status').html(status);

			$.ajax({
				url: asc_core_tools_admin.ajax_url,
				method: 'POST',
				data: {
					action: 'asc_core_tools_delete_obsolete_data',
					_ajax_nonce: asc_core_tools_admin.ajax_nonce
				},
				dataType: 'json',
				success: function(response) {
					status += '<br/>' + (response.message || '') + '<br/><b>Done.</b><br/>';
					$('.asc-core-tools-db-status').html(status);
					enable_db_buttons();
				},
				error: function(xhr) {
					var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error.';
					status += '<br/><b>' + msg + '</b><br/>';
					$('.asc-core-tools-db-status').html(status);
					enable_db_buttons();
				}
			});

			return false;
		});

		function delete_orphaned_data(table) {
			disable_db_buttons();

			var status = $('.asc-core-tools-db-status').html();
			status += '<br/>Deleting orphaned data in ' + table + ' table...';
			$('.asc-core-tools-db-status').html(status);

			setTimeout(function() {
				delete_orphaned_data_cb(table);
			}, 1500); // allow locks to be released
		}

		function delete_orphaned_data_cb(table) {
			$.ajax({
				url: asc_core_tools_admin.ajax_url,
				method: 'POST',
				data: {
					action: 'asc_core_tools_delete_orphaned_data',
					table: table,
					_ajax_nonce: asc_core_tools_admin.ajax_nonce
				},
				dataType: 'json',
				success: function(response) {
					var status = $('.asc-core-tools-db-status').html() || '';
					status += '<br/>' + (response.message || '');
					$('.asc-core-tools-db-status').html(status);

					switch(table) {
						case 'postmeta':
							delete_orphaned_data('terms');
							break;
						case 'terms':
							delete_orphaned_data('termmeta');
							break;
						case 'termmeta':
							delete_orphaned_data('term_taxonomy');
							break;
						case 'term_taxonomy':
							delete_orphaned_data('term_relationships');
							break;
						case 'term_relationships':
							status = $('.asc-core-tools-db-status').html() + '<br/><b>Done.</b><br/>';
							$('.asc-core-tools-db-status').html(status);
							enable_db_buttons();
							break;
					}
				},
				error: function(xhr) {
					var status = $('.asc-core-tools-db-status').html() || '';
					var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error.';
					status += '<br/><b>' + msg + '</b><br/>';
					$('.asc-core-tools-db-status').html(status);
					enable_db_buttons();
				}
			});
		}

		$(document).on('click', '.asc-core-tools-delete-orphaned-data', function(e) {
			delete_orphaned_data('postmeta');

			return false;
		});

		function optimize_tables(table) {
			disable_db_buttons();

			var status = $('.asc-core-tools-db-status').html();
			status += '<br/>Optimizing ' + table + ' table...';
			$('.asc-core-tools-db-status').html(status);

			setTimeout(function() {
				optimize_tables_cb(table);
			}, 1500); // allow locks to be released
		}

		function optimize_tables_cb(table) {
			$.ajax({
				url: asc_core_tools_admin.ajax_url,
				method: 'POST',
				data: {
					action: 'asc_core_tools_optimize_tables',
					table: table,
					_ajax_nonce: asc_core_tools_admin.ajax_nonce
				},
				dataType: 'json',
				success: function(response) {
					var status = $('.asc-core-tools-db-status').html() || '';
					status += '<br/>' + (response.message || '');
					$('.asc-core-tools-db-status').html(status);

					switch(table) {
						case 'posts':
							optimize_tables('postmeta');
							break;
						case 'postmeta':
							optimize_tables('terms');
							break;
						case 'terms':
							optimize_tables('termmeta');
							break;
						case 'termmeta':
							optimize_tables('term_taxonomy');
							break;
						case 'term_taxonomy':
							optimize_tables('term_relationships');
							break;
						case 'term_relationships':
							status = $('.asc-core-tools-db-status').html() + '<br/><b>Done.</b><br/>';
							$('.asc-core-tools-db-status').html(status);
							enable_db_buttons();
							break;
					}
				},
				error: function(xhr) {
					var status = $('.asc-core-tools-db-status').html() || '';
					var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error.';
					status += '<br/><b>' + msg + '</b><br/>';
					$('.asc-core-tools-db-status').html(status);
					enable_db_buttons();
				}
			});
		}

		$(document).on('click', '.asc-core-tools-optimize-tables', function(e) {
			optimize_tables('posts');

			return false;
		});
	});
})(jQuery);