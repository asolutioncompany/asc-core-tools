<?php
/**
 * Core Tools Main Class
 *
 * Main plugin class that handles initialization and lifecycle hooks.
 *
 * Manages settings and loads assets.
 *
 * @package: asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Core;

use ASC\CoreTools\Admin\Admin;
use ASC\CoreTools\Front\Front;

/**
 * Core Tools Main Class
 */
class Core {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.2.0';

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private static array $default_settings = array(
		'disable_xmlrpc' => 1,
		'disable_autoupdate_emails' => 1,
		'disable_autosave' => 1,
		'autosave_interval_seconds' => 300,
		'disable_revisions' => 1,
		'number_revisions' => 0,
		'disable_comments' => 1,
		'hide_login' => 0,
		'login_page_slug' => '',
		'enable_ninja_forms' => 1,
		'enable_social_sharing' => 1,
		'social_sharing_post_types' => 'post,page',
		'share_facebook' => 1,
		'share_x' => 1,
		'share_linkedin' => 1,
		'share_bluesky' => 1,
		'share_email' => 1,
		'share_copy_link' => 1,
		'self_host_fontawesome' => 1,
		'enable_local_fonts' => 0,
		'delete_old_trash' => 0,
		'delete_old_trash_days' => 30,
		'delete_old_draft' => 0,
		'delete_old_draft_days' => 30,
		'delete_old_revisions' => 0,
		'delete_old_revisions_days' => 30,
	);

	/**
	 * Plugin instance.
	 *
	 * @var Core|null
	 */
	private static ?Core $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	private function init(): void {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		new Front();
		if ( is_admin() ) {
			$this->init_admin();
		}
	}

	/**
	 * Initialize admin functionality.
	 *
	 * @return void
	 */
	private function init_admin(): void {
		new Admin();
	}

	/**
	 * Get plugin instance.
	 *
	 * @return Core
	 */
	public static function get_instance(): Core {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get the plugin URL.
	 *
	 * @return string
	 */
	public function get_plugin_url(): string {
		return plugin_dir_url( \ASC_CORE_TOOLS_PLUGIN_FILE );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function get_plugin_path(): string {
		return plugin_dir_path( \ASC_CORE_TOOLS_PLUGIN_FILE );
	}

	/**
	 * Get plugin settings.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$settings = get_option( Admin::OPTION_NAME, array() );
		$settings = wp_parse_args( $settings, self::$default_settings );

		return $settings;
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	public static function get_default_settings(): array {
		return self::$default_settings;
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'asc-core-tools',
			false,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages'
		);
	}

	/**
	 * Activation hook callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Flush rewrite rules if needed
		flush_rewrite_rules();

		// Set default options if needed
		add_option( 'asc_core_tools_version', self::VERSION );
	}

	/**
	 * Deactivation hook callback.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Flush rewrite rules if needed
		flush_rewrite_rules();

		// Clean up temporary data if needed
	}

	/**
	 * Uninstall hook callback.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		// Delete options
		delete_option( 'asc_core_tools_version' );
		delete_option( Admin::OPTION_NAME );

		// Clean up any other data
	}
}
