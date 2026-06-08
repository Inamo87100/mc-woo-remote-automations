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

	/** @var int Maximum number of characters persisted for response body previews. */
	const MAX_STORED_RESPONSE_LENGTH = 1000;

	/**
	 * Returns all known sensitive keys for payload redaction.
	 *
	 * @return string[]
	 */
	public static function get_sensitive_keys() {
		return array(
			'password',
			'pass',
			'pwd',
			'secret',
			'token',
			'api_key',
			'apikey',
			'authorization',
			'auth',
			'x-mc-secret',
			'client_secret',
			'refresh_token',
			'access_token',
		);
	}

	/**
	 * Returns validated log table name (prefixed).
	 *
	 * @return string
	 */
	public static function get_log_table_name() {
		global $wpdb;
		$table = $wpdb->prefix . self::LOG_TABLE;
		if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $table ) ) {
			return '';
		}
		return $table;
	}

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
				'request_payload'  => wp_json_encode( self::prepare_request_payload_for_storage( $request_payload ) ),
				'response_body'    => self::prepare_response_body_for_storage( $response_body ),
			),
			array( '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Keeps request payload logs useful while minimizing sensitive stored data.
	 *
	 * @param mixed $request_payload Request payload.
	 * @return array
	 */
	private static function prepare_request_payload_for_storage( $request_payload ) {
		if ( ! is_array( $request_payload ) ) {
			return array();
		}

		$summary = array();

		if ( isset( $request_payload['user_email'] ) ) {
			$summary['user_email'] = sanitize_email( $request_payload['user_email'] );
		}
		if ( isset( $request_payload['email'] ) ) {
			$summary['email'] = sanitize_email( $request_payload['email'] );
		}
		if ( isset( $request_payload['role'] ) ) {
			$summary['role'] = sanitize_key( (string) $request_payload['role'] );
		}

		if ( isset( $request_payload['first_name'] ) || isset( $request_payload['last_name'] ) ) {
			$summary['name_data'] = '[omitted]';
		}

		return self::redact_sensitive_data( $summary );
	}

	/**
	 * Persists a redacted and size-limited response preview instead of full raw bodies.
	 *
	 * @param string $response_body Raw response body.
	 * @return string
	 */
	private static function prepare_response_body_for_storage( $response_body ) {
		$response_body = (string) $response_body;
		if ( '' === $response_body ) {
			return '';
		}

		$decoded = json_decode( $response_body, true );
		if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
			$summary_keys = array( 'success', 'code', 'message', 'error', 'errors' );
			$summary      = array();
			foreach ( $summary_keys as $key ) {
				if ( array_key_exists( $key, $decoded ) ) {
					$summary[ $key ] = $decoded[ $key ];
				}
			}

			if ( empty( $summary ) ) {
				$summary = $decoded;
			}

			$json = wp_json_encode(
				self::redact_sensitive_data( $summary ),
				JSON_UNESCAPED_SLASHES
			);
			return self::truncate_for_storage( (string) $json );
		}

		$stripped = wp_strip_all_tags( $response_body );
		$redacted = self::redact_sensitive_data( (string) $stripped );

		return self::truncate_for_storage( (string) $redacted );
	}

	/**
	 * Truncates a string to a safe storage size.
	 *
	 * @param string $value Source value.
	 * @return string
	 */
	private static function truncate_for_storage( $value ) {
		$value = (string) $value;
		if ( strlen( $value ) <= self::MAX_STORED_RESPONSE_LENGTH ) {
			return $value;
		}

		return substr( $value, 0, self::MAX_STORED_RESPONSE_LENGTH ) . '…';
	}

	/**
	 * Deletes logs older than N days.
	 *
	 * @param int $days Retention in days.
	 * @return int Number of deleted rows.
	 */
	public static function delete_logs_older_than( $days ) {
		global $wpdb;
		$days = max( 0, intval( $days ) );
		if ( 0 === $days ) {
			return 0;
		}
		$table = self::get_log_table_name();
		if ( '' === $table ) {
			return 0;
		}
		$query = $wpdb->prepare(
			"DELETE FROM {$table} WHERE created_at < DATE_SUB( NOW(), INTERVAL %d DAY )",
			$days
		);
		$wpdb->query( $query );
		return intval( $wpdb->rows_affected );
	}

	/**
	 * Deletes every row from the log table.
	 *
	 * @return int Number of deleted rows.
	 */
	public static function delete_all_logs() {
		global $wpdb;
		$table = self::get_log_table_name();
		if ( '' === $table ) {
			return 0;
		}
		$wpdb->query( "DELETE FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return intval( $wpdb->rows_affected );
	}

	/**
	 * Redacts sensitive values in arrays or strings before display.
	 *
	 * @param mixed $value Array, object, or scalar value.
	 * @return mixed
	 */
	public static function redact_sensitive_data( $value ) {
		if ( is_array( $value ) ) {
			$redacted = array();
			foreach ( $value as $key => $item ) {
				$key_lc = strtolower( (string) $key );
				if ( in_array( $key_lc, self::get_sensitive_keys(), true ) ) {
					$redacted[ $key ] = '[redacted]';
					continue;
				}
				$redacted[ $key ] = self::redact_sensitive_data( $item );
			}
			return $redacted;
		}

		if ( is_object( $value ) ) {
			return self::redact_sensitive_data( (array) $value );
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		$redacted = preg_replace( '/(authorization\s*[:=]\s*)([^\s,]+)/i', '$1[redacted]', $value );
		$redacted = preg_replace( '/(bearer\s+)[a-z0-9\-_\.=]+/i', '$1[redacted]', (string) $redacted );
		$redacted = preg_replace( '/((?:api|client|access|refresh|mc)[\s_\-]*(?:key|token|secret)\s*[:=]\s*)([^\s,]+)/i', '$1[redacted]', (string) $redacted );
		return (string) $redacted;
	}
}
