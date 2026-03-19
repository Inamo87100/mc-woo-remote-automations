<?php
/**
 * Core plugin logic for MC-Woo Remote Automations.
 *
 * Handles plugin activation, custom post type registration,
 * and WooCommerce order-status automation processing.
 *
 * @package MC_Woo_Remote_Automations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstraps the plugin and processes automation triggers.
 */
class MC_Woo_Remote_Main {

	/**
	 * Registers WordPress hooks.
	 */
	public function __construct() {
		register_activation_hook( MC_WOO_REMOTE_FILE, array( $this, 'activate' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'handle_order_status_change' ), 10, 4 );

		MC_Woo_Remote_Banner::init();
		new MC_Woo_Remote_Admin();
	}

	/**
	 * Plugin activation callback – creates the log database table.
	 */
	public function activate() {
		global $wpdb;
		$table   = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			created_at DATETIME NOT NULL,
			automation_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			connection_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			action_key VARCHAR(50) NOT NULL DEFAULT '',
			user_email VARCHAR(190) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT '',
			response_code INT NULL,
			message TEXT NULL,
			request_payload LONGTEXT NULL,
			response_body LONGTEXT NULL,
			PRIMARY KEY (id)
		) {$charset};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Registers the mcwra_connection and mcwra_automation custom post types.
	 */
	public function register_post_types() {
		register_post_type(
			'mcwra_connection',
			array(
				'labels'      => array(
					'name'               => __( 'Connections', 'mc-woo-remote-automations' ),
					'singular_name'      => __( 'Connection', 'mc-woo-remote-automations' ),
					'menu_name'          => __( 'Connections', 'mc-woo-remote-automations' ),
					'name_admin_bar'     => __( 'Connection', 'mc-woo-remote-automations' ),
					'add_new'            => __( 'Add Connection', 'mc-woo-remote-automations' ),
					'add_new_item'       => __( 'Add Connection', 'mc-woo-remote-automations' ),
					'new_item'           => __( 'New Connection', 'mc-woo-remote-automations' ),
					'edit_item'          => __( 'Edit Connection', 'mc-woo-remote-automations' ),
					'view_item'          => __( 'View Connection', 'mc-woo-remote-automations' ),
					'all_items'          => __( 'Connections', 'mc-woo-remote-automations' ),
					'search_items'       => __( 'Search Connections', 'mc-woo-remote-automations' ),
					'not_found'          => __( 'No connections found', 'mc-woo-remote-automations' ),
					'not_found_in_trash' => __( 'No connections found in trash', 'mc-woo-remote-automations' ),
				),
				'public'      => false,
				'show_ui'     => true,
				'show_in_menu' => false,
				'supports'    => array( 'title' ),
			)
		);

		register_post_type(
			'mcwra_automation',
			array(
				'labels'      => array(
					'name'               => __( 'Automations', 'mc-woo-remote-automations' ),
					'singular_name'      => __( 'Automation', 'mc-woo-remote-automations' ),
					'menu_name'          => __( 'Automations', 'mc-woo-remote-automations' ),
					'name_admin_bar'     => __( 'Automation', 'mc-woo-remote-automations' ),
					'add_new'            => __( 'Add Automation', 'mc-woo-remote-automations' ),
					'add_new_item'       => __( 'Add Automation', 'mc-woo-remote-automations' ),
					'new_item'           => __( 'New Automation', 'mc-woo-remote-automations' ),
					'edit_item'          => __( 'Edit Automation', 'mc-woo-remote-automations' ),
					'view_item'          => __( 'View Automation', 'mc-woo-remote-automations' ),
					'all_items'          => __( 'Automations', 'mc-woo-remote-automations' ),
					'search_items'       => __( 'Search Automations', 'mc-woo-remote-automations' ),
					'not_found'          => __( 'No automations found', 'mc-woo-remote-automations' ),
					'not_found_in_trash' => __( 'No automations found in trash', 'mc-woo-remote-automations' ),
				),
				'public'      => false,
				'show_ui'     => true,
				'show_in_menu' => false,
				'supports'    => array( 'title' ),
			)
		);
	}

