<?php
/**
 * Admin Class
 *
 * Core admin class that maintains constants and initializes admin components.
 *
 * @package: asc-ai-summaries
 * @since: 0.1.0
 */

declare( strict_types = 1 );

namespace ASolutionCompany\AISummaries\Admin;

/**
 * Admin Class
 */
class Admin {

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'asc-ai-summaries';

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'asc_ais_settings';

	/**
	 * Settings page instance.
	 *
	 * @var SettingsPage|null
	 */
	private ?SettingsPage $settings_page = null;

	/**
	 * Post meta panel instance.
	 *
	 * @var PostMetaPanel|null
	 */
	private ?PostMetaPanel $post_meta_panel = null;

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

		// Initialize post meta panel
		$this->post_meta_panel = new PostMetaPanel();
	}

	/**
	 * Get settings page instance.
	 *
	 * @return SettingsPage|null
	 */
	public function get_settings_page(): ?SettingsPage {
		return $this->settings_page;
	}

	/**
	 * Get post meta panel instance.
	 *
	 * @return PostMetaPanel|null
	 */
	public function get_post_meta_panel(): ?PostMetaPanel {
		return $this->post_meta_panel;
	}
}