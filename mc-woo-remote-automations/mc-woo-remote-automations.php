<?php
/**
 * Plugin Name:       MC-Woo Remote Automations
 * Plugin URI:        https://mambacoding.com/
 * Description:       Automate remote user creation and role assignment from WooCommerce orders.
 * Version:           1.1.3
 * Author:            Mamba Coding
 * Author URI:        https://mambacoding.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mc-woo-remote-automations
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * WC requires at least: 3.0
 * WC tested up to:   9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// Plugin version.
define( 'MC_WOO_REMOTE_VERSION', '1.1.3' );

// Absolute path to the plugin directory (with trailing slash).
define( 'MC_WOO_REMOTE_PATH', plugin_dir_path( __FILE__ ) );

// Public URL to the plugin directory (with trailing slash).
define( 'MC_WOO_REMOTE_URL', plugin_dir_url( __FILE__ ) );

// Absolute path to the main plugin file.
define( 'MC_WOO_REMOTE_FILE', __FILE__ );

// Load translations.
add_action(
'plugins_loaded',
function () {
load_plugin_textdomain( 'mc-woo-remote-automations', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
);

// Load includes.
require_once MC_WOO_REMOTE_PATH . 'includes/functions.php';
require_once MC_WOO_REMOTE_PATH . 'includes/class-helpers.php';
require_once MC_WOO_REMOTE_PATH . 'includes/class-admin-banner.php';
require_once MC_WOO_REMOTE_PATH . 'admin/class-admin.php';
require_once MC_WOO_REMOTE_PATH . 'includes/class-main.php';

// Bootstrap the plugin.
new MC_Woo_Remote_Main();
