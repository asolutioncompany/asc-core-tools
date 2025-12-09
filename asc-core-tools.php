<?php

declare( strict_types = 1 );

/**
 * aS.c Core tools
 *
 * A lightweight WordPress plugin for common customizations of WordPress.
 *
 * The benefit of this plugin is to quickly secure a WordPress website, provide customizations
 * that usually require custom coding, and quickly template a default plugin for the website to
 * make further small customizations.
 *
 * It is built for developers in mind who just need a quick solution to cover the basics for small
 * websites.
 *
 * Features:
 *
 * - Disable XMLPRC.php
 * - Hide WordPress Login
 * - Disable or change number of post revisions saved
 * - Disable or change interval for autosaves (WordPress Heartbeat API)
 * - Disable autoupdate emails
 * - Disable all comments
 * - Database maintenance
 * - Generate template CSS, Javascript, and PHP files to quickly customize WordPress
 *
 * Visit the Github page for the Setup Guide and more information:
 *
 * https://github.com/asolutioncompany/asc-core-tools
 *
 * @wordpress-plugin
 * Plugin Name: aS.c Core tools
 * Plugin URI: https://github.com/asolutioncompany/asc-core-tools
 * Description: Common customizations for WordPress
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Author: aSolution.company
 * Author URI: https://asolution.company
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: asc-ai-summaries
 * Domain Path: /languages
 */

namespace ASolutionCompany\CoreTools;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    exit;
}

// Load Composer autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\CoreTools', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\\CoreTools', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\\CoreTools', 'uninstall' ) );

// Initialize main class object
$asc_core_tools = CoreTools::get_instance();