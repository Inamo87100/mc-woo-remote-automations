<?php
/**
 * Fixture loader utility for MC plugin test suites.
 *
 * Provides static helpers to load JSON fixture data and populate
 * WordPress or WooCommerce objects for use in unit and integration tests.
 *
 * @package MC_Tests
 */

/**
 * Loads and inserts test fixtures into WordPress.
 */
class Fixture_Loader {

	/**
	 * Load users from a JSON fixture file and insert them into WordPress.
	 *
	 * Each entry in the JSON array should contain:
	 *   - user_email (string, required)
	 *   - first_name (string, optional)
	 *   - last_name  (string, optional)
	 *   - role       (string, optional – defaults to 'subscriber')
	 *
	 * @param string $fixture_file Absolute path to the JSON fixture file.
	 * @return int[] Array of inserted user IDs.
	 */
	public static function load_users( $fixture_file ) {
		$fixtures = self::read_json( $fixture_file );
		$ids      = array();

		foreach ( $fixtures as $user_data ) {
			$email = isset( $user_data['user_email'] ) ? $user_data['user_email'] : '';
			if ( ! is_email( $email ) ) {
				continue;
			}

			// Skip if already exists.
			if ( get_user_by( 'email', $email ) ) {
				continue;
			}

			$role = isset( $user_data['role'] ) ? $user_data['role'] : 'subscriber';

			$user_id = wp_insert_user(
				array(
					'user_email' => sanitize_email( $email ),
					'user_login' => sanitize_user( $email, true ),
					'first_name' => isset( $user_data['first_name'] ) ? sanitize_text_field( $user_data['first_name'] ) : '',
					'last_name'  => isset( $user_data['last_name'] ) ? sanitize_text_field( $user_data['last_name'] ) : '',
					'role'       => sanitize_key( $role ),
					'user_pass'  => wp_generate_password(),
				)
			);

			if ( ! is_wp_error( $user_id ) ) {
				$ids[] = $user_id;
			}
		}

		return $ids;
	}

	/**
	 * Load automations from a JSON fixture file.
	 *
	 * Returns the decoded array for use in tests; does not persist to the
	 * database (automation storage varies by plugin version).
	 *
	 * @param string $fixture_file Absolute path to the JSON fixture file.
	 * @return array[] Array of automation data arrays.
	 */
	public static function load_automations( $fixture_file ) {
		return self::read_json( $fixture_file );
	}

	/**
	 * Load connections from a JSON fixture file.
	 *
	 * Returns the decoded array for use in tests.
	 *
	 * @param string $fixture_file Absolute path to the JSON fixture file.
	 * @return array[] Array of connection data arrays.
	 */
	public static function load_connections( $fixture_file ) {
		return self::read_json( $fixture_file );
	}

	/**
	 * Load orders from a JSON fixture file.
	 *
	 * Returns the decoded array for use in tests.
	 *
	 * @param string $fixture_file Absolute path to the JSON fixture file.
	 * @return array[] Array of order data arrays.
	 */
	public static function load_orders( $fixture_file ) {
		return self::read_json( $fixture_file );
	}

	/**
	 * Load API responses from a JSON fixture file.
	 *
	 * Returns the decoded associative array for assertion use in tests.
	 *
	 * @param string $fixture_file Absolute path to the JSON fixture file.
	 * @return array Associative array of expected API responses.
	 */
	public static function load_api_responses( $fixture_file ) {
		return self::read_json( $fixture_file );
	}

	/**
	 * Read and decode a JSON fixture file.
	 *
	 * @param string $fixture_file Absolute path to the JSON fixture file.
	 * @return array Decoded JSON data, or empty array on failure.
	 */
	private static function read_json( $fixture_file ) {
		if ( ! file_exists( $fixture_file ) ) {
			return array();
		}

		$contents = file_get_contents( $fixture_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$decoded  = json_decode( $contents, true );

		return is_array( $decoded ) ? $decoded : array();
	}
}
