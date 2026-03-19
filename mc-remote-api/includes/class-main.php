<?php
/**
 * Main plugin class for MC Remote API.
 *
 * @package MC_Remote_API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers REST API routes, admin settings, and plugin activation logic.
 */
class MC_Remote_API_Main {

	/**
	 * Bootstraps all WordPress hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		register_activation_hook( MC_REMOTE_API_FILE, array( $this, 'activate' ) );

		MC_Remote_API_Banner::init();
	}

	/**
	 * Plugin activation callback – generates a random API secret if none exists.
	 */
	public function activate() {
		if ( ! get_option( 'mc_api_secret' ) ) {
			update_option( 'mc_api_secret', wp_generate_password( 32, true, true ) );
		}
	}

	/**
	 * Registers the REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			'mc/v1',
			'/create-user',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'create_user' ),
			)
		);
		register_rest_route(
			'mc/v1',
			'/assign-role',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'assign_role' ),
			)
		);
		register_rest_route(
			'mc/v1',
			'/ping',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'ping' ),
			)
		);
	}

	/**
	 * Validates the X-MC-SECRET header against the stored secret.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return bool
	 */
	private function is_valid_request( $request ) {
		$secret = (string) get_option( 'mc_api_secret' );
		$header = (string) $request->get_header( 'X-MC-SECRET' );
		return $secret !== '' && hash_equals( $secret, $header );
	}

	/**
	 * Returns a valid WordPress role slug, falling back to a default.
	 *
	 * @param string $role    Requested role slug.
	 * @param string $default Fallback role to use when $role is invalid.
	 * @return string
	 */
	private function sanitize_role( $role, $default = 'customer' ) {
		return wp_roles()->is_role( $role ) ? $role : $default;
	}

	/**
	 * Handles the /ping endpoint.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response|array
	 */
	public function ping( $request ) {
		if ( ! $this->is_valid_request( $request ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Invalid secret' ), 401 );
		}
		return array( 'success' => true, 'plugin' => 'MC Remote API' );
	}

	/**
	 * Handles the /create-user endpoint.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response|array
	 */
	public function create_user( $request ) {
		if ( ! $this->is_valid_request( $request ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Invalid secret' ), 401 );
		}

		$email = sanitize_email( $request['user_email'] );
		$first = sanitize_text_field( $request['first_name'] );
		$last  = sanitize_text_field( $request['last_name'] );
		$role  = $this->sanitize_role( sanitize_text_field( $request['role'] ?? 'customer' ) );

		if ( ! $email || ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Invalid email' ), 400 );
		}

		if ( email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );
			return array( 'success' => true, 'code' => 'user_exists', 'user_id' => $user ? $user->ID : 0 );
		}

		// Generate a secure random password instead of using the email address.
		$password = wp_generate_password( 24, true, false );
		$user_id  = wp_create_user( $email, $password, $email );
		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => $user_id->get_error_message() ), 500 );
		}

		wp_update_user(
			array(
				'ID'         => $user_id,
				'first_name' => $first,
				'last_name'  => $last,
				'role'       => $role,
			)
		);

		return array( 'success' => true, 'code' => 'user_created', 'user_id' => $user_id );
	}

	/**
	 * Handles the /assign-role endpoint.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response|array
	 */
	public function assign_role( $request ) {
		if ( ! $this->is_valid_request( $request ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Invalid secret' ), 401 );
		}

		$email = sanitize_email( $request['email'] );
		$role  = sanitize_text_field( $request['role'] );

		if ( ! $email || ! $role ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Missing email or role' ), 400 );
		}

		// Validate that the requested role exists in WordPress.
		if ( ! wp_roles()->is_role( $role ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Invalid role' ), 400 );
		}

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'User not found' ), 404 );
		}

		$user->set_role( $role );

		return array( 'success' => true, 'code' => 'role_assigned', 'role' => $role );
	}

	/**
	 * Registers the plugin settings page under Settings.
	 */
	public function admin_menu() {
		add_options_page(
			'MC Remote API',
			'MC Remote API',
			'manage_options',
			'mc-remote-api',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registers the mc_api_secret option.
	 */
	public function register_settings() {
		register_setting(
			'mc_api_settings',
			'mc_api_secret',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	/**
	 * Renders the plugin settings page HTML.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'MC Remote API', 'mc-remote-api' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'mc_api_settings' ); ?>
				<table class="form-table">
					<tr>
						<th><?php echo esc_html__( 'API Secret', 'mc-remote-api' ); ?></th>
						<td>
							<input type="text" class="regular-text" name="mc_api_secret" value="<?php echo esc_attr( get_option( 'mc_api_secret' ) ); ?>">
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<h2><?php echo esc_html__( 'Endpoints', 'mc-remote-api' ); ?></h2>
			<p><code>/wp-json/mc/v1/create-user</code></p>
			<p><code>/wp-json/mc/v1/assign-role</code></p>
			<p><code>/wp-json/mc/v1/ping</code></p>
		</div>
		<?php
	}
}
