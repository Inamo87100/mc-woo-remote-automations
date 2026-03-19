<?php
/**
 * Global helper functions for MC Remote API.
 *
 * @package MC_Remote_API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the stored API secret.
 *
 * @return string
 */
function mc_remote_api_get_secret() {
	return (string) get_option( 'mc_api_secret', '' );
}
