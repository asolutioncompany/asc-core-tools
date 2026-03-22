<?php
/**
 * HideLogin Class
 *
 * Serves login at a custom slug URL; blocks direct access to wp-login.php.
 *
 * @package asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Front;

use ASC\CoreTools\Core\Core;

/**
 * HideLogin Class
 */
class HideLogin {

	/**
	 * Query var for the custom login rewrite rule.
	 *
	 * @var string
	 */
	const LOGIN_QUERY_VAR = 'asc_core_tools_login';

	/**
	 * Initialize the HideLogin class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize hide login hooks when enabled.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();
		if ( empty( $settings['hide_login'] ) || empty( $settings['login_page_slug'] ) ) {
			return;
		}

		add_filter( 'query_vars', array( $this, 'add_login_query_var' ) );
		add_action( 'init', array( $this, 'add_login_rewrite_rule' ) );
		add_action( 'init', array( $this, 'redirect_backdoor_login_param_to_home' ), 1 );
		add_action( 'template_redirect', array( $this, 'load_wp_login_at_custom_slug' ), 0 );
		add_action( 'login_init', array( $this, 'gate_direct_wp_login_file' ), 0 );
		add_filter( 'site_url', array( $this, 'filter_login_url_to_slug' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'filter_login_url_to_slug_network' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'filter_redirect_login_to_slug' ), 10, 2 );
		add_filter( 'login_url', array( $this, 'login_url_to_slug' ), 10, 3 );
		add_filter( 'logout_url', array( $this, 'logout_url_to_custom_slug' ), 10, 2 );
		add_filter( 'logout_redirect', array( $this, 'logout_redirect_to_home' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'restrict_wp_admin' ) );
		add_filter( 'rest_authentication_errors', array( $this, 'restrict_rest_api' ) );
	}

	/**
	 * Register query var for the custom login rewrite rule.
	 *
	 * @param array<string> $vars Public query vars.
	 * @return array<string> Modified query vars.
	 */
	public function add_login_query_var( array $vars ): array {
		$vars[] = self::LOGIN_QUERY_VAR;

		return $vars;
	}

	/**
	 * Add rewrite rule so the login page slug points to our query var.
	 *
	 * @return void
	 */
	public function add_login_rewrite_rule(): void {
		$settings = Core::get_settings();
		$slug = $settings['login_page_slug'] ?? '';
		if ( $slug === '' ) {
			return;
		}

		add_rewrite_rule(
			'^' . preg_quote( $slug, '#' ) . '/?$',
			'index.php?' . self::LOGIN_QUERY_VAR . '=1',
			'top'
		);
	}

	/**
	 * Return the custom login URL (slug) for the site when Hide Login is enabled.
	 *
	 * @return string URL to the custom login page, or empty string if not configured.
	 */
	private function get_custom_login_url(): string {
		$settings = Core::get_settings();
		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );

		if ( $slug === '' ) {
			return '';
		}

		if ( get_option( 'permalink_structure' ) ) {
			return home_url( '/' . $slug . '/' );
		}

