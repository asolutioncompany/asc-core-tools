<?php
/**
 * Admin Class
 *
 * Core admin class that maintains constants and initializes admin components.
 *
 * @package: asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

use ASC\CoreTools\Core\Core;

/**
 * Admin Class
 */
class Admin {

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'asc-core-tools';

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'asc_core_tools_settings';

	/**
	 * Settings page instance.
	 *
	 * @var SettingsPage|null
	 */
	private ?SettingsPage $settings_page = null;

	/**
	 * Initialize the admin class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize admin components.
	 *
	 * @return void
	 */
	private function init(): void {
		$this->settings_page = new SettingsPage();
		new General();
		add_action( 'load-settings_page_' . self::PAGE_SLUG, array( $this, 'load_settings_page_components' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
	}

	/**
	 * Load Database and Fonts only when the plugin settings page is displayed.
	 *
	 * @return void
	 */
	public function load_settings_page_components(): void {
		new Database();
		new Fonts();
	}

	/**
	 * Enqueue admin assets (CSS and JavaScript).
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$core = Core::get_instance();
		$plugin_url = $core->get_plugin_url();
		$plugin_path = $core->get_plugin_path();
		$css_file = 'assets/admin/admin.css';
		$js_file = 'assets/admin/admin.js';

		wp_enqueue_style(
			'asc_core_tools_admin',
			$plugin_url . $css_file,
			array(),
			filemtime( $plugin_path . $css_file )
		);

		wp_enqueue_script(
			'asc_core_tools_admin',
			$plugin_url . $js_file,
			array( 'jquery' ),
			filemtime( $plugin_path . $js_file ),
			true
		);

		$db_js = 'assets/admin/database.js';
		wp_enqueue_script(
			'asc_core_tools_admin_database',
			$plugin_url . $db_js,
			array( 'asc_core_tools_admin' ),
			filemtime( $plugin_path . $db_js ),
			true
		);

		$fonts_js = 'assets/admin/fonts.js';
		wp_enqueue_script(
			'asc_core_tools_admin_fonts',
			$plugin_url . $fonts_js,
			array( 'asc_core_tools_admin' ),
			filemtime( $plugin_path . $fonts_js ),
			true
		);

		wp_localize_script(
			'asc_core_tools_admin',
			'asc_core_tools_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'asc-core-tools-admin-ajax-nonce' ),
			)
		);
	}

	/**
	 * Register the settings page under the Settings menu.
	 *
	 * @return void
	 */
	public function register_settings_page(): void {
		add_options_page(
			__( 'aS.c Core Tools', 'asc-core-tools' ),
			__( 'aS.c Core Tools', 'asc-core-tools' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this->settings_page, 'render_settings_page' )
		);
	}

	/**
	 * Get settings page instance.
	 *
	 * @return SettingsPage|null
	 */
	public function get_settings_page(): ?SettingsPage {
		return $this->settings_page;
	}
}