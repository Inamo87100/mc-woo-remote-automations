<?php
/**
 * Unit tests for MC Remote API endpoints.
 *
 * @package MC_Remote_API
 */

/**
 * Tests for the MC Remote API REST endpoints.
 */
class Test_MC_Remote_API_Endpoints extends WP_Test_REST_TestCase {

	/** @var MC_Remote_API_Main */
	private $plugin;

	/** @var string */
	private $test_secret = 'test-secret-1234567890abcdefghijkl';

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		update_option( 'mc_api_secret', $this->test_secret );
		$this->plugin = new MC_Remote_API_Main();
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		delete_option( 'mc_api_secret' );
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// /ping tests
	// -------------------------------------------------------------------------

	/**
	 * Test that /ping returns success with a valid secret.
	 */
	public function test_ping_with_valid_secret() {
		$request = new WP_REST_Request( 'GET', '/mc/v1/ping' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );

		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertSame( 'MC Remote API', $data['plugin'] );
	}

	/**
	 * Test that /ping returns 401 with an invalid secret.
	 */
	public function test_ping_with_invalid_secret() {
		$request = new WP_REST_Request( 'GET', '/mc/v1/ping' );
		$request->set_header( 'X-MC-SECRET', 'wrong-secret' );

		$response = rest_do_request( $request );

		$this->assertSame( 401, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	/**
	 * Test that /ping returns 401 when no secret header is provided.
	 */
	public function test_ping_without_secret() {
		$request  = new WP_REST_Request( 'GET', '/mc/v1/ping' );
		$response = rest_do_request( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	// -------------------------------------------------------------------------
	// /create-user tests
	// -------------------------------------------------------------------------

	/**
	 * Test user creation with valid data.
	 */
	public function test_create_user_success() {
		$email = 'testuser_' . uniqid() . '@example.com';

		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'user_email', $email );
		$request->set_param( 'first_name', 'Test' );
		$request->set_param( 'last_name', 'User' );
		$request->set_param( 'role', 'subscriber' );

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertTrue( $data['success'] );
		$this->assertSame( 'user_created', $data['code'] );
		$this->assertNotEmpty( $data['user_id'] );

		// Clean up.
		wp_delete_user( $data['user_id'] );
	}

	/**
	 * Test that creating a duplicate user returns user_exists.
	 */
	public function test_create_user_already_exists() {
		$email   = 'existing_' . uniqid() . '@example.com';
		$user_id = wp_create_user( $email, wp_generate_password(), $email );

		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'user_email', $email );

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertTrue( $data['success'] );
		$this->assertSame( 'user_exists', $data['code'] );

		// Clean up.
		wp_delete_user( $user_id );
	}

	/**
	 * Test that an invalid email returns HTTP 400.
	 */
	public function test_create_user_invalid_email() {
		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'user_email', 'not-an-email' );

		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	/**
	 * Test that /create-user returns 401 without a valid secret.
	 */
	public function test_create_user_unauthorized() {
		$request = new WP_REST_Request( 'POST', '/mc/v1/create-user' );
		$request->set_header( 'X-MC-SECRET', 'bad-secret' );
		$request->set_param( 'user_email', 'user@example.com' );

		$response = rest_do_request( $request );

		$this->assertSame( 401, $response->get_status() );
	}

	// -------------------------------------------------------------------------
	// /assign-role tests
	// -------------------------------------------------------------------------

	/**
	 * Test role assignment with valid data.
	 */
	public function test_assign_role_success() {
		$email   = 'role_test_' . uniqid() . '@example.com';
		$user_id = wp_create_user( $email, wp_generate_password(), $email );

		$request = new WP_REST_Request( 'POST', '/mc/v1/assign-role' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'email', $email );
		$request->set_param( 'role', 'editor' );

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		$this->assertTrue( $data['success'] );
		$this->assertSame( 'role_assigned', $data['code'] );
		$this->assertSame( 'editor', $data['role'] );

		// Verify role was actually assigned.
		$user = get_user_by( 'email', $email );
		$this->assertContains( 'editor', $user->roles );

		// Clean up.
		wp_delete_user( $user_id );
	}

	/**
	 * Test that assigning a role to a non-existent user returns 404.
	 */
	public function test_assign_role_user_not_found() {
		$request = new WP_REST_Request( 'POST', '/mc/v1/assign-role' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'email', 'nobody_' . uniqid() . '@example.com' );
		$request->set_param( 'role', 'editor' );

		$response = rest_do_request( $request );

		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * Test that an invalid role returns 400.
	 */
	public function test_assign_role_invalid_role() {
		$email   = 'invalid_role_' . uniqid() . '@example.com';
		$user_id = wp_create_user( $email, wp_generate_password(), $email );

		$request = new WP_REST_Request( 'POST', '/mc/v1/assign-role' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );
		$request->set_param( 'email', $email );
		$request->set_param( 'role', 'nonexistent_role_xyz' );

		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['success'] );

		// Clean up.
		wp_delete_user( $user_id );
	}

	/**
	 * Test that missing parameters return 400.
	 */
	public function test_assign_role_missing_params() {
		$request = new WP_REST_Request( 'POST', '/mc/v1/assign-role' );
		$request->set_header( 'X-MC-SECRET', $this->test_secret );

		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
	}
}
