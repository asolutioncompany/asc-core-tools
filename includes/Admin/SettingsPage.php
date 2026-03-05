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
			'features_fontawesome_heading',
			__( 'Font Awesome', 'asc-core-tools' ),
			$subsection_cb,
			$page_slug,
			'asc_core_tools_features_section'
		);
		add_settings_field(
			'self_host_fontawesome',
			__( 'Self-host Font Awesome', 'asc-core-tools' ),
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

		$output['disable_xmlrpc'] = ! empty( $input['disable_xmlrpc'] ) ? 1 : 0;
		$output['disable_autoupdate_emails'] = ! empty( $input['disable_autoupdate_emails'] ) ? 1 : 0;
		$output['disable_autosave'] = ! empty( $input['disable_autosave'] ) ? 1 : 0;
		$output['autosave_interval_seconds'] = isset( $input['autosave_interval_seconds'] )
			? max( 1, (int) $input['autosave_interval_seconds'] )
			: $defaults['autosave_interval_seconds'];
		$output['disable_revisions'] = ! empty( $input['disable_revisions'] ) ? 1 : 0;
		$output['number_revisions'] = isset( $input['number_revisions'] )
			? (int) $input['number_revisions']
			: $defaults['number_revisions'];
		$output['disable_comments'] = ! empty( $input['disable_comments'] ) ? 1 : 0;
		$output['hide_login'] = ! empty( $input['hide_login'] ) ? 1 : 0;
		$login_slug = isset( $input['login_page_slug'] ) ? sanitize_title( $input['login_page_slug'] ) : $defaults['login_page_slug'];
		$forbidden_slugs = array( 'wp-admin', 'wp-login', 'wp-content', 'wp-includes', 'wp-signup', 'wp-activate', 'admin', 'login', 'options' );
		if ( $login_slug !== '' && in_array( $login_slug, $forbidden_slugs, true ) ) {
			$login_slug = $defaults['login_page_slug'];
		}
		$output['login_page_slug'] = $login_slug;
		$output['enable_ninja_forms'] = ! empty( $input['enable_ninja_forms'] ) ? 1 : 0;
		$output['enable_social_sharing'] = ! empty( $input['enable_social_sharing'] ) ? 1 : 0;
		$output['social_sharing_post_types'] = isset( $input['social_sharing_post_types'] )
			? sanitize_text_field( $input['social_sharing_post_types'] )
			: $defaults['social_sharing_post_types'];
		$output['share_facebook'] = ! empty( $input['share_facebook'] ) ? 1 : 0;
		$output['share_x'] = ! empty( $input['share_x'] ) ? 1 : 0;
		$output['share_linkedin'] = ! empty( $input['share_linkedin'] ) ? 1 : 0;
		$output['share_bluesky'] = ! empty( $input['share_bluesky'] ) ? 1 : 0;
		$output['share_email'] = ! empty( $input['share_email'] ) ? 1 : 0;
		$output['share_copy_link'] = ! empty( $input['share_copy_link'] ) ? 1 : 0;
		$output['self_host_fontawesome'] = ! empty( $input['self_host_fontawesome'] ) ? 1 : 0;

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
		$value = isset( $settings['autosave_interval_seconds'] ) ? (int) $settings['autosave_interval_seconds'] : 60;
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
		$value = isset( $settings['number_revisions'] ) ? (int) $settings['number_revisions'] : -1;
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
		$value = isset( $settings['login_page_slug'] ) ? $settings['login_page_slug'] : '';
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
		$value = isset( $settings['social_sharing_post_types'] ) ? $settings['social_sharing_post_types'] : 'post,page';
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
			'share_linkedin' => __( 'LinkedIn', 'asc-core-tools' ),
			'share_facebook' => __( 'Facebook', 'asc-core-tools' ),
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
		$tabs = array( 'general', 'database', 'features' );

		// Get active tab or default to 'general'; allow only whitelisted tabs.
		$active_tab = 'general';
		if ( isset( $_GET['tab'] ) ) {
			$requested = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			if ( in_array( $requested, $tabs, true ) ) {
				$active_tab = $requested;
			}
		}

		// Set tab that is active with active class
		$active_tab_class = array(
			'general' => '',
			'database' => '',
			'features' => '',
		);
		$active_tab_class[$active_tab] = ' nav-tab-active';

		// Hide all tabs with CSS that are not active
		$inactive_tab_css = array(
			'general' => '',
			'database' => '',
			'features' => '',
		);

		foreach( $tabs as $tab ) {
			if ( $tab !== $active_tab ) {
				$inactive_tab_css[$tab] = ' style="display: none;"';
			}
		}

		/*
		 * Render Settings Sections and Fields
		 */

		global $wp_settings_sections, $wp_settings_fields;

		?>
		<div class="wrap asc-core-tools-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<nav class="nav-tab-wrapper asc-core-tools-tabs">
				<a class="nav-tab<?php echo $active_tab_class['general']; ?>" data-tab="general">
					<?php esc_html_e( 'General', 'asc-core-tools' ); ?>
				</a>
				<a class="nav-tab<?php echo $active_tab_class['features']; ?>" data-tab="features">
					<?php esc_html_e( 'Features', 'asc-core-tools' ); ?>
				</a>
				<a class="nav-tab<?php echo $active_tab_class['database']; ?>" data-tab="database">
					<?php esc_html_e( 'Database', 'asc-core-tools' ); ?>
				</a>
			</nav>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'asc_core_tools_settings_group' );
				?>

				<div class="asc-core-tools-tab-content asc-core-tools-general-tab"<?php echo $inactive_tab_css['general']; ?>>
					<h2><?php esc_html_e( 'General Settings', 'asc-core-tools' ); ?></h2>
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$general_fields = (array) ( $wp_settings_fields[ Admin::PAGE_SLUG ]['asc_core_tools_general_section'] ?? array() );
							$sections = array();
							$current_title = null;
							foreach ( $general_fields as $field_id => $field ) {
								$is_heading = ( isset( $field['callback'] ) && $field['callback'] === '__return_false' )
									|| ( is_string( $field_id ) && str_ends_with( $field_id, '_heading' ) );
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

				<div class="asc-core-tools-tab-content asc-core-tools-features-tab"<?php echo $inactive_tab_css['features']; ?>>
					<h2><?php esc_html_e( 'Features', 'asc-core-tools' ); ?></h2>
					<table class="form-table" role="presentation">
						<tbody>
							<?php
							$general_fields = (array) ( $wp_settings_fields[ Admin::PAGE_SLUG ]['asc_core_tools_features_section'] ?? array() );
							$sections = array();
							$current_title = null;
							foreach ( $general_fields as $field_id => $field ) {
								$is_heading = ( isset( $field['callback'] ) && $field['callback'] === '__return_false' )
									|| ( is_string( $field_id ) && str_ends_with( $field_id, '_heading' ) );
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

				<div class="asc-core-tools-tab-content asc-core-tools-database-tab"<?php echo $inactive_tab_css['database']; ?>>
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
										<div class="asc-core-tools-db-status"></div>
										<button type="button" class="asc-core-tools-clear-db-messages button"><?php esc_html_e( 'Clear Messages', 'asc-core-tools' ); ?></button>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<?php
				$save_wrap_style = ( $active_tab === 'database' ) ? ' style="display: none;"' : '';
				?>
				<div class="asc-core-tools-save-wrap"<?php echo $save_wrap_style; ?>>
					<?php submit_button( __( 'Save Settings', 'asc-core-tools' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}