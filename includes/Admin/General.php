<?php
/**
 * General Class
 *
 * Admin class for general settings.
 *
 * @package: asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

use ASC\CoreTools\Core\Core;

/**
 * General Class
 *
 * Applies general settings from the settings page (XML-RPC, autosave/heartbeat, revisions, comments, hide login).
 */
class General {

	/**
	 * Query var for the custom login rewrite rule.
	 *
	 * @var string
	 */
	const LOGIN_QUERY_VAR = 'asc_core_tools_login';

	/**
	 * Required query parameter name for gating wp-login.php when Hide Login is enabled.
	 *
	 * @var string
	 */
	const LOGIN_SLUG_PARAM = 'asc-core-tools-login';

	/**
	 * Initialize the General class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize general components: apply settings via hooks.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();

		if ( ! empty( $settings['disable_xmlrpc'] ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( ! empty( $settings['disable_autoupdate_emails'] ) ) {
			add_filter( 'auto_plugin_update_send_email', '__return_false' );
			add_filter( 'auto_theme_update_send_email', '__return_false' );
			add_filter( 'auto_core_update_send_email', '__return_false' );
		}

		if ( ! empty( $settings['disable_autosave'] ) ) {
			add_action( 'admin_init', array( $this, 'deregister_heartbeat' ), 1 );
			add_action( 'init', array( $this, 'deregister_heartbeat' ), 1 );
		} else {
			add_filter( 'heartbeat_settings', array( $this, 'update_heartbeat' ) );
		}

		add_filter( 'wp_revisions_to_keep', array( $this, 'limit_revisions' ), 10, 2 );

		if ( ! empty( $settings['disable_comments'] ) ) {
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );
			add_action( 'init', array( $this, 'remove_comment_support' ), 100 );
			add_filter( 'rest_pre_dispatch', array( $this, 'disable_comments_rest_api' ), 10, 3 );
			add_action( 'admin_menu', array( $this, 'maybe_remove_comments_menu' ), 999 );
		}

		if ( ! empty( $settings['hide_login'] ) && ! empty( $settings['login_page_slug'] ) ) {
			add_filter( 'query_vars', array( $this, 'add_login_query_var' ) );
			add_action( 'init', array( $this, 'add_login_rewrite_rule' ) );
			add_action( 'init', array( $this, 'redirect_backdoor_login_param_to_home' ), 1 );
			add_action( 'template_redirect', array( $this, 'redirect_slug_to_wp_login' ) );
			add_action( 'login_init', array( $this, 'gate_wp_login_by_slug_param' ) );
			add_filter( 'site_url', array( $this, 'filter_login_url_to_slug' ), 10, 4 );
			add_filter( 'network_site_url', array( $this, 'filter_login_url_to_slug_network' ), 10, 3 );
			add_filter( 'wp_redirect', array( $this, 'filter_redirect_login_to_slug' ), 10, 2 );
			add_filter( 'login_url', array( $this, 'login_url_to_slug' ), 10, 3 );
			add_filter( 'logout_url', array( $this, 'logout_url_to_wp_login_with_slug' ), 10, 2 );
			add_filter( 'logout_redirect', array( $this, 'logout_redirect_to_home' ), 10, 3 );
			add_action( 'admin_init', array( $this, 'restrict_wp_admin' ) );
			add_filter( 'rest_authentication_errors', array( $this, 'restrict_rest_api' ) );
		}
	}

	/**
	 * Deregister WP Heartbeat and Autosave scripts (disables autosave).
	 *
	 * @return void
	 */
	public function deregister_heartbeat(): void {
		wp_deregister_script( 'heartbeat' );
		wp_deregister_script( 'autosave' );
	}

	/**
	 * Filter heartbeat settings to set interval from plugin settings.
	 *
	 * WordPress allows interval between 15 and 120 seconds.
	 *
	 * @param array<string, int|bool> $settings Heartbeat settings.
	 * @return array<string, int|bool> Modified settings.
	 */
	public function update_heartbeat( array $settings ): array {
		$saved = Core::get_settings();
		$seconds = isset( $saved['autosave_interval_seconds'] ) ? (int) $saved['autosave_interval_seconds'] : 60;
		$settings['interval'] = max( 15, min( 120, $seconds ) );
		return $settings;
	}

	/**
	 * Filter number of revisions to keep from plugin settings.
	 *
	 * @param int     $num  Default number of revisions.
	 * @param \WP_Post $post Post object.
	 * @return int Number of revisions to keep (0 = none, -1 = unlimited).
	 */
	public function limit_revisions( int $num, \WP_Post $post ): int {
		$saved = Core::get_settings();
		if ( ! empty( $saved['disable_revisions'] ) ) {
			return 0;
		}
		return isset( $saved['number_revisions'] ) ? (int) $saved['number_revisions'] : $num;
	}

