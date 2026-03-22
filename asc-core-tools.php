<?php

declare( strict_types = 1 );

/**
 * aS.c Core Tools
 *
 * A lightweight WordPress plugin for common customizations of WordPress.
 *
 * Replaces numerous common plugins.
 *
 * This plugin is developed for public use as Free and Open Source Software
 * (FOSS).
 *
 * Tested with WordPress 6.9.1 and PHP 8.3.
 *
 * Visit the Github page for the Setup Guide and more information:
 *
 * https://github.com/asolutioncompany/asc-core-tools
 *
 * @wordpress-plugin
 * Plugin Name: aS.c Core Tools
 * Plugin URI: https://github.com/asolutioncompany/asc-core-tools
 * Description: Common customizations for WordPress
 * Version: 1.2.0
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 8.1
 * Author: aSolution.company
 * Author URI: https://asolution.company
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: asc-core-tools
 * Domain Path: /languages
 */

namespace ASC\CoreTools;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    exit;
}

define( 'ASC_CORE_TOOLS_PLUGIN_FILE', __FILE__ );

// Load Composer autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\Core\\Core', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\\Core\\Core', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\\Core\\Core', 'uninstall' ) );

// Initialize main class object
$asc_core_tools = Core\Core::get_instance();