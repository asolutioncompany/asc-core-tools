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
use ASC\CoreTools\Admin\Features;
use ASC\CoreTools\Admin\General;

/**
 * Core Tools Main Class
 */
class Core {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

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
		'self_host_fontawesome' => 1,
	);

	/**
	 * Plugin instance.
	 *
	 * @var Core|null
	 */
	private static ?Core $instance = null;

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
		// Load text domain for translations
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Apply general settings (XML-RPC, autosave/heartbeat, revisions) from settings page
		new General();

		// Features: shortcodes and social sharing (only registers when enabled)
		new Features();

		// Enqueue public (front-end) assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

		// Initialize admin functionality
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
		// Enqueue admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Initialize Admin class
		new Admin();
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
	 * Get the plugin URL.
	 *
	 * @return string
	 */
	private function get_plugin_url(): string {
		return plugin_dir_url( \ASC_CORE_TOOLS_PLUGIN_FILE );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	private function get_plugin_path(): string {
		return plugin_dir_path( \ASC_CORE_TOOLS_PLUGIN_FILE );
	}

	/**
	 * Enqueue admin assets (CSS and JavaScript).
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$plugin_url = $this->get_plugin_url();
		$plugin_path = $this->get_plugin_path();

		$css_file = 'assets/admin/admin.css';
		$js_file = 'assets/admin/admin.js';

		// Enqueue admin CSS
		wp_enqueue_style(
			'asc_core_tools_admin',
			$plugin_url . $css_file,
			array(),
			filemtime( $plugin_path . $css_file )
		);

		// Enqueue admin JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_core_tools_admin',
			$plugin_url . $js_file,
			array( 'jquery' ),
			filemtime( $plugin_path . $js_file ),
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'asc_core_tools_admin',
			'asc_core_tools_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce('asc-core-tools-admin-ajax-nonce')
			)
		);
	}

	/**
	 * Enqueue public assets (front-end CSS and JavaScript).
	 *
	 * Conditionally loads ninja-forms.css when Ninja Forms customization is enabled,
	 * and social-sharing.css / social-sharing.js (with clipboard.js) when social sharing is enabled.
	 *
	 * @return void
	 */
	public function enqueue_public_assets(): void {
		$plugin_url = $this->get_plugin_url();
		$plugin_path = $this->get_plugin_path();
		$settings = self::get_settings();

		$css_file = 'assets/public/public.css';
		$js_file = 'assets/public/public.js';

		// Enqueue base public CSS
		wp_enqueue_style(
			'asc_core_tools_public',
			$plugin_url . $css_file,
			array(),
			filemtime( $plugin_path . $css_file )
		);

		// Enqueue base public JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_core_tools_public',
			$plugin_url . $js_file,
			array( 'jquery' ),
			filemtime( $plugin_path . $js_file ),
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'asc_core_tools_public',
			'asc_core_tools_public',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'asc-core-tools-public-ajax-nonce' ),
			)
		);

		// Self-host Font Awesome: load fontawesome.css, all.css, solid.css, regular.css, brands.css from vendor when enabled
		if ( ! empty( $settings['self_host_fontawesome'] ) ) {
			$fa_base = 'vendor/fontawesome/css/';
			$fa_ver = self::VERSION;
			$prev_handle = '';
			$fa_files = array( 'fontawesome.css', 'all.css', 'solid.css', 'regular.css', 'brands.css' );
			foreach ( $fa_files as $file ) {
				$path = $plugin_path . $fa_base . $file;
				$ver = file_exists( $path ) ? (string) filemtime( $path ) : $fa_ver;
				$handle = 'asc_core_tools_fa_' . pathinfo( $file, PATHINFO_FILENAME );
				$deps = $prev_handle ? array( $prev_handle ) : array();
				wp_enqueue_style(
					$handle,
					$plugin_url . $fa_base . $file,
					$deps,
					$ver
				);
				$prev_handle = $handle;
			}
		}

		// Ninja Forms customization: load public ninja-forms.css when enabled
		if ( ! empty( $settings['enable_ninja_forms'] ) ) {
			$ninja_css = 'assets/public/ninja-forms.css';
			wp_enqueue_style(
				'asc_core_tools_ninja_forms',
				$plugin_url . $ninja_css,
				array(),
				filemtime( $plugin_path . $ninja_css )
			);
		}

		// Social sharing: load CSS, clipboard.js, and social-sharing.js when enabled
		if ( ! empty( $settings['enable_social_sharing'] ) ) {
			$social_css = 'assets/public/social-sharing.css';
			$social_js = 'assets/public/social-sharing.js';
			$clipboard_js = 'vendor/clipboard/clipboard.js';

			wp_enqueue_style(
				'asc_core_tools_social_sharing',
				$plugin_url . $social_css,
				array(),
				filemtime( $plugin_path . $social_css )
			);

			// Register clipboard.js so social-sharing.js can depend on it
			wp_register_script(
				'asc_core_tools_clipboard',
				$plugin_url . $clipboard_js,
				array(),
				filemtime( $plugin_path . $clipboard_js ),
				true
			);

			wp_enqueue_script(
				'asc_core_tools_social_sharing',
				$plugin_url . $social_js,
				array( 'jquery', 'asc_core_tools_clipboard' ),
				filemtime( $plugin_path . $social_js ),
				true
			);
		}
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
		delete_option( 'asc_core_tools_settings' );

		// Clean up any other data
	}
}
