<?php
/**
 * Front Class
 *
 * Handles front-end initialization: enqueues front assets and initializes
 * features that run on the front (shortcodes, social sharing).
 *
 * @package asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Front;

use ASC\CoreTools\Core\Core;

/**
 * Front Class
 */
class Front {

	/**
	 * Initialize the Front class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize front components.
	 *
	 * @return void
	 */
	private function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
		add_shortcode( 'asc_core_tools_year', array( $this, 'shortcode_year' ) );
		new HideLogin();
		new DisableComments();
		new SocialSharing();
	}

	/**
	 * Shortcode to display the current year (e.g. for copyright footers).
	 *
	 * @return string Current 4-digit year.
	 */
	public function shortcode_year(): string {
		return (string) gmdate( 'Y' );
	}

	/**
	 * Enqueue front assets (front-end CSS and JavaScript).
	 *
	 * Conditionally loads Font Awesome, ninja-forms.css, local fonts, and
	 * social-sharing assets when the corresponding settings are enabled.
	 *
	 * @return void
	 */
	public function enqueue_front_assets(): void {
		$core = Core::get_instance();
		$plugin_url = $core->get_plugin_url();
		$plugin_path = $core->get_plugin_path();
		$settings = Core::get_settings();

		$css_file = 'assets/front/front.css';
		$js_file = 'assets/front/front.js';

		wp_enqueue_style(
			'asc_core_tools_front',
			$plugin_url . $css_file,
			array(),
			filemtime( $plugin_path . $css_file )
		);

		wp_enqueue_script(
			'asc_core_tools_front',
			$plugin_url . $js_file,
			array( 'jquery' ),
			filemtime( $plugin_path . $js_file ),
			true
		);

		wp_localize_script(
			'asc_core_tools_front',
			'asc_core_tools_front',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'asc-core-tools-front-ajax-nonce' ),
			)
		);

		if ( ! empty( $settings['self_host_fontawesome'] ) ) {
			$fa_base = 'vendor/fontawesome/css/';
			$fa_ver = Core::VERSION;
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

		if ( ! empty( $settings['enable_ninja_forms'] ) ) {
			$ninja_css = 'assets/front/ninja-forms.css';
			wp_enqueue_style(
				'asc_core_tools_ninja_forms',
				$plugin_url . $ninja_css,
				array(),
				filemtime( $plugin_path . $ninja_css )
			);
		}

		if ( ! empty( $settings['enable_local_fonts'] ) ) {
			$fonts_css_url = content_url( 'fonts/fonts.css' );
			$fonts_css_path = WP_CONTENT_DIR . '/fonts/fonts.css';
			$version = is_file( $fonts_css_path ) ? (string) filemtime( $fonts_css_path ) : '1';
			wp_enqueue_style(
				'asc_core_tools_local_fonts',
				$fonts_css_url,
				array(),
				$version
			);
		}

		if ( ! empty( $settings['enable_social_sharing'] ) ) {
			$social_css = 'assets/front/social-sharing.css';
			$social_js = 'assets/front/social-sharing.js';
			$clipboard_js = 'vendor/clipboard/clipboard.js';

			wp_enqueue_style(
				'asc_core_tools_social_sharing',
				$plugin_url . $social_css,
				array(),
				filemtime( $plugin_path . $social_css )
			);

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
}