	/**
	 * Fires on WooCommerce order status transition and runs matching automations.
	 *
	 * @param int      $order_id   Order post ID.
	 * @param string   $old_status Previous order status slug (without wc- prefix).
	 * @param string   $new_status New order status slug (without wc- prefix).
	 * @param WC_Order $order      WooCommerce order object.
	 */
	public function handle_order_status_change( $order_id, $old_status, $new_status, $order ) {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}
		if ( ! $order ) {
			return;
		}

		$automations = get_posts(
			array(
				'post_type'      => 'mcwra_automation',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => '_mc_enabled',
						'value' => 'yes',
					),
				),
			)
		);

		$order_product_ids = mc_wra_get_order_product_ids( $order );
		$email             = sanitize_email( $order->get_billing_email() );
		$first             = sanitize_text_field( $order->get_billing_first_name() );
		$last              = sanitize_text_field( $order->get_billing_last_name() );

		foreach ( $automations as $automation ) {
			$trigger_status = get_post_meta( $automation->ID, '_mc_order_status', true );
			if ( $trigger_status !== $new_status ) {
				continue;
			}

			$product_ids = get_post_meta( $automation->ID, '_mc_product_ids', true );
			if ( ! is_array( $product_ids ) || empty( $product_ids ) ) {
				continue;
			}
			if ( empty( array_intersect( array_map( 'intval', $product_ids ), $order_product_ids ) ) ) {
				continue;
			}

			$connection_id = intval( get_post_meta( $automation->ID, '_mc_connection_id', true ) );
			if ( ! $connection_id ) {
				continue;
			}
			if ( 'yes' !== get_post_meta( $connection_id, '_mc_enabled', true ) ) {
				continue;
			}

			$base_url        = get_post_meta( $connection_id, '_mc_base_url', true );
			$create_endpoint = get_post_meta( $connection_id, '_mc_create_endpoint', true ) ?: '/wp-json/mc/v1/create-user';
			$role_endpoint   = get_post_meta( $connection_id, '_mc_role_endpoint', true ) ?: '/wp-json/mc/v1/assign-role';
			$create_secret   = get_post_meta( $connection_id, '_mc_create_secret', true );
			$role_secret     = get_post_meta( $connection_id, '_mc_role_secret', true );

			$timeout = intval( get_post_meta( $automation->ID, '_mc_timeout', true ) );
			if ( ! $timeout ) {
				$timeout = intval( get_option( 'mc_wra_default_timeout', 10 ) );
			}
			if ( ! $timeout ) {
				$timeout = 10;
			}

			if ( ! $email ) {
				continue;
			}

			if ( 'yes' === get_post_meta( $automation->ID, '_mc_create_if_missing', true ) ) {
				$request  = array(
					'user_email' => $email,
					'first_name' => $first,
					'last_name'  => $last,
					'user_pass'  => $email,
					'role'       => 'customer',
				);
				$response = wp_remote_post(
					mc_wra_build_url( $base_url, $create_endpoint ),
					array(
						'timeout' => $timeout,
						'headers' => array(
							'Content-Type' => 'application/json; charset=utf-8',
							'Accept'       => 'application/json',
							'X-MC-SECRET'  => $create_secret,
						),
						'body'    => wp_json_encode( $request ),
					)
				);
				MC_Woo_Remote_Helpers::handle_response_log( $automation->ID, $connection_id, $order_id, 'create_user', $email, $request, $response, true );
			}

			$remote_role = get_post_meta( $automation->ID, '_mc_remote_role', true );
			if ( 'yes' === get_post_meta( $automation->ID, '_mc_assign_role', true ) && $remote_role ) {
				$request  = array(
					'email' => $email,
					'role'  => $remote_role,
				);
				$response = wp_remote_post(
					mc_wra_build_url( $base_url, $role_endpoint ),
					array(
						'timeout' => $timeout,
						'headers' => array(
							'Content-Type' => 'application/json; charset=utf-8',
							'Accept'       => 'application/json',
							'X-MC-SECRET'  => $role_secret ? $role_secret : $create_secret,
						),
						'body'    => wp_json_encode( $request ),
					)
				);
				MC_Woo_Remote_Helpers::handle_response_log( $automation->ID, $connection_id, $order_id, 'assign_role', $email, $request, $response, false );
			}
		}
	}
}
