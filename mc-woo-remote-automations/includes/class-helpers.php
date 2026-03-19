<?php
/**
 * HTTP request and logging helpers for MC-Woo Remote Automations.
 *
 * @package MC_Woo_Remote_Automations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the HTTP response from a remote action and writes a log entry.
 */
class MC_Woo_Remote_Helpers {

	/** @var string Database table name (without prefix). */
	const LOG_TABLE = 'mc_wra_logs';

	/**
	 * Interprets a remote HTTP response and persists a log record.
	 *
	 * @param int          $automation_id Automation post ID.
	 * @param int          $connection_id Connection post ID.
	 * @param int          $order_id      WooCommerce order ID.
	 * @param string       $action_key    Action identifier ('create_user' or 'assign_role').
	 * @param string       $email         Customer email address.
	 * @param array        $request       Request payload array.
	 * @param array|WP_Error $response    wp_remote_* response or WP_Error.
	 * @param bool         $allow_exists  When true, an "already exists" response is treated as success.
	 */
	public static function handle_response_log(
		$automation_id,
		$connection_id,
		$order_id,
		$action_key,
		$email,
		$request,
		$response,
		$allow_exists
	) {
		if ( is_wp_error( $response ) ) {
			self::log_action(
				$automation_id,
				$connection_id,
				$order_id,
				'failed',
				$action_key,
				$email,
				null,
				$response->get_error_message(),
				$request,
				''
			);
			return;
		}

		$code    = (int) wp_remote_retrieve_response_code( $response );
		$body    = (string) wp_remote_retrieve_body( $response );
		$status  = ( $code >= 200 && $code < 300 ) ? 'success' : 'failed';
		$message = 'HTTP ' . $code;

		if ( $allow_exists ) {
			$body_lc = strtolower( $body );
			if (
				false !== strpos( $body_lc, 'user_exists' ) ||
				false !== strpos( $body_lc, 'already exists' ) ||
				409 === $code
			) {
				$status  = 'success';
				$message = 'Existing user accepted';
			}
		}

		self::log_action( $automation_id, $connection_id, $order_id, $status, $action_key, $email, $code, $message, $request, $body );
	}

	/**
	 * Inserts a log record into the database.
	 *
	 * @param int         $automation_id  Automation post ID.
	 * @param int         $connection_id  Connection post ID.
	 * @param int         $order_id       WooCommerce order ID.
	 * @param string      $status         'success' or 'failed'.
	 * @param string      $action_key     Action identifier.
	 * @param string      $email          Customer email address.
	 * @param int|null    $response_code  HTTP response code or null on WP_Error.
	 * @param string      $message        Human-readable result message.
	 * @param array       $request_payload Request body sent.
	 * @param string      $response_body  Raw response body.
	 */
	public static function log_action(
		$automation_id,
		$connection_id,
		$order_id,
		$status,
		$action_key,
		$email,
		$response_code,
		$message,
		$request_payload,
		$response_body
	) {
		global $wpdb;
		$table = $wpdb->prefix . self::LOG_TABLE;
		$wpdb->insert(
			$table,
			array(
				'created_at'       => current_time( 'mysql' ),
				'automation_id'    => intval( $automation_id ),
				'connection_id'    => intval( $connection_id ),
				'order_id'         => intval( $order_id ),
				'action_key'       => sanitize_text_field( $action_key ),
				'user_email'       => sanitize_email( $email ),
				'status'           => sanitize_text_field( $status ),
				'response_code'    => is_null( $response_code ) ? null : intval( $response_code ),
				'message'          => wp_kses_post( (string) $message ),
				'request_payload'  => wp_json_encode( $request_payload ),
				'response_body'    => (string) $response_body,
			),
			array( '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
