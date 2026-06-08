<?php
/**
 * Uninstall routine for MC-Woo Remote Automations.
 *
 * @package MC_Woo_Remote_Automations
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$delete_all_data = get_option( 'mc_wra_delete_data_on_uninstall', 'no' );

if ( 'yes' !== $delete_all_data ) {
	return;
}

global $wpdb;

$table = $wpdb->prefix . 'mc_wra_logs';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$post_ids = get_posts(
	array(
		'post_type'      => array( 'mcwra_connection', 'mcwra_automation' ),
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'suppress_filters' => true,
	)
);

foreach ( $post_ids as $post_id ) {
	wp_delete_post( (int) $post_id, true );
}

delete_option( 'mc_wra_operating_mode' );
delete_option( 'mc_wra_api_secret' );
delete_option( 'mc_api_secret' );
delete_option( 'mc_wra_default_timeout' );
delete_option( 'mc_wra_log_retention_days' );
delete_option( 'mc_wra_delete_data_on_uninstall' );
