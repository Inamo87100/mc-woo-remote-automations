<?php
/**
 * Tests for MC-Woo Remote Automations automation logic.
 *
 * @package MC_Woo_Remote_Automations
 */

/**
 * Tests for automation trigger and log helper logic.
 */
class Test_MC_Woo_Remote_Automation extends WP_UnitTestCase {

	/**
	 * Test that MC_Woo_Remote_Helpers::LOG_TABLE constant is set.
	 */
	public function test_log_table_constant() {
		$this->assertSame( 'mc_wra_logs', MC_Woo_Remote_Helpers::LOG_TABLE );
	}

	/**
	 * Test that mc_wra_build_url correctly combines base URL and endpoint.
	 */
	public function test_build_url_combines_correctly() {
		$url = mc_wra_build_url( 'https://example.com', '/wp-json/mc/v1/create-user' );
		$this->assertSame( 'https://example.com/wp-json/mc/v1/create-user', $url );
	}

	/**
	 * Test that mc_wra_build_url removes double slashes at join point.
	 */
	public function test_build_url_removes_double_slash() {
		$url = mc_wra_build_url( 'https://example.com/', '/wp-json/mc/v1/ping' );
		$this->assertSame( 'https://example.com/wp-json/mc/v1/ping', $url );
	}

	/**
	 * Test that mc_wra_build_url works when base URL has no trailing slash.
	 */
	public function test_build_url_no_trailing_slash() {
		$url = mc_wra_build_url( 'https://example.com', 'wp-json/mc/v1/assign-role' );
		$this->assertStringContainsString( 'example.com', $url );
		$this->assertStringContainsString( 'assign-role', $url );
	}

	/**
	 * Test that handle_response_log logs a WP_Error as 'failed'.
	 */
	public function test_handle_response_log_wp_error() {
		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		// Ensure the table exists (created on activation).
		$wpdb->query( "CREATE TABLE IF NOT EXISTS {$table} (
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
		)" );

		$wp_error = new WP_Error( 'http_request_failed', 'cURL error: connection refused' );

		MC_Woo_Remote_Helpers::handle_response_log(
			1,    // automation_id
			2,    // connection_id
			3,    // order_id
			'create_user',
			'test@example.com',
			array( 'user_email' => 'test@example.com' ),
			$wp_error,
			true
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				3
			)
		);

		$this->assertNotNull( $last_log );
		$this->assertSame( 'failed', $last_log->status );
		$this->assertSame( 'create_user', $last_log->action_key );
	}

	/**
	 * Test that handle_response_log logs a 200 response as 'success'.
	 */
	public function test_handle_response_log_success() {
		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		$mock_response = array(
			'response' => array( 'code' => 200 ),
			'body'     => '{"success":true,"code":"user_created","user_id":99}',
		);

		MC_Woo_Remote_Helpers::handle_response_log(
			10,
			20,
			30,
			'create_user',
			'success@example.com',
			array( 'user_email' => 'success@example.com' ),
			$mock_response,
			false
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				30
			)
		);

		$this->assertNotNull( $last_log );
		$this->assertSame( 'success', $last_log->status );
		$this->assertSame( 200, (int) $last_log->response_code );
	}

	/**
	 * Test that handle_response_log treats 'user_exists' in body as success when allow_exists = true.
	 */
	public function test_handle_response_log_user_exists_is_success() {
		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		$mock_response = array(
			'response' => array( 'code' => 200 ),
			'body'     => '{"success":true,"code":"user_exists","user_id":5}',
		);

		MC_Woo_Remote_Helpers::handle_response_log(
			11,
			21,
			31,
			'create_user',
			'exists@example.com',
			array( 'user_email' => 'exists@example.com' ),
			$mock_response,
			true
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				31
			)
		);

		$this->assertNotNull( $last_log );
		$this->assertSame( 'success', $last_log->status );
	}

	/**
	 * Test that handle_response_log logs a 500 response as 'failed'.
	 */
	public function test_handle_response_log_server_error() {
		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		$mock_response = array(
			'response' => array( 'code' => 500 ),
			'body'     => '{"error":"Internal Server Error"}',
		);

		MC_Woo_Remote_Helpers::handle_response_log(
			12,
			22,
			32,
			'assign_role',
			'error@example.com',
			array( 'email' => 'error@example.com', 'role' => 'editor' ),
			$mock_response,
			false
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				32
			)
		);

		$this->assertNotNull( $last_log );
		$this->assertSame( 'failed', $last_log->status );
	}
}