	/**
	 * Initialize general components: apply settings via hooks.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();

		if ( ! empty( $settings['disable_xmlrpc'] ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( ! empty( $settings['disable_autoupdate_emails'] ) ) {
			add_filter( 'auto_plugin_update_send_email', '__return_false' );
			add_filter( 'auto_theme_update_send_email', '__return_false' );
			add_filter( 'auto_core_update_send_email', '__return_false' );
		}

		if ( ! empty( $settings['disable_autosave'] ) ) {
			add_action( 'admin_init', array( $this, 'deregister_heartbeat' ), 1 );
			add_action( 'init', array( $this, 'deregister_heartbeat' ), 1 );
		} else {
			add_filter( 'heartbeat_settings', array( $this, 'update_heartbeat' ) );
		}

		add_filter( 'wp_revisions_to_keep', array( $this, 'limit_revisions' ), 10, 2 );

		if ( ! empty( $settings['disable_comments'] ) ) {
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );
			add_action( 'init', array( $this, 'remove_comment_support' ), 100 );
			add_filter( 'rest_pre_dispatch', array( $this, 'disable_comments_rest_api' ), 10, 3 );
			add_action( 'admin_menu', array( $this, 'maybe_remove_comments_menu' ), 999 );
		}

		if ( ! empty( $settings['hide_login'] ) && ! empty( $settings['login_page_slug'] ) ) {
			add_filter( 'query_vars', array( $this, 'add_login_query_var' ) );
			add_action( 'init', array( $this, 'add_login_rewrite_rule' ) );
			add_action( 'init', array( $this, 'redirect_backdoor_login_param_to_home' ), 1 );
			add_action( 'template_redirect', array( $this, 'redirect_slug_to_wp_login' ) );
			add_action( 'login_init', array( $this, 'gate_wp_login_by_slug_param' ) );
			add_filter( 'site_url', array( $this, 'filter_login_url_to_slug' ), 10, 4 );
			add_filter( 'network_site_url', array( $this, 'filter_login_url_to_slug_network' ), 10, 3 );
			add_filter( 'wp_redirect', array( $this, 'filter_redirect_login_to_slug' ), 10, 2 );
			add_filter( 'login_url', array( $this, 'login_url_to_slug' ), 10, 3 );
			add_filter( 'logout_url', array( $this, 'logout_url_to_wp_login_with_slug' ), 10, 2 );
			add_filter( 'logout_redirect', array( $this, 'logout_redirect_to_home' ), 10, 3 );
			add_action( 'admin_init', array( $this, 'restrict_wp_admin' ) );
			add_filter( 'rest_authentication_errors', array( $this, 'restrict_rest_api' ) );
		}
	}

	/**
	 * Register query var for the custom login URL.
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
	public function get_custom_login_url(): string {
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
	 * Gate wp-login.php: allow only when asc-core-tools-login param is present and matches the stored slug.
	 *
	 * @return void
	 */
	public function gate_wp_login_by_slug_param(): void {
		$settings = Core::get_settings();
		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );
		if ( $slug === '' ) {
			return;
		}

