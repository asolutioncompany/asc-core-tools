<?php
/**
 * Core Tools Main Class
 *
 * Main plugin class that handles initialization and lifecycle hooks.
 *
 * @package: asc-core-tools
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\CoreTools;

/**
 * Main AI Summaries Plugin Class
 */
class CoreTools {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '0.1.0';

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private static array $default_settings = array(
		'disable_xmlprc' => 1,
		'disable_heartbeat_api' => 1,
		'heartbeat_interval' => 300,
		'disable_revisions' => 1,
		'number_revisions' => -1,
		'disable_comments' => 1,
	);

	/**
	 * Plugin instance.
	 *
	 * @var AISummaries|null
	 */
	private static ?CoreTools $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return AISummaries
	 */
	public static function get_instance(): CoreTools {
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
		$settings = get_option( Admin\Admin::OPTION_NAME, array() );
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

		// Initialize admin functionality
		if ( is_admin() ) {
			$this->init_admin();
		}

		// Initialize template functionality
		$this->init_templates();
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
		new Admin\Admin();
	}

	/**
	 * Initialize template functionality.
	 *
	 * @return void
	 */
	private function init_templates(): void {
		// Enqueue template assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_template_assets' ) );

		// Initialize Template class
		new Templates\Template();
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
		return plugin_dir_url( dirname( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	private function get_plugin_path(): string {
		return plugin_dir_path( dirname( __FILE__ ) );
	}

	/**
	 * Enqueue admin assets (CSS and JavaScript).
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$plugin_url = $this->get_plugin_url();
		$version    = self::VERSION;

		// Enqueue admin CSS
		wp_enqueue_style(
			'asc_ais_admin',
			$plugin_url . 'assets/css/admin.css',
			array(),
			$version
		);

		// Enqueue admin JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_ais_admin',
			$plugin_url . 'assets/js/admin.js',
			array( 'jquery' ),
			$version,
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'asc_ct_admin',
			'ascCoreTools',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Enqueue public assets (Template CSS and JavaScript).
	 *
	 * @return void
	 */
	public function enqueue_public_assets(): void {
		$plugin_url = $this->get_plugin_url();
		$plugin_path = $this->get_plugin_path();
		$version    = self::VERSION;

		// Enqueue template CSS
		wp_enqueue_style(
			'asc_ct_templates',
			$plugin_url . 'templates/front.css',
			array(),
			$version
		);

		// Enqueue front JavaScript with jQuery as dependency
		wp_enqueue_script(
			'asc_ct_templates',
			$plugin_url . 'templates/front.js',
			array( 'jquery' ),
			$version,
			true
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
		add_option( 'asc_ais_version', self::VERSION );
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
		delete_option( 'asc_ais_version' );
		delete_option( 'asc_ais_settings' );

		// Clean up any other data
	}
}
