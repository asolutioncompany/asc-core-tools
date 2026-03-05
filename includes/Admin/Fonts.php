<?php
/**
 * Fonts Class
 *
 * AJAX handlers for scanning wp-content/fonts and generating fonts.css.
 *
 * @package asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

/**
 * Fonts Class
 */
class Fonts {

	/**
	 * Directory file list from last load/scan (for initial render).
	 *
	 * @var array<int, string>
	 */
	public static $last_directory_files = array();

	/**
	 * Subdirectory and file for local fonts (under WP_CONTENT_DIR).
	 *
	 * @var string
	 */
	const FONTS_DIR_REL = 'fonts';

	/**
	 * Generated CSS filename.
	 *
	 * @var string
	 */
	const FONTS_CSS_FILE = 'fonts.css';

	/**
	 * Allowed font file extensions.
	 *
	 * @var array<int, string>
	 */
	private const ALLOWED_EXTENSIONS = array( 'woff2', 'woff', 'ttf', 'otf', 'eot' );

	/**
	 * Initialize the Fonts class.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_asc_core_tools_scan_fonts', array( $this, 'ajax_scan_fonts' ) );
		add_action( 'wp_ajax_asc_core_tools_generate_fonts_css', array( $this, 'ajax_generate_fonts_css' ) );
		add_action( 'load-settings_page_asc-core-tools', array( $this, 'on_load_settings_page' ) );
	}

	/**
	 * Get the absolute path to the fonts directory.
	 *
	 * @return string
	 */
	private function get_fonts_dir(): string {
		return WP_CONTENT_DIR . '/' . self::FONTS_DIR_REL;
	}

	/**
	 * Get the absolute path to fonts.css.
	 *
	 * @return string
	 */
	private function get_fonts_css_path(): string {
		return $this->get_fonts_dir() . '/' . self::FONTS_CSS_FILE;
	}

	/**
	 * Get all relevant files in the fonts directory: font files plus fonts.css if present.
	 *
	 * @return array<int, string> Sorted list of filenames.
	 */
	public function get_directory_files(): array {
		$dir = $this->get_fonts_dir();
		if ( ! is_dir( $dir ) ) {
			return array();
		}
		$files = @scandir( $dir );
		if ( $files === false ) {
			return array();
		}
		$list = array();
		foreach ( $files as $file ) {
			if ( $file === '.' || $file === '..' ) {
				continue;
			}
			$path = $dir . '/' . $file;
			if ( ! is_file( $path ) ) {
				continue;
			}
			if ( $file === self::FONTS_CSS_FILE ) {
				$list[] = $file;
				continue;
			}
			$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
			if ( in_array( $ext, self::ALLOWED_EXTENSIONS, true ) ) {
				$list[] = $file;
			}
		}
		sort( $list );
		return $list;
	}

