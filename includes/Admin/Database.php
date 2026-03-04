<?php
/**
 * Database Class
 *
 * Admin class for database settings.
 *
 * @package: asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

/**
 * Database Class
 */
class Database {

	/**
	 * Initialize the Database class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * AJAX delete obsolete data:
	 *
	 * wp_posts:
	 *
	 * revision (not safe)
	 * oembed_cache
	 * trash (not safe)
	 * auto-draft
	 *
	 * wp_postmeta:
	 *
	 * oembed_cache
	 * _wp_old_slug
	 * _wp_old_date
	 * _edit_last
	 * _edit_lock
	 *
	 * wp_options:
	 *
	 * _transient_
	 * _site_transient_
	 * _wp_session_
	 *
	 * @return void
	 */
	public function ajax_delete_obsolete_data(): void {
		check_ajax_referer( 'asc-core-tools-admin-ajax-nonce' );

		$response = new \stdClass();
		$response->success = 0;
		$response->message = '';

		if ( ! current_user_can( 'manage_options' ) ) {
			$response->message = __( 'Not allowed.', 'asc-core-tools' );
			wp_send_json( $response );
		}

		global $wpdb;

		$posts_table = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		$terms_table = $wpdb->prefix . 'terms';
		$options_table = $wpdb->prefix . 'options';

		$sql = "DELETE FROM $posts_table
			WHERE post_status = 'revision'
				OR post_status = 'oembed_cache'
				OR post_status = 'trash'
				OR post_status = 'auto-draft'";

		$rows_1 = $wpdb->query( $sql );

		if ( ! $rows_1 ) {
			$rows_1 = 0;
		}

		$sql = "DELETE posts_child FROM $posts_table AS posts_child
			LEFT JOIN $posts_table AS posts_parent ON posts_parent.ID = posts_child.post_parent
			LEFT JOIN $terms_table AS terms ON terms.term_id = posts_child.post_parent
			WHERE posts_child.post_parent <> 0 AND posts_parent.ID IS NULL AND terms.term_id IS NULL";

		$rows_2 = $wpdb->query( $sql );

		if ( ! $rows_2 ) {
			$rows_2 = 0;
		}

		$sql = "DELETE FROM $postmeta_table
			WHERE meta_key = 'oembed_cache'
				OR meta_key = '_wp_old_slug'
				OR meta_key = '_wp_old_date'
				OR meta_key = '_edit_last'
				OR meta_key = '_edit_lock'";

		$rows_3 = $wpdb->query( $sql );

		if ( ! $rows_3 ) {
			$rows_3 = 0;
		}

		$sql = "DELETE FROM $options_table
			WHERE option_name LIKE '_transient_%'
			OR option_name LIKE '_site_transient_%';";

		$rows_4 = $wpdb->query( $sql );

		if ( ! $rows_4 ) {
			$rows_4 = 0;
		}

		$sql = "DELETE FROM $options_table
			WHERE option_name LIKE '_wp_session_%'";

		$rows_5 = $wpdb->query( $sql );

		if ( ! $rows_5 ) {
			$rows_5 = 0;
		}

		$rows = $rows_1 + $rows_2 + $rows_3 + $rows_4 + $rows_5;

		$response->success = 1;
		$message = 'Deleted ' . $rows . ' rows of transient and obsolete data.';
		$response->message = __( $message, 'asc-core-tools' );
		wp_send_json( $response );
	}

