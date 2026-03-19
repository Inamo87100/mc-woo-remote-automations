<?php
/**
 * PHPUnit bootstrap for MC-Woo Remote Automations tests.
 *
 * Loads the WordPress test environment and the plugin itself.
 *
 * Usage:
 *   phpunit --bootstrap tests/bootstrap.php
 *
 * @package MC_Woo_Remote_Automations
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php\n";
	exit( 1 );
}

// Load the WordPress test functions.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin under test.
 * WooCommerce stub must be loaded first.
 */
function _manually_load_woo_plugin() {
	// Load a minimal WooCommerce stub if WooCommerce is not installed.
	$wc_stub = dirname( __DIR__ ) . '/tests/stubs/woocommerce-stub.php';
	if ( file_exists( $wc_stub ) ) {
		require_once $wc_stub;
	}
	require dirname( __DIR__ ) . '/mc-woo-remote-automations.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_woo_plugin' );

// Bootstrap WordPress.
require $_tests_dir . '/includes/bootstrap.php';
