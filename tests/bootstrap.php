<?php
/**
 * PHPUnit bootstrap for the combined MC plugin test suite.
 *
 * Loads the WordPress test environment and both plugins.
 *
 * Usage:
 *   phpunit --bootstrap tests/bootstrap.php
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
 * Manually load both plugins under test.
 */
function _manually_load_mc_plugins() {
	// Load a minimal WooCommerce stub if WooCommerce is not installed.
	$wc_stub = dirname( __DIR__ ) . '/mc-woo-remote-automations/tests/stubs/woocommerce-stub.php';
	if ( file_exists( $wc_stub ) ) {
		require_once $wc_stub;
	}

	require dirname( __DIR__ ) . '/mc-remote-api/mc-remote-api.php';
	require dirname( __DIR__ ) . '/mc-woo-remote-automations/mc-woo-remote-automations.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_mc_plugins' );

// Bootstrap WordPress.
require $_tests_dir . '/includes/bootstrap.php';
