<?php
/**
 * WordPressSettings Class
 *
 * Applies WordPress settings on all requests (including public), so features
 * such as XML-RPC are disabled when xmlrpc.php is hit without loading admin.
 *
 * @package asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Front;

use ASC\CoreTools\Core\Core;

/**
 * WordPressSettings Class
 *
 * Front-initialized WordPress behavior toggles from plugin settings.
 */
class WordPressSettings {

	/**
	 * Initialize the WordPressSettings class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Register hooks that must run outside the admin context.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();

		if ( ! empty( $settings['disable_xmlrpc'] ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
	}
}
