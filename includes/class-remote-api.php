<?php
/**
 * Remote API endpoints for MC-Woo Remote Automations.
 *
 * @package MC_Woo_Remote_Automations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers secure REST API endpoints used when this plugin is installed on a destination site.
 */
class MC_Woo_Remote_API {

	/**
	 * Registers WordPress hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers remote API routes.
	 */
	public function register_routes() {
		register_rest_route(
			'mc/v1',
			'/create-user',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'permission_callback' ),
				'callback'            => array( $this, 'create_user' ),
			)
		);

		register_rest_route(
			'mc/v1',
			'/assign-role',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'permission_callback' ),
				'callback'            => array( $this, 'assign_role' ),
			)
		);

		register_rest_route(
			'mc/v1',
			'/ping',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'permission_callback' ),
				'callback'            => array( $this, 'ping' ),
			)
		);
	}

	/**
	 * Validates the shared secret sent through the X-MC-SECRET header.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function permission_callback( $request ) {
		$secret = (string) get_option( 'mc_wra_api_secret', '' );

		// Backward compatibility for sites that previously used the companion plugin.
		if ( '' === $secret ) {
			$secret = (string) get_option( 'mc_api_secret', '' );
		}

		$header = (string) $request->get_header( 'X-MC-SECRET' );

		if ( '' !== $secret && '' !== $header && hash_equals( $secret, $header ) ) {
			return true;
		}

		return new WP_Error(
			'mc_wra_invalid_secret',
			esc_html__( 'Invalid API secret.', 'mc-woo-remote-automations' ),
			array( 'status' => 401 )
		);
	}

	/**
	 * Returns a valid WordPress role slug.
	 *
	 * @param string $role    Requested role.
	 * @param string $default Default role.
	 * @return string
	 */
	private function sanitize_role( $role, $default = 'customer' ) {
		$role = sanitize_key( $role );
		return wp_roles()->is_role( $role ) ? $role : $default;
	}

	/**
	 * Ping endpoint used by connection tests.
	 *
	 * @return array
	 */
	public function ping() {
		return array(
			'success' => true,
			'plugin'  => 'MC-Woo Remote Automations',
			'mode'    => get_option( 'mc_wra_operating_mode', 'both' ),
		);
	}

	/**
	 * Creates a WordPress user when missing.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_REST_Response
	 */
	public function create_user( $request ) {
		$email = sanitize_email( $request->get_param( 'user_email' ) );
		$first = sanitize_text_field( (string) $request->get_param( 'first_name' ) );
		$last  = sanitize_text_field( (string) $request->get_param( 'last_name' ) );
		$role  = $this->sanitize_role( (string) $request->get_param( 'role' ), 'customer' );

		if ( ! $email || ! is_email( $email ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid email.', 'mc-woo-remote-automations' ),
				),
				400
			);
		}

		if ( email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );
			return array( 'success' => true, 'code' => 'user_exists', 'user_id' => $user ? (int) $user->ID : 0 );
		}

		$password = wp_generate_password( 24, true, false );
		$user_id  = wp_create_user( $email, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => $user_id->get_error_message() ), 500 );
		}

		wp_update_user(
			array(
				'ID'         => (int) $user_id,
				'first_name' => $first,
				'last_name'  => $last,
				'role'       => $role,
			)
		);

		return array( 'success' => true, 'code' => 'user_created', 'user_id' => (int) $user_id );
	}

	/**
	 * Assigns an existing WordPress role to an existing user.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_REST_Response
	 */
	public function assign_role( $request ) {
		$email = sanitize_email( $request->get_param( 'email' ) );
		$role  = sanitize_key( (string) $request->get_param( 'role' ) );

		if ( ! $email || ! is_email( $email ) || ! $role ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Missing email or role.', 'mc-woo-remote-automations' ),
				),
				400
			);
		}

		if ( ! wp_roles()->is_role( $role ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid role.', 'mc-woo-remote-automations' ),
				),
				400
			);
		}

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'User not found.', 'mc-woo-remote-automations' ),
				),
				404
			);
		}

		$user->set_role( $role );

		return array( 'success' => true, 'code' => 'role_assigned', 'role' => $role );
	}
}