	/**
	 * AJAX delete orphaned data
	 *
	 * @return void
	 */
	public function ajax_delete_orphaned_data(): void {
		check_ajax_referer( 'asc-core-tools-admin-ajax-nonce' );

		$response = new \stdClass();
		$response->success = 0;
		$response->message = '';

		if ( ! current_user_can( 'manage_options' ) ) {
			$response->message = __( 'Not allowed.', 'asc-core-tools' );
			wp_send_json( $response );
		}

		$allowed_tables = array(
			'postmeta',
			'terms',
			'termmeta',
			'term_taxonomy',
			'term_relationships',
			'terms_and_term_taxonomy',
		);
		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		if ( ! in_array( $table, $allowed_tables, true ) ) {
			$response->message = __( 'Invalid table.', 'asc-core-tools' );
			wp_send_json( $response );
		}

		$rows = 0;

		global $wpdb;

		$posts_table = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		$terms_table = $wpdb->prefix . 'terms';
		$termmeta_table = $wpdb->prefix . 'termmeta';
		$term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
		$term_relationships_table = $wpdb->prefix . 'term_relationships';

		if ( 'postmeta' === $table ) {
			$sql = "DELETE postmeta FROM $postmeta_table AS postmeta
				LEFT JOIN $posts_table AS posts ON posts.ID = postmeta.post_id
				WHERE posts.ID IS NULL";
			$rows = $wpdb->query( $sql );
		}

		if ( 'terms' === $table ) {
			$sql = "DELETE terms FROM $terms_table AS terms
				LEFT JOIN $term_taxonomy_table AS term_taxonomy
					ON term_taxonomy.term_id = terms.term_id
				WHERE term_taxonomy.term_id IS NULL";
			$rows = $wpdb->query( $sql );
		}

		if ( 'termmeta' === $table ) {
			$sql = "DELETE termmeta FROM $termmeta_table AS termmeta
				LEFT JOIN $terms_table AS terms
					ON terms.term_id = termmeta.term_id
				WHERE terms.term_id IS NULL";
			$rows = $wpdb->query( $sql );
		}

		if ( 'term_taxonomy' === $table ) {
			$sql = "DELETE term_taxonomy FROM $term_taxonomy_table AS term_taxonomy
				LEFT JOIN $term_relationships_table AS term_relationships
					ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
				LEFT JOIN $terms_table AS terms
					ON terms.term_id = term_taxonomy.term_id
				WHERE term_relationships.term_taxonomy_id IS NULL
					AND terms.term_id IS NULL";
			$rows = $wpdb->query( $sql );
		}

		if ( 'term_relationships' === $table ) {
			$sql = "DELETE term_relationships FROM $term_relationships_table AS term_relationships
				LEFT JOIN $posts_table AS posts
					ON posts.ID = term_relationships.object_id
				LEFT JOIN $term_taxonomy_table AS term_taxonomy
					ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				WHERE posts.ID IS NULL AND term_taxonomy.term_taxonomy_id IS NULL";
			$rows = $wpdb->query( $sql );
		}

		// Inefficient for large tables.
		if ( 'terms_and_term_taxonomy' === $table ) {
			$sql = "DELETE terms, term_taxonomy_sibling FROM $terms_table AS terms
				LEFT JOIN $term_taxonomy_table AS term_taxonomy_sibling
					ON term_taxonomy_sibling.term_id = terms.term_id
				LEFT JOIN $term_taxonomy_table AS term_taxonomy_parent
					ON term_taxonomy_parent.parent = terms.term_id
				LEFT JOIN $term_relationships_table AS term_relationships
					ON term_relationships.term_taxonomy_id = term_taxonomy_sibling.term_taxonomy_id
				WHERE term_taxonomy_parent.parent IS NULL
					AND term_relationships.term_taxonomy_id IS NULL";
			$rows = $wpdb->query( $sql );
		}

		if ( false === $rows ) {
			$rows = 0;
		}

		$response->success = 1;
		$message = 'Deleted ' . $rows . ' rows of orphaned ' . $table . ' data.';
		$response->message = __( $message, 'asc-core-tools' );
		wp_send_json( $response );
	}

	/**
	 * AJAX optimize tables.
	 *
	 * @return void
	 */
	public function ajax_optimize_tables(): void {
		check_ajax_referer( 'asc-core-tools-admin-ajax-nonce' );

		$response = new \stdClass();
		$response->success = 0;
		$response->message = '';

		if ( ! current_user_can( 'manage_options' ) ) {
			$response->message = __( 'Not allowed.', 'asc-core-tools' );
			wp_send_json( $response );
		}

		$allowed_tables = array(
			'posts', 'postmeta', 'terms', 'termmeta', 'term_taxonomy', 'term_relationships',
			'options', 'users', 'usermeta', 'comments', 'commentmeta', 'links',
		);
		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		if ( ! in_array( $table, $allowed_tables, true ) ) {
			$response->message = __( 'Invalid table.', 'asc-core-tools' );
			wp_send_json( $response );
		}

		global $wpdb;
		$full_table = $wpdb->prefix . $table;

		$wpdb->query( "OPTIMIZE TABLE `$full_table`" );

		$response->success = 1;
		$message = 'Optimized ' . $full_table . ' table.';
		$response->message = __( $message, 'asc-core-tools' );
		wp_send_json( $response );
	}

	/**
	 * Initialize database components.
	 *
	 * @return void
	 */
	private function init(): void {
		add_action( "wp_ajax_asc_core_tools_delete_obsolete_data", array( $this, "ajax_delete_obsolete_data" ) );
		add_action( "wp_ajax_asc_core_tools_delete_orphaned_data", array( $this, "ajax_delete_orphaned_data" ) );
		add_action( "wp_ajax_asc_core_tools_optimize_tables", array( $this, "ajax_optimize_tables" ) );
	}
}
