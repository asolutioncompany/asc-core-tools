/*!
 * aS.c Core Tools Admin Javascript - Database
 *
 * AJAX handlers for the Database tab: delete obsolete data, delete orphaned data
 * (by table), optimize tables. Sends nonce and action; updates the status area on success/error.
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
				url: ajax_url,
				method: 'POST',
				data: { action: 'asc_core_tools_delete_obsolete_data', _ajax_nonce: ajax_nonce },
				dataType: 'json',
				success: function(response) {
					var msg = '';
					if (response.message) {
						msg = escapeHtml(response.message);
					}
					status += '<br/>' + msg + '<br/><b>Done.</b><br/>';
					$('.asc-core-tools-db-status').html(status);
					enable_db_buttons();
				},
				error: function(xhr) {
					var msg = 'Error.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						msg = escapeHtml(xhr.responseJSON.message);
					}
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
			status += '<br/>Deleting orphaned data in ' + escapeHtml(table) + ' table...';
			$('.asc-core-tools-db-status').html(status);
			setTimeout(function() { delete_orphaned_data_cb(table); }, 1500);
		}

		function delete_orphaned_data_cb(table) {
			$.ajax({
				url: ajax_url,
				method: 'POST',
				data: { action: 'asc_core_tools_delete_orphaned_data', table: table, _ajax_nonce: ajax_nonce },
				dataType: 'json',
				success: function(response) {
					var status = $('.asc-core-tools-db-status').html() || '';
					var msg = '';
					if (response.message) {
						msg = escapeHtml(response.message);
					}
					status += '<br/>' + msg;
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
					var msg = 'Error.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						msg = escapeHtml(xhr.responseJSON.message);
					}
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
			status += '<br/>Optimizing ' + escapeHtml(table) + ' table...';
			$('.asc-core-tools-db-status').html(status);
			setTimeout(function() { optimize_tables_cb(table); }, 1500);
		}

		function optimize_tables_cb(table) {
			$.ajax({
				url: ajax_url,
				method: 'POST',
				data: { action: 'asc_core_tools_optimize_tables', table: table, _ajax_nonce: ajax_nonce },
				dataType: 'json',
				success: function(response) {
					var status = $('.asc-core-tools-db-status').html() || '';
					var msg = '';
					if (response.message) {
						msg = escapeHtml(response.message);
					}
					status += '<br/>' + msg;
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
					var msg = 'Error.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						msg = escapeHtml(xhr.responseJSON.message);
					}
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
