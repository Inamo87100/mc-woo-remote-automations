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