		return home_url( '/?' . $slug );
	}

	/**
	 * Block direct access to wp-login.php when the main script is that file.
	 *
	 * Login at the custom slug loads wp-login.php via require; SCRIPT_FILENAME
	 * stays index.php, so those requests are not blocked here.
	 *
	 * @return void
	 */
	public function gate_direct_wp_login_file(): void {
		$settings = Core::get_settings();
		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );

		if ( $slug === '' ) {
			return;
		}

		if ( empty( $_SERVER['SCRIPT_FILENAME'] ) || ! is_string( $_SERVER['SCRIPT_FILENAME'] ) ) {
			return;
		}

		$script_real = realpath( $_SERVER['SCRIPT_FILENAME'] );
		$login_real = realpath( ABSPATH . 'wp-login.php' );

		if ( false === $script_real || false === $login_real || $script_real !== $login_real ) {
			return;
		}

		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	/**
	 * Redirect unauthenticated users who hit the backdoor query var to the home page.
	 *
	 * @return void
	 */
	public function redirect_backdoor_login_param_to_home(): void {
		$settings = Core::get_settings();

		if ( empty( $settings['hide_login'] ) || trim( (string) ( $settings['login_page_slug'] ?? '' ) ) === '' ) {
			return;
		}

		if ( is_user_logged_in() || ! isset( $_GET[ self::LOGIN_QUERY_VAR ] ) ) {
			return;
		}

		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	/**
	 * Run wp-login.php in place so the browser URL stays the custom slug (with query args).
	 *
	 * @return void
	 */
	public function load_wp_login_at_custom_slug(): void {
		if ( ! get_query_var( self::LOGIN_QUERY_VAR ) ) {
			return;
		}

		$settings = Core::get_settings();

		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );
		if ( $slug === '' ) {
			return;
		}

		global $pagenow;

		$pagenow = 'wp-login.php';

		status_header( 200 );
		nocache_headers();

		require ABSPATH . 'wp-login.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		exit;
	}

	/**
	 * Replace wp-login.php in site_url with the custom login slug.
	 *
	 * @param string $url Full URL.
	 * @param string $path Path (e.g. wp-login.php).
	 * @param string|null $scheme Scheme (WordPress may pass null).
	 * @param int|null $blog_id Blog ID (site_url only).
	 * @return string
	 */
	public function filter_login_url_to_slug( string $url, string $path, ?string $scheme, $blog_id = null ): string {
		return $this->replace_wp_login_php_with_slug( $url, $scheme );
	}

	/**
	 * Replace wp-login.php in network_site_url with the custom login slug.
	 *
	 * @param string $url Full URL.
	 * @param string $path Path.
	 * @param string|null $scheme Scheme (WordPress may pass null).
	 * @return string
	 */
	public function filter_login_url_to_slug_network( string $url, string $path, ?string $scheme ): string {
		return $this->replace_wp_login_php_with_slug( $url, $scheme );
	}

	/**
	 * Replace wp-login.php in redirect location with the custom login slug.
	 *
	 * @param string $location Redirect URL.
	 * @param int $status Status code.
	 * @return string
	 */
	public function filter_redirect_login_to_slug( string $location, int $status ): string {
		return $this->replace_wp_login_php_with_slug( $location );
	}

	/**
	 * If URL contains wp-login.php, replace with the custom slug URL and preserve query args.
	 *
	 * @param string $url URL to filter.
	 * @param string|null $scheme Optional scheme (kept for filter signature compatibility).
	 * @return string
	 */
	private function replace_wp_login_php_with_slug( string $url, ?string $scheme = null ): string {
		if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
			return $url;
		}

		if ( strpos( $url, 'wp-login.php' ) === false ) {
			return $url;
		}

		$settings = Core::get_settings();

		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );
		if ( $slug === '' ) {
			return $url;
		}

		$custom = $this->get_custom_login_url();

		$parsed = parse_url( $url );
		if ( ! is_array( $parsed ) ) {
			return $url;
		}

		if ( isset( $parsed['query'] ) ) {
			parse_str( $parsed['query'], $args );

			if ( isset( $args['login'] ) && ! is_array( $args['login'] ) ) {
				$args['login'] = rawurlencode( $args['login'] );
			}

			$custom = add_query_arg( $args, $custom );
		}

		return $custom;
	}

	/**
	 * Filter login_url to return the custom slug URL; wp-admin redirect target returns home URL.
	 *
	 * @param string $url Default login URL.
	 * @param string $redirect Redirect path.
	 * @param bool $force_reauth Whether to force reauth.
	 * @return string
	 */
	public function login_url_to_slug( string $url, string $redirect, bool $force_reauth ): string {
		$custom = $this->get_custom_login_url();

		if ( $custom === '' ) {
			return $url;
		}

		if ( $redirect !== '' && strpos( $redirect, 'wp-admin' ) !== false ) {
			return home_url( '/' );
		}

		if ( $redirect !== '' ) {
			$custom = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom );
		}

		if ( $force_reauth ) {
			$custom = add_query_arg( 'reauth', '1', $custom );
		}

		return $custom;
	}

	/**
	 * Filter logout_url to use the custom slug with logout action and redirect_to=home.
	 *
	 * @param string $logout_url Default logout URL.
	 * @param string $redirect Redirect path after logout.
	 * @return string
	 */
	public function logout_url_to_custom_slug( string $logout_url, string $redirect ): string {
		$base = $this->get_custom_login_url();

		if ( $base === '' ) {
			return $logout_url;
		}

		$url = add_query_arg(
			array(
				'action' => 'logout',
				'redirect_to' => urlencode( home_url( '/' ) ),
				'_wpnonce' => wp_create_nonce( 'log-out' ),
			),
			$base
		);

		return $url;
	}

	/**
	 * After logout, redirect to the custom login page when Hide Login is enabled.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $requested_redirect_to redirect_to from the request.
	 * @param \WP_User|null $user User that was logged out (may be null).
	 * @return string
	 */
	public function logout_redirect_to_home( string $redirect_to, string $requested_redirect_to, $user ): string {
		$settings = Core::get_settings();

		if ( empty( $settings['hide_login'] ) || trim( (string) ( $settings['login_page_slug'] ?? '' ) ) === '' ) {
			return $redirect_to;
		}

		$login_url = $this->get_custom_login_url();
		$result = home_url( '/' );

		if ( $login_url !== '' ) {
			$result = $login_url;
		}

		return $result;
	}

	/**
	 * Restrict wp-admin: redirect to home if user is not logged in.
	 *
	 * @return void
	 */
	public function restrict_wp_admin(): void {
		if ( is_user_logged_in() ) {
			return;
		}

		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	/**
	 * Restrict REST API for non-authenticated users when Hide Login is enabled.
	 *
	 * @param \WP_Error|null|bool $result Result of authentication check.
	 * @return \WP_Error|null|bool
	 */
	public function restrict_rest_api( $result ) {
		if ( is_user_logged_in() ) {
			return $result;
		}

		return new \WP_Error(
			'rest_not_logged_in',
			__( 'REST API is disabled when Hide Login is enabled. Log in via the custom login URL.', 'asc-core-tools' ),
			array( 'status' => 401 )
		);
	}
}
