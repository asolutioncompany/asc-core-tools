<?php
/**
 * SettingsPage Class
 *
 * Admin class for the settings page.
 *
 * @package: asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

use ASC\CoreTools\Core\Core as Settings;

/**
 * SettingsPage Class
 */
class SettingsPage {
	/**
	 * Initialize the SettingsPage class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize settings page components.
	 *
	 * @return void
	 */
	private function init(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		$option_name = Admin::OPTION_NAME;
		$page_slug = Admin::PAGE_SLUG;

		register_setting(
			'asc_core_tools_settings_group',
			$option_name,
			array(
				'type' => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'asc_core_tools_general_section',
			'',
			'__return_false',
			$page_slug
		);

		$subsection_cb = '__return_false';

		add_settings_field(
			'general_security_heading',
			__( 'XML-RPC', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_general_section'
		);

		add_settings_field(
			'disable_xmlrpc',
			__( 'Disable XML-RPC', 'asc-core-tools' ),
			array( $this, 'render_disable_xmlrpc' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'disable_xmlrpc' )
		);

		add_settings_field(
			'general_updates_heading',
			__( 'Autoupdate Emails', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_general_section'
		);

		add_settings_field(
			'disable_autoupdate_emails',
			__( 'Disable autoupdate emails', 'asc-core-tools' ),
			array( $this, 'render_disable_autoupdate_emails' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'disable_autoupdate_emails' )
		);

		add_settings_field(
			'general_autosave_heading',
			__( 'Autosave / Heartbeat API', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_general_section'
		);

		add_settings_field(
			'disable_autosave',
			__( 'Disable Autosave / Heartbeat API', 'asc-core-tools' ),
			array( $this, 'render_disable_autosave' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'disable_autosave' )
		);

		add_settings_field(
			'autosave_interval_seconds',
			__( 'Autosave Interval (Seconds):', 'asc-core-tools' ),
			array( $this, 'render_autosave_interval' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'autosave_interval_seconds' )
		);

		add_settings_field(
			'general_revisions_heading',
			__( 'Revisions', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_general_section'
		);

		add_settings_field(
			'disable_revisions',
			__( 'Disable Revisions', 'asc-core-tools' ),
			array( $this, 'render_disable_revisions' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'disable_revisions' )
		);

		add_settings_field(
			'number_revisions',
			__( 'Number of Kept Revisions (Use -1 for Unlimited):', 'asc-core-tools' ),
			array( $this, 'render_number_revisions' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'number_revisions' )
		);

		add_settings_field(
			'general_comments_heading',
			__( 'Comments', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_general_section'
		);

		add_settings_field(
			'disable_comments',
			__( 'Disable Comments', 'asc-core-tools' ),
			array( $this, 'render_disable_comments' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'disable_comments' )
		);

		add_settings_field(
			'general_hide_login_heading',
			__( 'Hide Login', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_general_section'
		);
		add_settings_field(
			'hide_login',
			__( 'Hide the default WordPress login page', 'asc-core-tools' ),
			array( $this, 'render_hide_login' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'hide_login' )
		);
		add_settings_field(
			'login_page_slug',
			__( 'Enter the page slug for the new login page URL:', 'asc-core-tools' ),
			array( $this, 'render_login_page_slug' ),
			$page_slug,
			'asc_core_tools_general_section',
			array( 'label_for' => 'login_page_slug' )
		);

		add_settings_section(
			'asc_core_tools_database_section',
			'',
			'__return_false',
			$page_slug
		);

		add_settings_section(
			'asc_core_tools_features_section',
			'',
			'__return_false',
			$page_slug
		);

		// Features tab fields.
		add_settings_field(
			'features_shortcodes_heading',
			__( 'Shortcodes', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'features_shortcodes_display',
			'',
			array( $this, 'render_shortcodes_display' ),
			$page_slug,
			'asc_core_tools_features_section'
		);

		add_settings_field(
			'features_local_fonts_heading',
			__( 'Local Fonts', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'enable_local_fonts',
			__( 'Enable local fonts', 'asc-core-tools' ),
			array( $this, 'render_enable_local_fonts' ),
			$page_slug,
			'asc_core_tools_features_section',
			array( 'label_for' => 'enable_local_fonts' )
		);
		add_settings_field(
			'local_fonts_actions',
			'',
			array( $this, 'render_local_fonts_actions' ),
			$page_slug,
			'asc_core_tools_features_section'
		);

		add_settings_field(
			'features_fontawesome_heading',
			__( 'Font Awesome', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'self_host_fontawesome',
			__( 'Enable local Font Awesome fonts', 'asc-core-tools' ),
			array( $this, 'render_self_host_fontawesome' ),
			$page_slug,
			'asc_core_tools_features_section',
			array( 'label_for' => 'self_host_fontawesome' )
		);

		add_settings_field(
			'features_social_heading',
			__( 'Social Sharing', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'features_social_description',
			'',
			array( $this, 'render_social_description' ),
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'enable_social_sharing',
			__( 'Enable Social Sharing', 'asc-core-tools' ),
			array( $this, 'render_enable_social_sharing' ),
			$page_slug,
			'asc_core_tools_features_section',
			array( 'label_for' => 'enable_social_sharing' )
		);
		add_settings_field(
			'social_sharing_post_types',
			__( 'Set Social Sharing Post Types:', 'asc-core-tools' ),
			array( $this, 'render_social_sharing_post_types' ),
			$page_slug,
			'asc_core_tools_features_section',
			array( 'label_for' => 'social_sharing_post_types' )
		);
		add_settings_field(
			'social_sharing_networks',
			'',
			array( $this, 'render_social_sharing_networks' ),
			$page_slug,
			'asc_core_tools_features_section'
		);

		add_settings_field(
			'features_ninja_heading',
			__( 'Ninja Forms', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'enable_ninja_forms',
			__( 'Enable Ninja Forms Customization', 'asc-core-tools' ),
			array( $this, 'render_enable_ninja_forms' ),
			$page_slug,
			'asc_core_tools_features_section',
			array( 'label_for' => 'enable_ninja_forms' )
		);
	}

	/**
	 * Sanitize settings array.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$defaults = Settings::get_default_settings();
		$current = Settings::get_settings();
		$output = array();

		$output['disable_xmlrpc'] = 0;
		if ( ! empty( $input['disable_xmlrpc'] ) ) {
			$output['disable_xmlrpc'] = 1;
		}

		$output['disable_autoupdate_emails'] = 0;
		if ( ! empty( $input['disable_autoupdate_emails'] ) ) {
			$output['disable_autoupdate_emails'] = 1;
		}

		$output['disable_autosave'] = 0;
		if ( ! empty( $input['disable_autosave'] ) ) {
			$output['disable_autosave'] = 1;
		}

		$output['autosave_interval_seconds'] = $defaults['autosave_interval_seconds'];
		if ( isset( $input['autosave_interval_seconds'] ) ) {
			$output['autosave_interval_seconds'] = max( 1, (int) $input['autosave_interval_seconds'] );
		}

		$output['disable_revisions'] = 0;
		if ( ! empty( $input['disable_revisions'] ) ) {
			$output['disable_revisions'] = 1;
		}

		$output['number_revisions'] = $defaults['number_revisions'];
		if ( isset( $input['number_revisions'] ) ) {
			$output['number_revisions'] = (int) $input['number_revisions'];
		}

		$output['disable_comments'] = 0;
		if ( ! empty( $input['disable_comments'] ) ) {
			$output['disable_comments'] = 1;
		}

		$output['hide_login'] = 0;
		if ( ! empty( $input['hide_login'] ) ) {
			$output['hide_login'] = 1;
		}

		$login_slug = sanitize_title( (string) ( $input['login_page_slug'] ?? $defaults['login_page_slug'] ) );
		$forbidden_slugs = array( 'wp-admin', 'wp-login', 'wp-content', 'wp-includes', 'wp-signup', 'wp-activate', 'admin', 'login', 'options' );
		if ( $login_slug !== '' && in_array( $login_slug, $forbidden_slugs, true ) ) {
			$login_slug = $defaults['login_page_slug'];
		}
		$output['login_page_slug'] = $login_slug;

		$output['enable_ninja_forms'] = 0;
		if ( ! empty( $input['enable_ninja_forms'] ) ) {
			$output['enable_ninja_forms'] = 1;
		}

		$output['enable_social_sharing'] = 0;
		if ( ! empty( $input['enable_social_sharing'] ) ) {
			$output['enable_social_sharing'] = 1;
		}

		$output['social_sharing_post_types'] = sanitize_text_field( (string) ( $input['social_sharing_post_types'] ?? $defaults['social_sharing_post_types'] ) );

		$output['share_facebook'] = 0;
		if ( ! empty( $input['share_facebook'] ) ) {
			$output['share_facebook'] = 1;
		}

		$output['share_x'] = 0;
		if ( ! empty( $input['share_x'] ) ) {
			$output['share_x'] = 1;
		}

		$output['share_linkedin'] = 0;
		if ( ! empty( $input['share_linkedin'] ) ) {
			$output['share_linkedin'] = 1;
		}

		$output['share_bluesky'] = 0;
		if ( ! empty( $input['share_bluesky'] ) ) {
			$output['share_bluesky'] = 1;
		}

		$output['share_email'] = 0;
		if ( ! empty( $input['share_email'] ) ) {
			$output['share_email'] = 1;
		}

		$output['share_copy_link'] = 0;
		if ( ! empty( $input['share_copy_link'] ) ) {
			$output['share_copy_link'] = 1;
		}

		$output['self_host_fontawesome'] = 0;
		if ( ! empty( $input['self_host_fontawesome'] ) ) {
			$output['self_host_fontawesome'] = 1;
		}

		$output['enable_local_fonts'] = 0;
		if ( ! empty( $input['enable_local_fonts'] ) ) {
			$output['enable_local_fonts'] = 1;
		}

		$result = wp_parse_args( $output, wp_parse_args( $current, $defaults ) );

		// When Hide Login is enabled, add the login slug rewrite rule and flush so it is persisted.
		// (If we only flushed, the rule would be missing because init ran with old options.)
		if ( ! empty( $result['hide_login'] ) && ! empty( $result['login_page_slug'] ) ) {
			$slug = $result['login_page_slug'];

			add_rewrite_rule(
				'^' . preg_quote( $slug, '#' ) . '/?$',
				'index.php?asc_core_tools_login=1',
				'top'
			);

			flush_rewrite_rules( true );
		} else {
			// When Hide Login is turned off, flush so the custom login rule is removed.
			$had_hide_login = ! empty( $current['hide_login'] ) && ! empty( $current['login_page_slug'] );

			if ( $had_hide_login ) {
				flush_rewrite_rules( true );
			}
		}

		return $result;
	}

	/**
	 * Render Disable XML-RPC checkbox.
	 *
	 * @return void
	 */
	public function render_disable_xmlrpc(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['disable_xmlrpc'] );
		$name = Admin::OPTION_NAME . '[disable_xmlrpc]';
		$id = 'disable_xmlrpc';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Disable autoupdate emails checkbox.
	 *
	 * @return void
	 */
	public function render_disable_autoupdate_emails(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['disable_autoupdate_emails'] );
		$name = Admin::OPTION_NAME . '[disable_autoupdate_emails]';
		$id = 'disable_autoupdate_emails';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Disable Autosave checkbox.
	 *
	 * @return void
	 */
	public function render_disable_autosave(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['disable_autosave'] );
		$name = Admin::OPTION_NAME . '[disable_autosave]';
		$id = 'disable_autosave';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Autosave Interval number input.
	 *
	 * @return void
	 */
	public function render_autosave_interval(): void {
		$settings = Settings::get_settings();
		$value = (int) ( $settings['autosave_interval_seconds'] ?? 60 );
		$name = Admin::OPTION_NAME . '[autosave_interval_seconds]';
		$id = 'autosave_interval_seconds';

		?>
		<input type="number" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="<?php echo esc_attr( (string) $value ); ?>" min="1" step="1" class="small-text">
		<?php
	}

	/**
	 * Render Disable Revisions checkbox.
	 *
	 * @return void
	 */
	public function render_disable_revisions(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['disable_revisions'] );
		$name = Admin::OPTION_NAME . '[disable_revisions]';
		$id = 'disable_revisions';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Number of Kept Revisions number input.
	 *
	 * @return void
	 */
	public function render_number_revisions(): void {
		$settings = Settings::get_settings();
		$value = (int) ( $settings['number_revisions'] ?? -1 );
		$name = Admin::OPTION_NAME . '[number_revisions]';
		$id = 'number_revisions';
		?>
		<input type="number" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="<?php echo esc_attr( (string) $value ); ?>" min="-1" step="1" class="small-text">
		<?php
	}

	/**
	 * Render Disable Comments checkbox.
	 *
	 * @return void
	 */
	public function render_disable_comments(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['disable_comments'] );
		$name = Admin::OPTION_NAME . '[disable_comments]';
		$id = 'disable_comments';
		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Hide Login checkbox.
	 *
	 * @return void
	 */
	public function render_hide_login(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['hide_login'] );
		$name = Admin::OPTION_NAME . '[hide_login]';
		$id = 'hide_login';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Login Page Slug text input.
	 *
	 * @return void
	 */
	public function render_login_page_slug(): void {
		$settings = Settings::get_settings();
		$value = $settings['login_page_slug'] ?? '';
		$name = Admin::OPTION_NAME . '[login_page_slug]';
		$id = 'login_page_slug';

		?>
		<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	/**
	 * Render Shortcodes display (Features tab).
	 *
	 * @return void
	 */
	public function render_shortcodes_display(): void {
		?>
		<div class="asc-core-tools-description"><?php esc_html_e( 'Manually add a social sharing bar to a page:', 'asc-core-tools' ); ?> <code>[asc_core_tools_social_sharing]</code></div>
		<div class="asc-core-tools-description"><?php esc_html_e( 'Shows the current year, useful for copyright footers:', 'asc-core-tools' ); ?> <code>[asc_core_tools_year]</code></div>
		<?php
	}

	/**
	 * Render Enable Ninja Forms Customization checkbox.
	 *
	 * @return void
	 */
	public function render_enable_ninja_forms(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['enable_ninja_forms'] );
		$name = Admin::OPTION_NAME . '[enable_ninja_forms]';
		$id = 'enable_ninja_forms';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Social Sharing description (Features tab).
	 *
	 * @return void
	 */
	public function render_social_description(): void {
		?>
		<div class="asc-core-tools-description"><?php esc_html_e( 'Font Awesome is required for the sharing icons. Either enable Self-host Font Awesome above or install the Font Awesome plugin.', 'asc-core-tools' ); ?></div>
		<div class="asc-core-tools-description"><?php esc_html_e( 'Either enable social sharing on listed post types (post, page, etc.) or use the shortcode:', 'asc-core-tools' ); ?> <code>[asc_core_tools_social_sharing]</code></div>
		<?php
	}

	/**
	 * Render Self-host Font Awesome checkbox.
	 *
	 * @return void
	 */
	public function render_self_host_fontawesome(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['self_host_fontawesome'] );
		$name = Admin::OPTION_NAME . '[self_host_fontawesome]';
		$id = 'self_host_fontawesome';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Enable local fonts checkbox.
	 *
	 * @return void
	 */
	public function render_enable_local_fonts(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['enable_local_fonts'] );
		$name = Admin::OPTION_NAME . '[enable_local_fonts]';
		$id = 'enable_local_fonts';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Local fonts description, Scan button, font list, and Generate CSS button.
	 *
	 * @return void
	 */
	public function render_local_fonts_actions(): void {
		$files = Fonts::get_last_directory_files();
		$message = __( 'No font files or fonts.css found.', 'asc-core-tools' );
		if ( count( $files ) > 0 ) {
			$message = __( 'Files in wp-content/fonts:', 'asc-core-tools' );
		}

		?>
		<div class="asc-core-tools-description"><?php esc_html_e( 'Upload your fonts to the wp-content/fonts directory.', 'asc-core-tools' ); ?></div>
		<p>
			<button type="button" class="button asc-core-tools-scan-fonts"><?php esc_html_e( 'Scan for fonts', 'asc-core-tools' ); ?></button>
			<button type="button" class="button asc-core-tools-generate-fonts-css"><?php esc_html_e( 'Generate CSS', 'asc-core-tools' ); ?></button>
		</p>
		<div class="asc-core-tools-font-list" aria-live="polite">
			<?php if ( count( $files ) > 0 ) : ?>
				<p><strong><?php echo esc_html( $message ); ?></strong></p>
				<ul>
					<?php foreach ( $files as $file ) : ?>
						<li><?php echo esc_html( $file ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php echo esc_html( $message ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Enable Social Sharing checkbox.
	 *
	 * @return void
	 */
	public function render_enable_social_sharing(): void {
		$settings = Settings::get_settings();
		$value = ! empty( $settings['enable_social_sharing'] );
		$name = Admin::OPTION_NAME . '[enable_social_sharing]';
		$id = 'enable_social_sharing';

		?>
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="1" <?php checked( $value ); ?>>
		<?php
	}

	/**
	 * Render Social Sharing Post Types text input.
	 *
	 * @return void
	 */
	public function render_social_sharing_post_types(): void {
		$settings = Settings::get_settings();
		$value = $settings['social_sharing_post_types'] ?? 'post,page';
		$name = Admin::OPTION_NAME . '[social_sharing_post_types]';
		$id = 'social_sharing_post_types';

		?>
		<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
			value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	/**
	 * Render Social Sharing networks checkboxes (which icons to show in the sharing bar).
	 *
	 * @return void
	 */
	public function render_social_sharing_networks(): void {
		?>
		<div class="asc-core-tools-description-label"><?php esc_html_e( 'Networks to show in sharing bar:', 'asc-core-tools' ); ?></div>
		<?php

		$settings = Settings::get_settings();
		$networks = array(
			'share_facebook' => __( 'Facebook', 'asc-core-tools' ),
			'share_linkedin' => __( 'LinkedIn', 'asc-core-tools' ),
			'share_bluesky' => __( 'Bluesky', 'asc-core-tools' ),
			'share_x' => __( 'X', 'asc-core-tools' ),
			'share_email' => __( 'Email', 'asc-core-tools' ),
			'share_copy_link' => __( 'Copy link', 'asc-core-tools' ),
		);

		foreach ( $networks as $key => $label ) {
			$value = ! empty( $settings[ $key ] );
			$name = Admin::OPTION_NAME . '[' . $key . ']';
			$id = $key;

			?>
			<div class="asc-core-tools-checkbox">
				<label for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
						value="1" <?php checked( $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			</div>
			<?php
		}
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show success message if settings were saved
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'asc_core_tools_messages',
				'asc_core_tools_message',
				__( 'Settings saved successfully.', 'asc-core-tools' ),
				'success'
			);
		}
		settings_errors( 'asc_core_tools_messages' );

		// Get current settings
		$settings = Settings::get_settings();

		/*
		 * Tab panels
		 */

		// Tabs available
		$tabs = array( 'wordpress', 'display', 'database' );

		// Get active tab or default to 'wordpress'; allow only whitelisted tabs.
		$active_tab = 'wordpress';
		if ( isset( $_GET['tab'] ) ) {
			$requested = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			if ( in_array( $requested, $tabs, true ) ) {
				$active_tab = $requested;
			}
		}

		// Set tab that is active with active class
		$active_tab_class = array(
			'wordpress' => '',
			'display'   => '',
			'database'  => '',
		);
		$active_tab_class[$active_tab] = ' nav-tab-active';

		// Hide all tabs with CSS that are not active
		$inactive_tab_css = array(
			'wordpress' => '',
			'display'   => '',
			'database'  => '',
		);

		foreach ( $tabs as $tab ) {
			if ( $tab !== $active_tab ) {
				$inactive_tab_css[ $tab ] = ' style="display: none;"';
			}
		}

		$aria_selected_wordpress = 'false';
		if ( $active_tab === 'wordpress' ) {
			$aria_selected_wordpress = 'true';
		}

		$aria_selected_display = 'false';
		if ( $active_tab === 'display' ) {
			$aria_selected_display = 'true';
		}

		$aria_selected_database = 'false';
		if ( $active_tab === 'database' ) {
			$aria_selected_database = 'true';
		}

		$save_wrap_style = '';
		if ( $active_tab === 'database' ) {
			$save_wrap_style = ' style="display: none;"';
		}

		/*
		 * Render Settings Sections and Fields
		 */

		global $wp_settings_sections, $wp_settings_fields;

		?>
		<div class="wrap asc-core-tools-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<nav class="nav-tab-wrapper asc-core-tools-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'asc-core-tools' ); ?>">
				<a href="#" class="nav-tab<?php echo esc_attr( $active_tab_class['wordpress'] ); ?>" id="asc-core-tools-tab-wordpress" role="tab" aria-selected="<?php echo esc_attr( $aria_selected_wordpress ); ?>" aria-controls="asc-core-tools-panel-wordpress" data-tab="wordpress">
					<?php esc_html_e( 'WordPress', 'asc-core-tools' ); ?>
				</a>
				<a href="#" class="nav-tab<?php echo esc_attr( $active_tab_class['display'] ); ?>" id="asc-core-tools-tab-display" role="tab" aria-selected="<?php echo esc_attr( $aria_selected_display ); ?>" aria-controls="asc-core-tools-panel-display" data-tab="display">
					<?php esc_html_e( 'Display', 'asc-core-tools' ); ?>
				</a>
				<a href="#" class="nav-tab<?php echo esc_attr( $active_tab_class['database'] ); ?>" id="asc-core-tools-tab-database" role="tab" aria-selected="<?php echo esc_attr( $aria_selected_database ); ?>" aria-controls="asc-core-tools-panel-database" data-tab="database">
					<?php esc_html_e( 'Database', 'asc-core-tools' ); ?>
				</a>
			</nav>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'asc_core_tools_settings_group' );
				?>

				<div class="asc-core-tools-tab-content asc-core-tools-wordpress-tab" id="asc-core-tools-panel-wordpress" role="tabpanel" aria-labelledby="asc-core-tools-tab-wordpress"<?php echo esc_attr( $inactive_tab_css['wordpress'] ); ?>>
					<h2><?php esc_html_e( 'WordPress Settings', 'asc-core-tools' ); ?></h2>
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$general_fields = (array) ( $wp_settings_fields[ Admin::PAGE_SLUG ]['asc_core_tools_general_section'] ?? array() );
							$sections = array();
							$current_title = null;
							foreach ( $general_fields as $field_id => $field ) {
								$is_heading = false;
								if ( ( isset( $field['callback'] ) && $field['callback'] === '__return_false' ) || ( is_string( $field_id ) && str_ends_with( $field_id, '_heading' ) ) ) {
									$is_heading = true;
								}
								if ( $is_heading ) {
									$current_title = $field['title'];
								} else {
									if ( $current_title !== null ) {
										$sections[ $current_title ][] = array(
											'id' => $field_id,
											'title' => $field['title'],
											'callback' => $field['callback'],
											'args' => $field['args'] ?? array(),
											'is_number' => in_array( $field_id, array( 'autosave_interval_seconds', 'number_revisions', 'login_page_slug' ), true ),
										);
									}
								}
							}
							foreach ( $sections as $section_title => $section_fields ) {
								?>
								<tr>
									<th scope="row"><?php echo esc_html( $section_title ); ?></th>
									<td>
										<fieldset>
											<legend class="screen-reader-text"><span><?php echo esc_html( $section_title ); ?></span></legend>
											<?php
											foreach ( $section_fields as $f ) {
												$args = $f['args'];
												$label_for = $args['label_for'] ?? $f['id'];
												if ( $f['is_number'] ) {
													?>
													<div class="asc-core-tools-table-input-description">
														<label for="<?php echo esc_attr( $label_for ); ?>"><?php echo esc_html( $f['title'] ); ?></label>
														<?php call_user_func( $f['callback'], $args ); ?>
													</div>
													<?php
												} else {
													?>
													<div class="asc-core-tools-checkbox">
														<label>
															<?php call_user_func( $f['callback'], $args ); ?>
															<?php echo esc_html( $f['title'] ); ?>
														</label>
													</div>
													<?php
												}
											}
											?>
										</fieldset>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>

				<div class="asc-core-tools-tab-content asc-core-tools-display-tab" id="asc-core-tools-panel-display" role="tabpanel" aria-labelledby="asc-core-tools-tab-display"<?php echo esc_attr( $inactive_tab_css['display'] ); ?>>
					<h2><?php esc_html_e( 'Display Settings', 'asc-core-tools' ); ?></h2>
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$general_fields = (array) ( $wp_settings_fields[ Admin::PAGE_SLUG ]['asc_core_tools_features_section'] ?? array() );
							$sections = array();
							$current_title = null;
							foreach ( $general_fields as $field_id => $field ) {
								$is_heading = false;
								if ( ( isset( $field['callback'] ) && $field['callback'] === '__return_false' ) || ( is_string( $field_id ) && str_ends_with( $field_id, '_heading' ) ) ) {
									$is_heading = true;
								}
								if ( $is_heading ) {
									$current_title = $field['title'];
								} else {
									if ( $current_title !== null ) {
										$sections[ $current_title ][] = array(
											'id' => $field_id,
											'title' => $field['title'],
											'callback' => $field['callback'],
											'args' => $field['args'] ?? array(),
											'is_number' => in_array( $field_id, array( 'social_sharing_post_types' ), true ),
										);
									}
								}
							}
							foreach ( $sections as $section_title => $section_fields ) {
								?>
								<tr>
									<th scope="row"><?php echo esc_html( $section_title ); ?></th>
									<td>
										<fieldset>
											<legend class="screen-reader-text"><span><?php echo esc_html( $section_title ); ?></span></legend>
											<?php
											foreach ( $section_fields as $f ) {
												$args = $f['args'];
												$label_for = $args['label_for'] ?? $f['id'];
												if ( $f['is_number'] ) {
													?>
													<div class="asc-core-tools-table-input-description">
														<label for="<?php echo esc_attr( $label_for ); ?>"><?php echo esc_html( $f['title'] ); ?></label>
														<?php call_user_func( $f['callback'], $args ); ?>
													</div>
													<?php
												} elseif ( $f['title'] === '' ) {
													?>
													<?php call_user_func( $f['callback'], $args ); ?>
													<?php
												} else {
													?>
													<div class="asc-core-tools-checkbox">
														<label>
															<?php call_user_func( $f['callback'], $args ); ?>
															<?php echo esc_html( $f['title'] ); ?>
														</label>
													</div>
													<?php
												}
											}
											?>
										</fieldset>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>

				<div class="asc-core-tools-tab-content asc-core-tools-database-tab" id="asc-core-tools-panel-database" role="tabpanel" aria-labelledby="asc-core-tools-tab-database"<?php echo esc_attr( $inactive_tab_css['database'] ); ?>>
					<h2><?php esc_html_e( 'Database Maintenance', 'asc-core-tools' ); ?></h2>
					<table class="form-table asc-core-tools-database-table" role="presentation">
						<tbody>
							<tr>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php esc_html_e( 'Warning', 'asc-core-tools' ); ?></span></legend>
										<div class="asc-core-tools-description"><strong><?php esc_html_e( 'Warning:', 'asc-core-tools' ); ?></strong> <?php esc_html_e( 'Deleting obsolete data will delete trash, auto-draft, and revision posts.', 'asc-core-tools' ); ?></div>
									</fieldset>
								</td>
							</tr>
							<tr>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php esc_html_e( 'Actions', 'asc-core-tools' ); ?></span></legend>
										<button type="button" class="asc-core-tools-delete-obsolete-data button button-primary"><?php esc_html_e( 'Delete Obsolete Data', 'asc-core-tools' ); ?></button>
										<button type="button" class="asc-core-tools-delete-orphaned-data button button-primary"><?php esc_html_e( 'Delete Orphaned Data', 'asc-core-tools' ); ?></button>
										<button type="button" class="asc-core-tools-optimize-tables button button-primary"><?php esc_html_e( 'Optimize Tables', 'asc-core-tools' ); ?></button>
									</fieldset>
								</td>
							</tr>
							<tr>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php esc_html_e( 'Messages', 'asc-core-tools' ); ?></span></legend>
										<div class="asc-core-tools-db-status" aria-live="polite" aria-atomic="true"></div>
										<button type="button" class="asc-core-tools-clear-db-messages button"><?php esc_html_e( 'Clear Messages', 'asc-core-tools' ); ?></button>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="asc-core-tools-save-wrap"<?php echo esc_attr( $save_wrap_style ); ?>>
					<?php submit_button( __( 'Save Settings', 'asc-core-tools' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}