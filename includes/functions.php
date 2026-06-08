<?php
/**
 * Helper functions for MC-Woo Remote Automations.
 *
 * @package MC_Woo_Remote_Automations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds an absolute URL from a base URL and an endpoint path.
 *
 * @param string $base     Base URL (e.g. https://example.com).
 * @param string $endpoint Endpoint path (e.g. /wp-json/mc/v1/ping).
 * @return string
 */
function mc_wra_build_url( $base, $endpoint ) {
	return rtrim( (string) $base, '/' ) . '/' . ltrim( (string) $endpoint, '/' );
}

/**
 * Validates a remote base URL for connection usage.
 *
 * Requires HTTPS for all remote hosts, with an HTTP exception for localhost and 127.0.0.1.
 *
 * @param string $base_url Base URL.
 * @return true|WP_Error
 */
function mc_wra_validate_remote_base_url( $base_url ) {
	$base_url = trim( (string) $base_url );

	if ( '' === $base_url ) {
		return new WP_Error(
			'mc_wra_remote_url_required',
			__( 'Remote Site URL is required.', 'mc-woo-remote-automations' )
		);
	}

	if ( ! wp_http_validate_url( $base_url ) ) {
		return new WP_Error(
			'mc_wra_remote_url_invalid',
			__( 'Remote Site URL is not valid.', 'mc-woo-remote-automations' )
		);
	}

	$parts  = wp_parse_url( $base_url );
	$scheme = strtolower( (string) ( $parts['scheme'] ?? '' ) );
	$host   = strtolower( (string) ( $parts['host'] ?? '' ) );

	if ( 'https' === $scheme ) {
		return true;
	}

	$is_local_dev_host = in_array( $host, array( 'localhost', '127.0.0.1' ), true );
	if ( 'http' === $scheme && $is_local_dev_host ) {
		return true;
	}

	return new WP_Error(
		'mc_wra_remote_url_insecure',
		__( 'Remote Site URL must use HTTPS. HTTP is allowed only for localhost or 127.0.0.1 during development.', 'mc-woo-remote-automations' )
	);
}

/**
 * Returns all product / variation IDs present in a WooCommerce order.
 *
 * @param WC_Order $order WooCommerce order object.
 * @return int[]
 */
function mc_wra_get_order_product_ids( $order ) {
	$ids = array();
	foreach ( $order->get_items() as $item ) {
		$pid = (int) $item->get_product_id();
		$vid = (int) $item->get_variation_id();
		if ( $pid ) {
			$ids[] = $pid;
		}
		if ( $vid ) {
			$ids[] = $vid;
		}
	}
	return array_values( array_unique( $ids ) );
}
