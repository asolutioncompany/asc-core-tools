<?php
/**
 * DisableComments Class
 *
 * Disables comments and related UI when the setting is enabled.
 *
 * @package asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Front;

use ASC\CoreTools\Core\Core;

/**
 * DisableComments Class
 */
class DisableComments {

	/**
	 * Initialize the DisableComments class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize disable comments hooks when enabled.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();
		if ( empty( $settings['disable_comments'] ) ) {
			return;
		}

		add_filter( 'comments_open', '__return_false', 20, 2 );
		add_filter( 'pings_open', '__return_false', 20, 2 );
		add_action( 'init', array( $this, 'remove_comment_support' ), 100 );
		add_filter( 'rest_pre_dispatch', array( $this, 'disable_comments_rest_api' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'maybe_remove_comments_menu' ), 999 );
	}

	/**
	 * Remove the Comments menu only when comments are disabled and no comments exist.
	 * If any comments exist, keep the menu so admins can verify or manage them.
	 *
	 * @return void
	 */
	public function maybe_remove_comments_menu(): void {
		$counts = wp_count_comments();

		if ( isset( $counts->total_comments ) ) {
			$total = (int) $counts->total_comments;
		} else {
			$total = (int) ( $counts->approved ?? 0 ) + (int) ( $counts->moderated ?? 0 )
				+ (int) ( $counts->spam ?? 0 ) + (int) ( $counts->trash ?? 0 );
		}

		if ( $total === 0 ) {
			remove_menu_page( 'edit-comments.php' );
		}
	}

	/**
	 * Block comment REST API endpoints when comments are disabled.
	 *
	 * @param mixed $result Response to replace the requested version with.
	 * @param \WP_REST_Server $server Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 * @return mixed Unchanged result, or WP_Error for comment routes.
	 */
	public function disable_comments_rest_api( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
		$route = $request->get_route();

		if ( $route !== null && str_contains( (string) $route, '/comments' ) ) {
			return new \WP_Error(
				'rest_comments_disabled',
				__( 'Comments are disabled.', 'asc-core-tools' ),
				array( 'status' => 403 )
			);
		}

		return $result;
	}

	/**
	 * Remove comment and trackback support from all post types (admin and front).
	 *
	 * @return void
	 */
	public function remove_comment_support(): void {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		foreach ( $post_types as $post_type ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}
