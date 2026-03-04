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
		// Initialize settings page
		$this->settings_page = new SettingsPage();

		// Initialize database tools (AJAX handlers)
		new Database();

		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
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