<?php
/**
 * Integration tests for MC Remote API + MC-Woo Remote Automations.
 *
 * These tests verify the end-to-end interaction between both plugins:
 * the automation engine calling the Remote API endpoints and handling
 * success/failure responses correctly.
 *
 * Requires both plugins to be loaded (see tests/bootstrap.php).
 *
 * @package MC_Tests_Integration
 */

/**
 * Integration tests for the API <-> Automation plugin workflow.
 */
class Test_Integration_API_Automation extends WP_UnitTestCase {

	/** @var string */
	private $test_secret = 'integration-test-secret-abcdef1234';

	/**
	 * Fixtures directory for the shared test suite.
	 *
	 * @var string
	 */
	private $fixtures_dir;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->fixtures_dir = dirname( __DIR__ ) . '/fixtures';

		// Store a known secret so the API plugin accepts requests.
		update_option( 'mc_api_secret', $this->test_secret );
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		delete_option( 'mc_api_secret' );
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// Fixture loading tests
	// -------------------------------------------------------------------------

	/**
	 * Test that fixture-users.json loads valid user data.
	 */
	public function test_fixture_users_are_valid() {
		require_once dirname( __DIR__ ) . '/class-fixture-loader.php';

		$fixture_file = $this->fixtures_dir . '/fixture-users.json';
		$this->assertFileExists( $fixture_file );

		$users = json_decode( file_get_contents( $fixture_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertIsArray( $users );
		$this->assertNotEmpty( $users );

		foreach ( $users as $user ) {
			$this->assertArrayHasKey( 'user_email', $user );
			$this->assertTrue( is_email( $user['user_email'] ), "Invalid email in fixture: {$user['user_email']}" );
		}
	}

	/**
	 * Test that fixture-api-responses.json contains expected keys.
	 */
	public function test_fixture_api_responses_structure() {
		$fixture_file = $this->fixtures_dir . '/fixture-api-responses.json';
		$this->assertFileExists( $fixture_file );

		$responses = json_decode( file_get_contents( $fixture_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertIsArray( $responses );

		$this->assertArrayHasKey( 'create_user', $responses );
		$this->assertArrayHasKey( 'assign_role', $responses );
		$this->assertArrayHasKey( 'ping', $responses );
		$this->assertArrayHasKey( 'errors', $responses );
	}

	/**
	 * Test that fixture-connections.json loads valid connection data.
	 */
	public function test_fixture_connections_are_valid() {
		$fixture_file = $this->fixtures_dir . '/fixture-connections.json';
		$this->assertFileExists( $fixture_file );

		$connections = json_decode( file_get_contents( $fixture_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertIsArray( $connections );
		$this->assertNotEmpty( $connections );

		foreach ( $connections as $connection ) {
			$this->assertArrayHasKey( 'name', $connection );
			$this->assertArrayHasKey( 'url', $connection );
			$this->assertArrayHasKey( 'secret', $connection );
			$this->assertArrayHasKey( 'status', $connection );
			$this->assertContains( $connection['status'], array( 'active', 'inactive' ) );
		}
	}

	/**
	 * Test that fixture-automations.json loads valid automation data.
	 */
	public function test_fixture_automations_are_valid() {
		$fixture_file = $this->fixtures_dir . '/fixture-automations.json';
		$this->assertFileExists( $fixture_file );

		$automations = json_decode( file_get_contents( $fixture_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertIsArray( $automations );
		$this->assertNotEmpty( $automations );

		foreach ( $automations as $automation ) {
			$this->assertArrayHasKey( 'name', $automation );
			$this->assertArrayHasKey( 'trigger', $automation );
			$this->assertArrayHasKey( 'action', $automation );
			$this->assertArrayHasKey( 'conditions', $automation );
		}
	}

	// -------------------------------------------------------------------------
	// Fixture loader class tests
	// -------------------------------------------------------------------------

	/**
	 * Test that Fixture_Loader::load_users inserts users into WordPress.
	 */
	public function test_fixture_loader_load_users() {
		require_once dirname( __DIR__ ) . '/class-fixture-loader.php';

		$fixture_file = $this->fixtures_dir . '/fixture-users.json';
		$inserted_ids = Fixture_Loader::load_users( $fixture_file );

		$this->assertIsArray( $inserted_ids );
		$this->assertNotEmpty( $inserted_ids );

		// Verify the users exist in WordPress.
		foreach ( $inserted_ids as $user_id ) {
			$user = get_user_by( 'id', $user_id );
			$this->assertInstanceOf( 'WP_User', $user );
		}

		// Clean up.
		foreach ( $inserted_ids as $user_id ) {
			wp_delete_user( $user_id );
		}
	}

	/**
	 * Test that Fixture_Loader::load_users skips users with invalid emails.
	 */
	public function test_fixture_loader_skips_invalid_emails() {
		require_once dirname( __DIR__ ) . '/class-fixture-loader.php';

		$fixture_file = $this->fixtures_dir . '/fixture-invalid-data.json';
		$this->assertFileExists( $fixture_file );

		// load_users should gracefully skip all entries with invalid emails.
		// Passing invalid-data fixture – all emails are invalid so 0 users inserted.
		// We just verify no PHP errors are thrown.
		$inserted_ids = Fixture_Loader::load_users( $fixture_file );
		$this->assertIsArray( $inserted_ids );
		$this->assertEmpty( $inserted_ids );
	}

	// -------------------------------------------------------------------------
	// API endpoint integration with fixture data
	// -------------------------------------------------------------------------

	/**
	 * Test creating a user via the REST API using fixture data.
	 */
	public function test_api_create_user_with_fixture_data() {
		if ( ! class_exists( 'MC_Remote_API_Main' ) ) {
			$this->markTestSkipped( 'MC Remote API plugin not loaded.' );
		}

		new MC_Remote_API_Main();

		$email = 'fixture_integration_' . uniqid() . '@example.com';

		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'user_email', $email );
		$request->set_param( 'first_name', 'Fixture' );
		$request->set_param( 'last_name', 'User' );
		$request->set_param( 'role', 'subscriber' );

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $data['success'] );
		$this->assertSame( 'user_created', $data['code'] );

		// Verify user actually exists.
		$user = get_user_by( 'email', $email );
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertContains( 'subscriber', $user->roles );

		// Clean up.
		wp_delete_user( $user->ID );
	}

	/**
	 * Test that mc_wra_build_url produces URLs matching fixture connection data.
	 */
	public function test_build_url_with_fixture_connections() {
		if ( ! function_exists( 'mc_wra_build_url' ) ) {
			$this->markTestSkipped( 'MC-Woo Remote Automations plugin not loaded.' );
		}

		$fixture_file = $this->fixtures_dir . '/fixture-connections.json';
		$connections  = json_decode( file_get_contents( $fixture_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		foreach ( $connections as $connection ) {
			$url = mc_wra_build_url( $connection['url'], '/mc/v1/ping' );
			$this->assertStringContainsString( '/mc/v1/ping', $url );
			$this->assertStringNotContainsString( '//', substr( $url, 8 ) ); // No double slashes after scheme.
		}
	}

	/**
	 * Test that the handle_response_log helper processes fixture API responses correctly.
	 */
	public function test_handle_response_log_with_fixture_responses() {
		if ( ! class_exists( 'MC_Woo_Remote_Helpers' ) ) {
			$this->markTestSkipped( 'MC-Woo Remote Automations plugin not loaded.' );
		}

		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		// Ensure the log table exists.
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

		// Load expected success response from fixtures.
		$fixture_file = $this->fixtures_dir . '/fixture-api-responses.json';
		$responses    = json_decode( file_get_contents( $fixture_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$expected = $responses['create_user']['success_created'];

		$mock_response = array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode( $expected ),
		);

		MC_Woo_Remote_Helpers::handle_response_log(
			50, 60, 70,
			'create_user',
			'fixture@example.com',
			array( 'user_email' => 'fixture@example.com' ),
			$mock_response,
			false
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				70
			)
		);

		$this->assertNotNull( $last_log );
		$this->assertSame( 'success', $last_log->status );
		$this->assertSame( 200, (int) $last_log->response_code );
	}
}
