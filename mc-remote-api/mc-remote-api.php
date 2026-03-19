<?php
/**
 * Plugin Name:       MC Remote API
 * Plugin URI:        https://mambacoding.com/
 * Description:       API endpoints for remote user creation and role assignment.
 * Version:           1.1.0
 * Author:            Mamba Coding
 * Author URI:        https://mambacoding.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mc-remote-api
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
define( 'MC_REMOTE_API_VERSION', '1.1.0' );

// Absolute path to the plugin directory (with trailing slash).
define( 'MC_REMOTE_API_PATH', plugin_dir_path( __FILE__ ) );

// Public URL to the plugin directory (with trailing slash).
define( 'MC_REMOTE_API_URL', plugin_dir_url( __FILE__ ) );

// Absolute path to the main plugin file.
define( 'MC_REMOTE_API_FILE', __FILE__ );

// Load translations.
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'mc-remote-api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

// Load includes.
require_once MC_REMOTE_API_PATH . 'includes/functions.php';
require_once MC_REMOTE_API_PATH . 'includes/class-admin-banner.php';
require_once MC_REMOTE_API_PATH . 'includes/class-main.php';

// Bootstrap the plugin.
new MC_Remote_API_Main();