	/**
	 * Run auto-scan and optionally generate CSS when settings page loads.
	 * If "Enable local fonts" is on, generates fonts.css. Always refreshes directory file list.
	 *
	 * @return void
	 */
	public function on_load_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = \ASC\CoreTools\Core\Core::get_settings();
		$enable_local_fonts = ! empty( $settings['enable_local_fonts'] );
		if ( $enable_local_fonts ) {
			$this->do_generate_css();
		}
		self::$last_directory_files = $this->get_directory_files();
	}

	/**
	 * Get the directory file list from last load/scan (for initial page render).
	 *
	 * @return array<int, string>
	 */
	public static function get_last_directory_files(): array {
		return self::$last_directory_files;
	}

	/**
	 * Ensure a path is inside the fonts directory (no directory traversal).
	 * Handles paths to files that do not exist yet (e.g. fonts.css before first generate).
	 *
	 * @param string $path Full path to a file.
	 * @return bool
	 */
	private function path_is_in_fonts_dir( string $path ): bool {
		$fonts_dir = realpath( $this->get_fonts_dir() );
		if ( $fonts_dir === false ) {
			return false;
		}
		$resolved = realpath( $path );
		if ( $resolved !== false ) {
			return strpos( $resolved, $fonts_dir ) === 0;
		}
		// File may not exist yet: resolve parent directory and check basename has no traversal.
		$parent = realpath( dirname( $path ) );
		if ( $parent === false || $parent !== $fonts_dir ) {
			return false;
		}
		$basename = basename( $path );
		return $basename !== '' && $basename !== '.' && $basename !== '..' && strpos( $basename, '/' ) === false && strpos( $basename, '\\' ) === false;
	}

	/**
	 * AJAX: Scan wp-content/fonts for font files and fonts.css.
	 *
	 * @return void
	 */
	public function ajax_scan_fonts(): void {
		check_ajax_referer( 'asc-core-tools-admin-ajax-nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not allowed.', 'asc-core-tools' ) ) );
		}

		$dir = $this->get_fonts_dir();
		if ( ! is_dir( $dir ) ) {
			wp_send_json_success( array( 'fonts' => array(), 'message' => __( 'Directory wp-content/fonts does not exist.', 'asc-core-tools' ) ) );
		}

		$fonts = $this->get_directory_files();
		$message = count( $fonts ) > 0
			? __( 'Files in wp-content/fonts:', 'asc-core-tools' )
			: __( 'No font files or fonts.css found.', 'asc-core-tools' );
		wp_send_json_success( array( 'fonts' => $fonts, 'message' => $message ) );
	}

	/**
	 * Infer font-weight from filename.
	 *
	 * @param string $basename Filename without extension.
	 * @return string
	 */
	private function infer_font_weight( string $basename ): string {
		$lower = strtolower( $basename );
		if ( strpos( $lower, 'thin' ) !== false ) {
			return '100';
		}
		if ( strpos( $lower, 'extralight' ) !== false || strpos( $lower, 'extra-light' ) !== false ) {
			return '200';
		}
		if ( strpos( $lower, 'light' ) !== false ) {
			return '300';
		}
		if ( strpos( $lower, 'medium' ) !== false ) {
			return '500';
		}
		if ( strpos( $lower, 'semibold' ) !== false || strpos( $lower, 'semi-bold' ) !== false ) {
			return '600';
		}
		if ( strpos( $lower, 'bold' ) !== false ) {
			return '700';
		}
		if ( strpos( $lower, 'extrabold' ) !== false || strpos( $lower, 'extra-bold' ) !== false ) {
			return '800';
		}
		if ( strpos( $lower, 'black' ) !== false ) {
			return '900';
		}
		return '400';
	}

	/**
	 * Infer font-style from filename.
	 *
	 * @param string $basename Filename without extension.
	 * @return string
	 */
	private function infer_font_style( string $basename ): string {
		return ( strpos( strtolower( $basename ), 'italic' ) !== false ) ? 'italic' : 'normal';
	}

	/**
	 * Build a font-family name from filename (basename without extension).
	 *
	 * @param string $basename Filename without extension.
	 * @return string
	 */
	private function font_family_from_basename( string $basename ): string {
		$name = preg_replace( '/[-_](regular|normal|italic|bold|light|thin|medium|black)$/i', '', $basename );
		$name = trim( preg_replace( '/[-_]+/', ' ', $name ) );
		return $name !== '' ? $name : $basename;
	}

	/**
	 * Get CSS format for font extension.
	 *
	 * @param string $ext File extension.
	 * @return string
	 */
	private function format_for_extension( string $ext ): string {
		$map = array(
			'woff2' => 'woff2',
			'woff'  => 'woff',
			'ttf'   => 'truetype',
			'otf'   => 'opentype',
			'eot'   => 'embedded-opentype',
		);
		return $map[ $ext ] ?? 'woff2';
	}

	/**
	 * Generate fonts.css in wp-content/fonts. Used by AJAX and by on_load_settings_page.
	 *
	 * @return array{success: bool, message: string}
	 */
	public function do_generate_css(): array {
		$dir = $this->get_fonts_dir();
		if ( ! is_dir( $dir ) ) {
			return array(
				'success' => false,
				'message' => __( 'Directory wp-content/fonts does not exist. Create it and upload font files.', 'asc-core-tools' ),
			);
		}

		$files = @scandir( $dir );
		if ( $files === false ) {
			return array(
				'success' => false,
				'message' => __( 'Could not read directory.', 'asc-core-tools' ),
			);
		}

		$css_lines = array( '/* Generated by aS.c Core Tools - local fonts */', '' );
		$content_url = content_url( self::FONTS_DIR_REL );

		foreach ( $files as $file ) {
			if ( $file === '.' || $file === '..' || $file === self::FONTS_CSS_FILE ) {
				continue;
			}
			$path = $dir . '/' . $file;
			if ( ! is_file( $path ) ) {
				continue;
			}
			$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, self::ALLOWED_EXTENSIONS, true ) ) {
				continue;
			}
			if ( ! $this->path_is_in_fonts_dir( $path ) ) {
				continue;
			}

			$basename = pathinfo( $file, PATHINFO_FILENAME );
			$family = $this->font_family_from_basename( $basename );
			$weight = $this->infer_font_weight( $basename );
			$style = $this->infer_font_style( $basename );
			$format = $this->format_for_extension( $ext );
			$url = $content_url . '/' . rawurlencode( $file );

			$css_lines[] = '@font-face {';
			$css_lines[] = '  font-family: "' . $family . '";';
			$css_lines[] = '  font-weight: ' . $weight . ';';
			$css_lines[] = '  font-style: ' . $style . ';';
			$css_lines[] = '  src: url("' . esc_url( $url ) . '") format("' . $format . '");';
			$css_lines[] = '}';
			$css_lines[] = '';
		}

		$css = implode( "\n", $css_lines );
		$css_path = $this->get_fonts_css_path();

		if ( ! $this->path_is_in_fonts_dir( $css_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid path.', 'asc-core-tools' ),
			);
		}

		$written = @file_put_contents( $css_path, $css );
		if ( $written === false ) {
			return array(
				'success' => false,
				'message' => __( 'Could not write fonts.css. Check that wp-content/fonts is writable.', 'asc-core-tools' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'fonts.css generated successfully.', 'asc-core-tools' ),
		);
	}

	/**
	 * AJAX: Generate fonts.css in wp-content/fonts.
	 *
	 * @return void
	 */
	public function ajax_generate_fonts_css(): void {
		check_ajax_referer( 'asc-core-tools-admin-ajax-nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not allowed.', 'asc-core-tools' ) ) );
		}

		$result = $this->do_generate_css();
		if ( $result['success'] ) {
			wp_send_json_success( array(
				'message' => $result['message'],
				'fonts' => $this->get_directory_files(),
			) );
		}
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}
}
