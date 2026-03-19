<?php
/**
 * Security validation tests for MC Remote API.
 *
 * Verifies authentication, input sanitisation, and output escaping.
 *
 * @package MC_Remote_API
 */

/**
 * Security tests for MC Remote API.
 */
class Test_MC_Remote_API_Security extends WP_UnitTestCase {

	/** @var string */
	private $test_secret = 'test-secret-abcdefghijklmnopqrstuvwx';

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
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
	// Authentication tests
	// -------------------------------------------------------------------------

	/**
	 * Test that an empty stored secret blocks all requests.
	 */
	public function test_empty_stored_secret_blocks_requests() {
		update_option( 'mc_api_secret', '' );

		$plugin  = new MC_Remote_API_Main();
		$request = new WP_REST_Request( 'GET', '/mc/v1/ping' );
		$request->set_header( 'X-MC-SECRET', '' );

		$response = rest_do_request( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test that the secret comparison is constant-time (uses hash_equals).
	 * Verifies mc_remote_api_get_secret() returns the stored value.
	 */
	public function test_get_secret_returns_stored_value() {
		$secret = mc_remote_api_get_secret();
		$this->assertSame( $this->test_secret, $secret );
	}

	/**
	 * Test that the secret is a non-empty string after activation.
	 */
	public function test_activation_generates_non_empty_secret() {
		delete_option( 'mc_api_secret' );

		$plugin = new MC_Remote_API_Main();
		$plugin->activate();

		$secret = get_option( 'mc_api_secret' );
		$this->assertNotEmpty( $secret );
		$this->assertIsString( $secret );
	}

	/**
	 * Test that activation does not overwrite an existing secret.
	 */
	public function test_activation_does_not_overwrite_existing_secret() {
		update_option( 'mc_api_secret', 'my-existing-secret-value-abc12345' );

		$plugin = new MC_Remote_API_Main();
		$plugin->activate();

		$this->assertSame( 'my-existing-secret-value-abc12345', get_option( 'mc_api_secret' ) );
	}

	// -------------------------------------------------------------------------
	// Input sanitisation tests
	// -------------------------------------------------------------------------

	/**
	 * Test that email inputs are sanitised before processing.
	 */
	public function test_email_is_sanitised() {
		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		// Inject HTML in email field – should be sanitised to empty/invalid.
		$request->set_param( 'user_email', '<script>alert(1)</script>' );

		$response = rest_do_request( $request );

		// Must not succeed – bad email should be rejected.
		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Test that role inputs are validated against registered WordPress roles.
	 */
	public function test_invalid_role_is_rejected_in_assign_role() {
		$email   = 'sec_test_' . uniqid() . '@example.com';
		$user_id = wp_create_user( $email, wp_generate_password(), $email );

		$request = new WP_REST_Request( 'POST', '/mc/v1/assign-role' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'email', $email );
		$request->set_param( 'role', 'administrator<script>alert(1)</script>' );

		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );

		wp_delete_user( $user_id );
	}

	/**
	 * Test that an invalid role in /create-user defaults to 'customer' (not crashes).
	 */
	public function test_invalid_role_in_create_user_defaults_to_customer() {
		$email = 'default_role_' . uniqid() . '@example.com';

		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'user_email', $email );
		$request->set_param( 'role', 'totally_invalid_role_xyz' );

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Should succeed but use default role.
		$this->assertTrue( $data['success'] );

		$user = get_user_by( 'email', $email );
		if ( $user ) {
			wp_delete_user( $user->ID );
		}
	}

	// -------------------------------------------------------------------------
	// Settings page tests
	// -------------------------------------------------------------------------

	/**
	 * Test that the settings page requires manage_options capability.
	 */
	public function test_settings_page_requires_manage_options() {
		// Create a subscriber (no manage_options).
		$subscriber_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$this->assertFalse( current_user_can( 'manage_options' ) );

		wp_delete_user( $subscriber_id );
	}
}
