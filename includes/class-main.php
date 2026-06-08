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
		add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_personal_data_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_personal_data_eraser' ) );
		new MC_Woo_Remote_Admin();
		$mode = get_option( 'mc_wra_operating_mode', 'both' );

		if ( in_array( $mode, array( 'controller', 'both' ), true ) ) {
			add_action( 'woocommerce_order_status_changed', array( $this, 'handle_order_status_change' ), 10, 4 );
		}

		if ( in_array( $mode, array( 'remote', 'both' ), true ) ) {
			new MC_Woo_Remote_API();
		}
	}

	/**
	 * Registers suggested privacy-policy content with the WordPress Privacy Policy Guide.
	 *
	 * Site administrators can copy this text into their privacy policy page.
	 */
	public function add_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content  = '<p>' . __( 'When a WooCommerce order reaches a configured status trigger, this plugin may transmit certain customer data to a remote WordPress site. The destination URL is entered by the site administrator in a Connection record — it is always a WordPress site the administrator owns and controls. No data is ever sent to the plugin developer or any third-party server.', 'mc-woo-remote-automations' ) . '</p>';
		$content .= '<p><strong>' . __( 'Data that may be transmitted to the remote site:', 'mc-woo-remote-automations' ) . '</strong></p>';
		$content .= '<ul>';
		$content .= '<li>' . __( 'Customer billing e-mail address', 'mc-woo-remote-automations' ) . '</li>';
		$content .= '<li>' . __( 'Customer billing first name and last name', 'mc-woo-remote-automations' ) . '</li>';
		$content .= '<li>' . __( 'A WordPress role slug chosen by the administrator (for role-assignment actions)', 'mc-woo-remote-automations' ) . '</li>';
		$content .= '</ul>';
		$content .= '<p><strong>' . __( 'When data is transmitted:', 'mc-woo-remote-automations' ) . '</strong> ' . __( 'Only when an order transitions to the status configured in an active Automation rule and the order contains one of the configured products.', 'mc-woo-remote-automations' ) . '</p>';
		$content .= '<p><strong>' . __( 'Local logging:', 'mc-woo-remote-automations' ) . '</strong> ' . __( 'Each API call writes a log record to a local database table. The record includes the customer e-mail address, the HTTP response code, and a redacted preview of the request/response payload. Logs can be reviewed and purged at any time from Woo Remote Automations → Logs.', 'mc-woo-remote-automations' ) . '</p>';
		$content .= '<p>' . __( 'Log entries tied to an e-mail address can be exported and erased through WordPress privacy tools under Tools → Export Personal Data and Tools → Erase Personal Data.', 'mc-woo-remote-automations' ) . '</p>';
		$content .= '<p>' . __( 'As the site administrator you are responsible for documenting this data transfer in your own privacy policy as required by applicable law (e.g. GDPR).', 'mc-woo-remote-automations' ) . '</p>';

		wp_add_privacy_policy_content(
			__( 'MC-Woo Remote Automations', 'mc-woo-remote-automations' ),
			wp_kses_post( $content )
		);
	}

	/**
	 * Registers this plugin's personal-data exporter with WordPress privacy tools.
	 *
	 * @param array<string, array<string, mixed>> $exporters Registered exporters.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_personal_data_exporter( $exporters ) {
		$exporters['mc-wra-logs'] = array(
			'exporter_friendly_name' => __( 'MC-Woo Remote Automations Logs', 'mc-woo-remote-automations' ),
			'callback'               => array( $this, 'export_personal_data' ),
		);

		return $exporters;
	}

	/**
	 * Registers this plugin's personal-data eraser with WordPress privacy tools.
	 *
	 * @param array<string, array<string, mixed>> $erasers Registered erasers.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_personal_data_eraser( $erasers ) {
		$erasers['mc-wra-logs'] = array(
			'eraser_friendly_name' => __( 'MC-Woo Remote Automations Logs', 'mc-woo-remote-automations' ),
			'callback'             => array( $this, 'erase_personal_data' ),
		);

		return $erasers;
	}

	/**
	 * Exports plugin log records associated with a matching e-mail address.
	 *
	 * @param string $email_address User e-mail address submitted to the exporter tool.
	 * @param int    $page          Export page number.
	 * @return array<string, mixed>
	 */
	public function export_personal_data( $email_address, $page = 1 ) {
		global $wpdb;

		$email_address = sanitize_email( $email_address );
		$page          = max( 1, absint( $page ) );
		$number        = 50;
		$offset        = ( $page - 1 ) * $number;
		$data          = array();
		$table         = MC_Woo_Remote_Helpers::get_log_table_name();
		$expected_table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		if ( '' === $table || '' === $email_address || $table !== $expected_table ) {
			return array(
				'data' => $data,
				'done' => true,
			);
		}

		$query = $wpdb->prepare(
			"SELECT id, created_at, action_key, status, response_code, message, order_id, request_payload, response_body FROM {$table} WHERE user_email = %s ORDER BY id ASC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$email_address,
			$number,
			$offset
		);
		$rows  = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $rows as $row ) {
			$item_data = array(
				array(
					'name'  => __( 'Log date', 'mc-woo-remote-automations' ),
					'value' => (string) $row['created_at'],
				),
				array(
					'name'  => __( 'Customer e-mail', 'mc-woo-remote-automations' ),
					'value' => $email_address,
				),
				array(
					'name'  => __( 'Action', 'mc-woo-remote-automations' ),
					'value' => $this->get_log_action_label( (string) $row['action_key'] ),
				),
				array(
					'name'  => __( 'Execution status', 'mc-woo-remote-automations' ),
					'value' => (string) $row['status'],
				),
				array(
					'name'  => __( 'HTTP response code', 'mc-woo-remote-automations' ),
					'value' => is_null( $row['response_code'] ) ? '' : (string) $row['response_code'],
				),
				array(
					'name'  => __( 'Result message', 'mc-woo-remote-automations' ),
					'value' => (string) $row['message'],
				),
			);

			if ( ! empty( $row['order_id'] ) ) {
				$item_data[] = array(
					'name'  => __( 'Order ID', 'mc-woo-remote-automations' ),
					'value' => (string) intval( $row['order_id'] ),
				);
			}
			if ( ! empty( $row['request_payload'] ) ) {
				$item_data[] = array(
					'name'  => __( 'Request payload summary', 'mc-woo-remote-automations' ),
					'value' => (string) $row['request_payload'],
				);
			}
			if ( ! empty( $row['response_body'] ) ) {
				$item_data[] = array(
					'name'  => __( 'Response body summary', 'mc-woo-remote-automations' ),
					'value' => (string) $row['response_body'],
				);
			}

			$data[] = array(
				'group_id'    => 'mc-wra-logs',
				'group_label' => __( 'MC-Woo Remote Automations Logs', 'mc-woo-remote-automations' ),
				'item_id'     => 'mc-wra-log-' . intval( $row['id'] ),
				'data'        => $item_data,
			);
		}

		return array(
			'data' => $data,
			'done' => count( $rows ) < $number,
		);
	}

	/**
	 * Anonymizes plugin log records associated with a matching e-mail address.
	 *
	 * We intentionally anonymize instead of deleting rows so administrators keep
	 * operational audit signals (timestamps, action type, status, and response code)
	 * while personally identifiable fields are removed.
	 *
	 * @param string $email_address User e-mail address submitted to the eraser tool.
	 * @param int    $page          Eraser page number.
	 * @return array<string, mixed>
	 */
	public function erase_personal_data( $email_address, $page = 1 ) {
		global $wpdb;

		$email_address  = sanitize_email( $email_address );
		$number         = 50;
		$items_removed  = false;
		$items_retained = false;
		$messages       = array();
		$table          = MC_Woo_Remote_Helpers::get_log_table_name();
		$expected_table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		if ( '' === $table || '' === $email_address || $table !== $expected_table ) {
			return array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => $messages,
				'done'           => true,
			);
		}

		$query = $wpdb->prepare(
			"SELECT id, message FROM {$table} WHERE user_email = %s ORDER BY id ASC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$email_address,
			$number
		);
		$rows  = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $rows as $row ) {
			$updated = $wpdb->update(
				$table,
				array(
					'user_email'      => '',
					'request_payload' => '{}',
					'response_body'   => '',
					'message'         => $this->redact_email_addresses( (string) $row['message'] ),
				),
				array( 'id' => intval( $row['id'] ) ),
				array( '%s', '%s', '%s', '%s' ),
				array( '%d' )
			);

			if ( false === $updated ) {
				$items_retained = true;
				$messages[]     = sprintf(
					/* translators: %d: database log ID. */
					__( 'A log record could not be anonymized (ID %d).', 'mc-woo-remote-automations' ),
					intval( $row['id'] )
				);
				continue;
			}

			$items_removed = true;
		}

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => count( $rows ) < $number,
		);
	}

	/**
	 * Returns a friendly action label for exported log entries.
	 *
	 * @param string $action_key Action key stored in the log table.
	 * @return string
	 */
	private function get_log_action_label( $action_key ) {
		if ( 'create_user' === $action_key ) {
			return __( 'Create user on remote site', 'mc-woo-remote-automations' );
		}
		if ( 'assign_role' === $action_key ) {
			return __( 'Assign role on remote site', 'mc-woo-remote-automations' );
		}

		return $action_key;
	}

	/**
	 * Redacts e-mail addresses from a free-text message.
	 *
	 * @param string $message Source log message.
	 * @return string
	 */
	private function redact_email_addresses( $message ) {
		return (string) preg_replace(
			'/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',
			'[redacted-email]',
			$message
		);
	}

	/**
	 * Plugin activation callback – creates the log database table.
	 */
	public function activate() {
		if ( ! get_option( 'mc_wra_api_secret' ) ) {
			update_option( 'mc_wra_api_secret', wp_generate_password( 32, true, true ) );
		}
		if ( ! get_option( 'mc_wra_operating_mode' ) ) {
			update_option( 'mc_wra_operating_mode', 'both' );
		}

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
			PRIMARY KEY (id),
			KEY user_email (user_email)
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
				'map_meta_cap' => true,
				'capability_type' => array( 'mcwra_connection', 'mcwra_connections' ),
				'capabilities' => array(
					'edit_post'              => 'manage_options',
					'read_post'              => 'manage_options',
					'delete_post'            => 'manage_options',
					'edit_posts'             => 'manage_options',
					'edit_others_posts'      => 'manage_options',
					'publish_posts'          => 'manage_options',
					'read_private_posts'     => 'manage_options',
					'delete_posts'           => 'manage_options',
					'delete_private_posts'   => 'manage_options',
					'delete_published_posts' => 'manage_options',
					'delete_others_posts'    => 'manage_options',
					'edit_private_posts'     => 'manage_options',
					'edit_published_posts'   => 'manage_options',
					'create_posts'           => 'manage_options',
				),
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
				'map_meta_cap' => true,
				'capability_type' => array( 'mcwra_automation', 'mcwra_automations' ),
				'capabilities' => array(
					'edit_post'              => 'manage_options',
					'read_post'              => 'manage_options',
					'delete_post'            => 'manage_options',
					'edit_posts'             => 'manage_options',
					'edit_others_posts'      => 'manage_options',
					'publish_posts'          => 'manage_options',
					'read_private_posts'     => 'manage_options',
					'delete_posts'           => 'manage_options',
					'delete_private_posts'   => 'manage_options',
					'delete_published_posts' => 'manage_options',
					'delete_others_posts'    => 'manage_options',
					'edit_private_posts'     => 'manage_options',
					'edit_published_posts'   => 'manage_options',
					'create_posts'           => 'manage_options',
				),
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
				'post_status'    => 'publish',
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
			$url_validation  = mc_wra_validate_remote_base_url( $base_url );

			if ( is_wp_error( $url_validation ) ) {
				continue;
			}

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
