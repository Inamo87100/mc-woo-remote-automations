<?php
/**
 * PHPUnit bootstrap for MC Remote API tests.
 *
 * Loads the WordPress test environment and the plugin itself.
 *
 * Usage:
 *   phpunit --bootstrap tests/bootstrap.php
 *
 * @package MC_Remote_API
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
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/mc-remote-api.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Bootstrap WordPress.
require $_tests_dir . '/includes/bootstrap.php';
