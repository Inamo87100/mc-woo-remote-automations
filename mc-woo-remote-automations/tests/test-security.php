<?php
/**
 * Security validation tests for MC-Woo Remote Automations.
 *
 * Verifies input sanitisation, output escaping, and access controls.
 *
 * @package MC_Woo_Remote_Automations
 */

/**
 * Security tests for MC-Woo Remote Automations.
 */
class Test_MC_Woo_Remote_Security extends WP_UnitTestCase {

	// -------------------------------------------------------------------------
	// Input sanitisation tests
	// -------------------------------------------------------------------------

	/**
	 * Test that mc_wra_get_order_product_ids returns only integer values.
	 */
	public function test_get_order_product_ids_returns_integers() {
		// mc_wra_get_order_product_ids expects a WC_Order – skip if WooCommerce not loaded.
		if ( ! function_exists( 'wc_get_order' ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded.' );
		}

		// Create a mock order object for testing.
		$order = $this->getMockBuilder( 'WC_Order' )
		               ->disableOriginalConstructor()
		               ->getMock();

		$mock_item = $this->getMockBuilder( 'WC_Order_Item_Product' )
		                  ->disableOriginalConstructor()
		                  ->getMock();
		$mock_item->method( 'get_product_id' )->willReturn( '42' );

		$order->method( 'get_items' )->willReturn( array( $mock_item ) );

		$ids = mc_wra_get_order_product_ids( $order );

		$this->assertIsArray( $ids );
		foreach ( $ids as $id ) {
			$this->assertIsInt( $id );
		}
	}

	/**
	 * Test that mc_wra_build_url sanitises the base URL and endpoint.
	 */
	public function test_build_url_with_xss_in_base() {
		$url = mc_wra_build_url( 'javascript:alert(1)', '/endpoint' );
		// Should not return a javascript: URL.
		$this->assertStringNotContainsString( 'javascript:', $url );
	}

	// -------------------------------------------------------------------------
	// Log field sanitisation tests
	// -------------------------------------------------------------------------

	/**
	 * Test that log_action sanitises the action_key field.
	 */
	public function test_log_action_sanitises_action_key() {
		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		// Ensure the table exists.
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

		MC_Woo_Remote_Helpers::log_action(
			1, 1, 999, 'success',
			'<script>alert(1)</script>',  // Malicious action_key.
			'test@example.com',
			200,
			'OK',
			array(),
			''
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				999
			)
		);

		$this->assertNotNull( $last_log );
		// sanitize_text_field should strip the script tag.
		$this->assertStringNotContainsString( '<script>', $last_log->action_key );
	}

	/**
	 * Test that log_action sanitises the user_email field.
	 */
	public function test_log_action_sanitises_email() {
		global $wpdb;
		$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;

		MC_Woo_Remote_Helpers::log_action(
			1, 1, 998, 'success',
			'create_user',
			'invalid-<email>@<script>alert(1)</script>',
			200,
			'OK',
			array(),
			''
		);

		$last_log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
				998
			)
		);

		$this->assertNotNull( $last_log );
		$this->assertStringNotContainsString( '<script>', $last_log->user_email );
	}

	// -------------------------------------------------------------------------
	// Capability tests
	// -------------------------------------------------------------------------

	/**
	 * Test that subscribers cannot access manage_options capability.
	 */
	public function test_subscriber_cannot_manage_options() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->assertFalse( current_user_can( 'manage_options' ) );

		wp_delete_user( $user_id );
	}

	/**
	 * Test that administrators have manage_options capability.
	 */
	public function test_administrator_can_manage_options() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( 'manage_options' ) );

		wp_delete_user( $user_id );
	}
}
