<?php
/**
 * WordPressSettings Class
 *
 * Admin-initialized WordPress behavior from the plugin settings page:
 * automatic update notification emails (when disabled), autosave and
 * Heartbeat API, and post revisions. XML-RPC is handled in
 * {@see \ASC\CoreTools\Front\WordPressSettings} so it applies on public
 * requests (for example {@code xmlrpc.php}).
 *
 * @package: asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

use ASC\CoreTools\Core\Core;

/**
 * WordPressSettings Class
 *
 * Applies WordPress settings from the settings page: optional suppression of
 * emails after automatic core, plugin, and theme updates; autosave and
 * heartbeat interval; revision limits.
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
	 * Initialize WordPress settings components: apply settings via hooks.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();

		// When enabled, WordPress does not send notification emails after
		// automatic core, plugin, or theme updates (see also Settings UI label).
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
		$seconds = (int) ( $saved['autosave_interval_seconds'] ?? 60 );
		$settings['interval'] = max( 15, min( 120, $seconds ) );

		return $settings;
	}

	/**
	 * Filter number of revisions to keep from plugin settings.
	 *
	 * @param int $num Default number of revisions.
	 * @param \WP_Post $post Post object.
	 * @return int Number of revisions to keep (0 = none, -1 = unlimited).
	 */
	public function limit_revisions( int $num, \WP_Post $post ): int {
		$saved = Core::get_settings();

		if ( ! empty( $saved['disable_revisions'] ) ) {
			return 0;
		}

		return (int) ( $saved['number_revisions'] ?? $num );
	}
}