		$param = isset( $_REQUEST[ self::LOGIN_SLUG_PARAM ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::LOGIN_SLUG_PARAM ] ) ) : '';
		if ( $param === '' || $param !== $slug ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	/**
	 * Redirect unauthenticated users who hit index.php?asc_core_tools_login=1 (backdoor) to the home page.
	 * Runs early on init so the redirect happens before the main query or template.
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
	 * When the request is for the custom login slug, redirect to wp-login.php with the slug parameter.
	 *
	 * @return void
	 */
	public function redirect_slug_to_wp_login(): void {
		if ( ! get_query_var( self::LOGIN_QUERY_VAR ) ) {
			return;
		}

		$settings = Core::get_settings();
		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );
		if ( $slug === '' ) {
			return;
		}

		// Use home_url so our site_url filter does not replace this with the slug (which would cause a redirect loop).
		$login_url = home_url( '/wp-login.php' );
		$login_url = add_query_arg( self::LOGIN_SLUG_PARAM, $slug, $login_url );

		// Preserve query vars from the current request (e.g. action, _wpnonce, redirect_to); skip our own and sanitize keys/values.
		if ( ! empty( $_GET ) && is_array( $_GET ) ) {
			foreach ( $_GET as $key => $value ) {
				if ( $key === self::LOGIN_SLUG_PARAM || $key === self::LOGIN_QUERY_VAR || ! is_string( $key ) || ! is_string( $value ) ) {
					continue;
				}
				$key = sanitize_key( $key );
				$value = sanitize_text_field( wp_unslash( $value ) );
				if ( $key !== '' ) {
					$login_url = add_query_arg( $key, $value, $login_url );
				}
			}
		}

		wp_safe_redirect( $login_url );
		exit;
	}

	/**
	 * Replace wp-login.php in URLs with the custom login slug (so login form and links use the slug).
	 *
	 * @param string    $url     Full URL.
	 * @param string    $path    Path (e.g. wp-login.php).
	 * @param string|null $scheme Scheme (WordPress may pass null).
	 * @param int|null  $blog_id Blog ID (site_url only).
	 * @return string
	 */
	public function filter_login_url_to_slug( string $url, string $path, ?string $scheme, $blog_id = null ): string {
		return $this->replace_wp_login_php_with_slug( $url, $scheme );
	}

	/**
	 * Replace wp-login.php in network_site_url with the custom login slug.
	 *
	 * @param string    $url    Full URL.
	 * @param string    $path   Path.
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
	 * @param int    $status   Status code.
	 * @return string
	 */
	public function filter_redirect_login_to_slug( string $location, int $status ): string {
		return $this->replace_wp_login_php_with_slug( $location );
	}

	/**
	 * If URL contains wp-login.php: when on the login page add the slug param so the form posts correctly;
	 * otherwise replace with the custom slug URL so links point to the slug (which redirects to wp-login.php with param).
	 *
	 * @param string      $url    URL to filter.
	 * @param string|null $scheme Optional scheme (kept for filter signature compatibility; not used).
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

		// Already the gated wp-login.php URL (e.g. our redirect from slug to wp-login.php): do not rewrite to slug.
		$parsed = parse_url( $url, PHP_URL_QUERY );
		if ( is_string( $parsed ) && strpos( $parsed, self::LOGIN_SLUG_PARAM . '=' ) !== false ) {
			parse_str( $parsed, $args );
			if ( isset( $args[ self::LOGIN_SLUG_PARAM ] ) && (string) $args[ self::LOGIN_SLUG_PARAM ] === $slug ) {
				return $url;
			}
		}

		global $pagenow;

		// On wp-login.php: ensure the URL has the slug param so form action and links keep the gate.
		if ( isset( $pagenow ) && $pagenow === 'wp-login.php' ) {
			return add_query_arg( self::LOGIN_SLUG_PARAM, $slug, $url );
		}

		// Elsewhere: point to the slug so the user is sent to the slug, then redirected to wp-login.php with param.
		$custom = $this->get_custom_login_url();
		$parsed = parse_url( $url );
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
	 * Filter login_url so it returns the custom slug URL.
	 * When the redirect target is wp-admin, return home URL so non-logged-in admin access goes to home.
	 *
	 * @param string $url    Default login URL.
	 * @param string $redirect Redirect path.
	 * @param bool   $force_reauth Whether to force reauth.
	 * @return string
	 */
	public function login_url_to_slug( string $url, string $redirect, bool $force_reauth ): string {
		$custom = $this->get_custom_login_url();
		if ( $custom === '' ) {
			return $url;
		}
		// Non-logged-in access to wp-admin should redirect to home, not the login slug.
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
	 * Filter logout_url so it points directly to wp-login.php with the slug param and redirect_to=home.
	 *
	 * @param string $logout_url Default logout URL (may point to slug from login_url filter).
	 * @param string $redirect   Redirect path after logout.
	 * @return string
	 */
	public function logout_url_to_wp_login_with_slug( string $logout_url, string $redirect ): string {
		$settings = Core::get_settings();
		$slug = trim( (string) ( $settings['login_page_slug'] ?? '' ), '/' );
		if ( $slug === '' ) {
			return $logout_url;
		}
		$url = home_url( '/wp-login.php' );
		$url = add_query_arg(
			array(
				self::LOGIN_SLUG_PARAM => $slug,
				'action' => 'logout',
				'redirect_to' => urlencode( home_url( '/' ) ),
				'_wpnonce' => wp_create_nonce( 'log-out' ),
			),
			$url
		);
		return $url;
	}

	/**
	 * After logout, redirect to the custom login page when Hide Login is enabled.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $requested_redirect_to redirect_to from the request.
	 * @param \WP_User|null $user User that was logged out (may be null in edge cases).
	 * @return string
	 */
	public function logout_redirect_to_home( string $redirect_to, string $requested_redirect_to, $user ): string {
		$settings = Core::get_settings();
		if ( empty( $settings['hide_login'] ) || trim( (string) ( $settings['login_page_slug'] ?? '' ) ) === '' ) {
			return $redirect_to;
		}
		$login_url = $this->get_custom_login_url();
		return $login_url !== '' ? $login_url : home_url( '/' );
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
	 * Restrict REST API for non-authenticated users when hide login is enabled.
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
	 * @param mixed            $result  Response to replace the requested version with.
	 * @param \WP_REST_Server  $server  Server instance.
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
