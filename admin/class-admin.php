<?php
/**
 * Admin UI class for MC-Woo Remote Automations.
 *
 * Handles meta boxes, custom columns, admin menu, and settings pages.
 *
 * @package MC_Woo_Remote_Automations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders all admin-facing UI for the plugin.
 */
class MC_Woo_Remote_Admin {
	/**
	 * Registers all admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_mcwra_connection', array( $this, 'save_connection' ), 10, 2 );
		add_action( 'save_post_mcwra_automation', array( $this, 'save_automation' ), 10, 2 );
		add_action( 'admin_post_mc_wra_test_connection', array( $this, 'handle_test_connection' ) );
		add_action( 'admin_post_mc_wra_logs_cleanup', array( $this, 'handle_logs_cleanup' ) );
		add_filter( 'manage_mcwra_automation_posts_columns', array( $this, 'automation_columns' ) );
		add_action( 'manage_mcwra_automation_posts_custom_column', array( $this, 'automation_column_content' ), 10, 2 );
		add_filter( 'enter_title_here', array( $this, 'custom_title_placeholder' ) );
		add_action( 'admin_notices', array( $this, 'render_test_result_notice' ) );
		add_action( 'wp_ajax_mc_wra_save_test_connection', array( $this, 'ajax_save_and_test_connection' ) );
		add_action( 'add_meta_boxes', array( $this, 'replace_connection_publish_box' ), 99 );
	}

	/**
	 * Registers the admin menu.
	 */
	public function admin_menu() {
		$parent_slug = 'edit.php?post_type=mcwra_automation';
		add_menu_page(
			__( 'Woo Remote Automations', 'mc-woo-remote-automations' ),
			__( 'Woo Remote Automations', 'mc-woo-remote-automations' ),
			'manage_options',
			$parent_slug,
			'',
			'dashicons-randomize',
			56
		);
		add_submenu_page( $parent_slug, __( 'Automations', 'mc-woo-remote-automations' ), __( 'Automations', 'mc-woo-remote-automations' ), 'manage_options', $parent_slug );
		add_submenu_page( $parent_slug, __( 'Connections', 'mc-woo-remote-automations' ), __( 'Connections', 'mc-woo-remote-automations' ), 'manage_options', 'edit.php?post_type=mcwra_connection' );
		add_submenu_page( $parent_slug, __( 'Logs', 'mc-woo-remote-automations' ), __( 'Logs', 'mc-woo-remote-automations' ), 'manage_options', 'mc-wra-logs', array( $this, 'render_logs_page' ) );
		add_submenu_page( $parent_slug, __( 'Settings', 'mc-woo-remote-automations' ), __( 'Settings', 'mc-woo-remote-automations' ), 'manage_options', 'mc-wra-settings', array( $this, 'render_settings_page' ) );
	}

	/**
	 * Registers plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'mc_wra_settings',
			'mc_wra_default_timeout',
			array(
				'type'              => 'integer',
				'sanitize_callback' => function ( $v ) {
					return max( 1, intval( $v ) );
				},
				'default'           => 10,
			)
		);

		register_setting(
			'mc_wra_settings',
			'mc_wra_operating_mode',
			array(
				'type'              => 'string',
				'sanitize_callback' => function ( $v ) {
					return in_array( $v, array( 'controller', 'remote', 'both' ), true ) ? $v : 'both';
				},
				'default'           => 'both',
			)
		);
		register_setting(
			'mc_wra_settings',
			'mc_wra_api_secret',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'mc_wra_settings',
			'mc_wra_log_retention_days',
			array(
				'type'              => 'integer',
				'sanitize_callback' => function ( $v ) {
					return max( 0, intval( $v ) );
				},
				'default'           => 90,
			)
		);
		register_setting(
			'mc_wra_settings',
			'mc_wra_delete_data_on_uninstall',
			array(
				'type'              => 'string',
				'sanitize_callback' => function ( $v ) {
					return 'yes' === $v ? 'yes' : 'no';
				},
				'default'           => 'no',
			)
		);
	}

	/**
	 * Enqueues admin-only JavaScript on relevant plugin screens.
	 *
	 * Targets: the Connection edit/create screen and the plugin Settings page.
	 * i18n strings are passed via wp_localize_script to avoid inline script.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		$load = false;

		// Connection create/edit screens.
		if ( $screen && 'mcwra_connection' === $screen->post_type ) {
			$load = true;
		}

		// Settings submenu page (hook suffix contains the page slug).
		if ( ! $load && false !== strpos( $hook, 'mc-wra-settings' ) ) {
			$load = true;
		}

		if ( ! $load ) {
			return;
		}

		wp_enqueue_script(
			'mc-wra-admin',
			MC_WOO_REMOTE_URL . 'assets/js/mc-wra-admin.js',
			array(),
			MC_WOO_REMOTE_VERSION,
			true
		);

		wp_localize_script(
			'mc-wra-admin',
			'mcWraAdminI18n',
			array(
				'show'               => __( 'Show', 'mc-woo-remote-automations' ),
				'hide'               => __( 'Hide', 'mc-woo-remote-automations' ),
				'saving'             => __( 'Saving and testing connection...', 'mc-woo-remote-automations' ),
				'invalidResponse'    => __( 'The server returned an invalid response.', 'mc-woo-remote-automations' ),
				'unexpectedResponse' => __( 'Unexpected response from WordPress.', 'mc-woo-remote-automations' ),
				'networkError'       => __( 'Connection test failed due to a browser or server error.', 'mc-woo-remote-automations' ),
			)
		);
	}

	/**
	 * Registers meta boxes for Connection and Automation post types.
	 */
	public function add_meta_boxes() {
		add_meta_box( 'mc_wra_connection_box', __( 'Connection Settings', 'mc-woo-remote-automations' ), array( $this, 'render_connection_box' ), 'mcwra_connection', 'normal', 'default' );
		add_meta_box( 'mc_wra_automation_box', __( 'Automation Settings', 'mc-woo-remote-automations' ), array( $this, 'render_automation_box' ), 'mcwra_automation', 'normal', 'default' );
	}

	/**
	 * Renders the Connection meta box fields.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_connection_box( $post ) {
		wp_nonce_field( 'mc_wra_save_connection', 'mc_wra_connection_nonce' );
		$enabled         = get_post_meta( $post->ID, '_mc_enabled', true );
		$base_url        = get_post_meta( $post->ID, '_mc_base_url', true );
		$create_endpoint = get_post_meta( $post->ID, '_mc_create_endpoint', true ) ?: '/wp-json/mc/v1/create-user';
		$role_endpoint   = get_post_meta( $post->ID, '_mc_role_endpoint', true ) ?: '/wp-json/mc/v1/assign-role';
		$ping_endpoint   = get_post_meta( $post->ID, '_mc_ping_endpoint', true ) ?: '/wp-json/mc/v1/ping';
		$remote_secret   = get_post_meta( $post->ID, '_mc_remote_secret', true );
		if ( '' === $remote_secret ) {
			$remote_secret = get_post_meta( $post->ID, '_mc_create_secret', true );
		}
		?>
		<table class="form-table">
			<tr>
				<th><label for="mc_enabled"><?php esc_html_e( 'Enabled', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="checkbox" id="mc_enabled" name="mc_enabled" value="yes" <?php checked( $enabled, 'yes' ); ?>></td>
			</tr>
			<tr>
				<th><label for="mc_base_url"><?php esc_html_e( 'Remote Site URL', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="url" class="regular-text" id="mc_base_url" name="mc_base_url" value="<?php echo esc_attr( $base_url ); ?>" placeholder="https://example.com"></td>
			</tr>
			<tr>
				<th><label for="mc_create_endpoint"><?php esc_html_e( 'Create User Endpoint', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="text" class="regular-text" id="mc_create_endpoint" name="mc_create_endpoint" value="<?php echo esc_attr( $create_endpoint ); ?>"></td>
			</tr>
			<tr>
				<th><label for="mc_role_endpoint"><?php esc_html_e( 'Assign Role Endpoint', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="text" class="regular-text" id="mc_role_endpoint" name="mc_role_endpoint" value="<?php echo esc_attr( $role_endpoint ); ?>"></td>
			</tr>
			<tr>
				<th><label for="mc_ping_endpoint"><?php esc_html_e( 'Ping Endpoint', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="text" class="regular-text" id="mc_ping_endpoint" name="mc_ping_endpoint" value="<?php echo esc_attr( $ping_endpoint ); ?>"></td>
			</tr>
			<tr>
				<th><label for="mc_remote_secret"><?php esc_html_e( 'Remote API Secret', 'mc-woo-remote-automations' ); ?></label></th>
				<td>
					<input type="password" class="regular-text" id="mc_remote_secret" name="mc_remote_secret" value="<?php echo esc_attr( $remote_secret ); ?>">
					<button type="button" class="button" id="mc_remote_secret_toggle" data-mc-wra-toggle="mc_remote_secret" style="margin-left:8px;"><?php esc_html_e( 'Show', 'mc-woo-remote-automations' ); ?></button>
					<p class="description"><?php esc_html_e( 'Paste here the Remote API Secret generated on the destination site settings page.', 'mc-woo-remote-automations' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders the Automation meta box fields.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_automation_box( $post ) {
		wp_nonce_field( 'mc_wra_save_automation', 'mc_wra_automation_nonce' );
		$enabled          = get_post_meta( $post->ID, '_mc_enabled', true );
		$order_status     = get_post_meta( $post->ID, '_mc_order_status', true ) ?: 'completed';
		$product_ids      = get_post_meta( $post->ID, '_mc_product_ids', true );
		if ( ! is_array( $product_ids ) ) {
			$product_ids = array();
		}
		$connection_id    = intval( get_post_meta( $post->ID, '_mc_connection_id', true ) );
		$create_if_missing = get_post_meta( $post->ID, '_mc_create_if_missing', true );
		$assign_role      = get_post_meta( $post->ID, '_mc_assign_role', true );
		$remote_role      = get_post_meta( $post->ID, '_mc_remote_role', true );
		$timeout          = intval( get_post_meta( $post->ID, '_mc_timeout', true ) );
		$statuses         = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
		$products         = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		$connections      = get_posts(
			array(
				'post_type'      => 'mcwra_connection',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<table class="form-table">
			<tr>
				<th><label for="mc_enabled_auto"><?php esc_html_e( 'Enabled', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="checkbox" id="mc_enabled_auto" name="mc_enabled" value="yes" <?php checked( $enabled, 'yes' ); ?>></td>
			</tr>
			<tr>
				<th><label for="mc_order_status"><?php esc_html_e( 'Order Status Trigger', 'mc-woo-remote-automations' ); ?></label></th>
				<td>
					<select id="mc_order_status" name="mc_order_status">
						<?php foreach ( $statuses as $key => $label ) : $slug = str_replace( 'wc-', '', $key ); ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $order_status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="mc_product_ids"><?php esc_html_e( 'Products', 'mc-woo-remote-automations' ); ?></label></th>
				<td>
					<select id="mc_product_ids" name="mc_product_ids[]" multiple size="12" style="min-width:420px;">
						<?php foreach ( $products as $product ) : ?>
							<option value="<?php echo esc_attr( $product->ID ); ?>" <?php echo in_array( $product->ID, array_map( 'intval', $product_ids ), true ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $product->post_title . ' (#' . $product->ID . ')' ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Hold Ctrl or Cmd to select multiple products.', 'mc-woo-remote-automations' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="mc_connection_id"><?php esc_html_e( 'Connection', 'mc-woo-remote-automations' ); ?></label></th>
				<td>
					<select id="mc_connection_id" name="mc_connection_id">
						<option value=""><?php esc_html_e( 'Select a connection', 'mc-woo-remote-automations' ); ?></option>
						<?php foreach ( $connections as $connection ) : ?>
							<option value="<?php echo esc_attr( $connection->ID ); ?>" <?php selected( $connection_id, $connection->ID ); ?>>
								<?php echo esc_html( $connection->post_title . ' (#' . $connection->ID . ')' ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="mc_create_if_missing"><?php esc_html_e( 'Create User If Missing', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="checkbox" id="mc_create_if_missing" name="mc_create_if_missing" value="yes" <?php checked( $create_if_missing, 'yes' ); ?>></td>
			</tr>
			<tr>
				<th><label for="mc_assign_role"><?php esc_html_e( 'Assign Role', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="checkbox" id="mc_assign_role" name="mc_assign_role" value="yes" <?php checked( $assign_role, 'yes' ); ?>></td>
			</tr>
			<tr>
				<th><label for="mc_remote_role"><?php esc_html_e( 'Remote Role', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="text" class="regular-text" id="mc_remote_role" name="mc_remote_role" value="<?php echo esc_attr( $remote_role ); ?>" placeholder="student"></td>
			</tr>
			<tr>
				<th><label for="mc_timeout"><?php esc_html_e( 'Override Timeout (seconds)', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="number" min="1" id="mc_timeout" name="mc_timeout" value="<?php echo esc_attr( $timeout ?: '' ); ?>" placeholder="<?php esc_attr_e( 'Use global default', 'mc-woo-remote-automations' ); ?>"></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Saves Connection meta box fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object (unused).
	 */
	public function save_connection( $post_id, $post ) {
		if ( ! isset( $_POST['mc_wra_connection_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mc_wra_connection_nonce'] ) ), 'mc_wra_save_connection' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$save_result = $this->save_connection_meta_from_request( $post_id );
		if ( is_wp_error( $save_result ) ) {
			add_filter(
				'redirect_post_location',
				function ( $location ) use ( $save_result ) {
					return add_query_arg(
						array(
							'mc_wra_test' => '0',
							'mc_wra_msg'  => rawurlencode( $save_result->get_error_message() ),
						),
						$location
					);
				}
			);
		}
	}

	/**
	 * Saves Automation meta box fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object (unused).
	 */
	public function save_automation( $post_id, $post ) {
		if ( ! isset( $_POST['mc_wra_automation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mc_wra_automation_nonce'] ) ), 'mc_wra_save_automation' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		update_post_meta( $post_id, '_mc_enabled', isset( $_POST['mc_enabled'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_mc_order_status', sanitize_text_field( wp_unslash( $_POST['mc_order_status'] ?? 'completed' ) ) );
		update_post_meta( $post_id, '_mc_product_ids', array_map( 'intval', (array) ( $_POST['mc_product_ids'] ?? array() ) ) );
		update_post_meta( $post_id, '_mc_connection_id', intval( $_POST['mc_connection_id'] ?? 0 ) );
		update_post_meta( $post_id, '_mc_create_if_missing', isset( $_POST['mc_create_if_missing'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_mc_assign_role', isset( $_POST['mc_assign_role'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_mc_remote_role', sanitize_text_field( wp_unslash( $_POST['mc_remote_role'] ?? '' ) ) );
		update_post_meta( $post_id, '_mc_timeout', max( 0, intval( $_POST['mc_timeout'] ?? 0 ) ) );
	}

	/**
	 * Handles the Test Connection admin-post action.
	 */
	public function handle_test_connection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'mc-woo-remote-automations' ) );
		}
		$connection_id = intval( $_GET['connection_id'] ?? 0 );
		check_admin_referer( 'mc_wra_test_connection_' . $connection_id );

		$result   = $this->get_connection_test_result( $connection_id );
		$redirect = admin_url( 'post.php?post=' . $connection_id . '&action=edit' );
		$redirect = add_query_arg(
			array(
				'mc_wra_test' => $result['success'] ? '1' : '0',
				'mc_wra_msg'  => rawurlencode( $result['message'] ),
			),
			$redirect
		);
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Replaces the standard WordPress Publish box for Connection records.
	 */
	public function replace_connection_publish_box() {
		remove_meta_box( 'submitdiv', 'mcwra_connection', 'side' );

		add_meta_box(
			'mc_wra_connection_actions',
			__( 'Connection Actions', 'mc-woo-remote-automations' ),
			array( $this, 'render_connection_actions_box' ),
			'mcwra_connection',
			'normal',
			'low'
		);
	}

	/**
	 * Renders a simplified action box for Connection records.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_connection_actions_box( $post ) {
		$nonce       = wp_create_nonce( 'mc_wra_ajax_save_test_connection_' . $post->ID );
		$button_text = ( $post->ID && 'auto-draft' !== $post->post_status )
			? __( 'Save & Test Connection', 'mc-woo-remote-automations' )
			: __( 'Publish & Test Connection', 'mc-woo-remote-automations' );
		?>
		<div class="mc-wra-connection-actions" style="padding: 6px 0;">
			<p style="margin-top:0;">
				<?php esc_html_e( 'Save the connection settings, publish the connection, and immediately test the remote endpoint.', 'mc-woo-remote-automations' ); ?>
			</p>

			<p>
				<button type="button" class="button button-primary button-large" id="mc-wra-save-test-button"
					data-nonce="<?php echo esc_attr( $nonce ); ?>"
					data-post-id="<?php echo esc_attr( $post->ID ); ?>">
					<?php echo esc_html( $button_text ); ?>
				</button>
				<span class="spinner" id="mc-wra-save-test-spinner" style="float:none;margin-top:0;"></span>
			</p>

			<div id="mc-wra-save-test-result" style="display:none;margin-top:10px;"></div>

			<p class="description">
				<?php esc_html_e( 'The standard WordPress Publish/Update box is hidden on this screen to avoid confusion.', 'mc-woo-remote-automations' ); ?>
			</p>
		</div>
		<?php
	}



	/**
	 * Save Connection metadata from an HTTP request.
	 *
	 * @param int $post_id Connection post ID.
	 */
	private function save_connection_meta_from_request( $post_id ) {
		$enabled_value = sanitize_text_field( wp_unslash( $_POST['mc_enabled'] ?? $_POST['mc_connection_enabled'] ?? '' ) );
		$base_url      = esc_url_raw( wp_unslash( $_POST['mc_base_url'] ?? '' ) );
		$url_check     = mc_wra_validate_remote_base_url( $base_url );

		if ( is_wp_error( $url_check ) ) {
			return $url_check;
		}

		update_post_meta( $post_id, '_mc_enabled', in_array( $enabled_value, array( 'yes', '1', 'on' ), true ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_mc_base_url', $base_url );
		update_post_meta( $post_id, '_mc_create_endpoint', sanitize_text_field( wp_unslash( $_POST['mc_create_endpoint'] ?? '/wp-json/mc/v1/create-user' ) ) );
		update_post_meta( $post_id, '_mc_role_endpoint', sanitize_text_field( wp_unslash( $_POST['mc_role_endpoint'] ?? '/wp-json/mc/v1/assign-role' ) ) );
		update_post_meta( $post_id, '_mc_ping_endpoint', sanitize_text_field( wp_unslash( $_POST['mc_ping_endpoint'] ?? '/wp-json/mc/v1/ping' ) ) );

		$remote_secret = sanitize_text_field( wp_unslash( $_POST['mc_remote_secret'] ?? '' ) );
		update_post_meta( $post_id, '_mc_remote_secret', $remote_secret );

		// Backward compatibility with earlier versions.
		update_post_meta( $post_id, '_mc_create_secret', $remote_secret );
		update_post_meta( $post_id, '_mc_role_secret', $remote_secret );

		return true;
	}

	/**
	 * AJAX handler: save, publish and test a Connection without using wp-admin/post.php.
	 */
	public function ajax_save_and_test_connection() {
		try {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Permission denied.', 'mc-woo-remote-automations' ) ),
					403
				);
			}

			$post_id = absint( $_POST['post_id'] ?? 0 );

			if ( ! $post_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'mc_wra_ajax_save_test_connection_' . $post_id ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Security check failed. Please reload the page and try again.', 'mc-woo-remote-automations' ) ),
					403
				);
			}

			if ( 'mcwra_connection' !== get_post_type( $post_id ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Invalid connection record.', 'mc-woo-remote-automations' ) ),
					400
				);
			}

			$title = sanitize_text_field( wp_unslash( $_POST['post_title'] ?? '' ) );
			if ( '' === $title ) {
				$title = __( 'Remote Connection', 'mc-woo-remote-automations' );
			}

			$updated = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_title'  => $title,
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $updated ) ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %s: WordPress error message. */
							__( 'Connection could not be saved: %s', 'mc-woo-remote-automations' ),
							$updated->get_error_message()
						),
					),
					500
				);
			}

			$save_result = $this->save_connection_meta_from_request( $post_id );
			if ( is_wp_error( $save_result ) ) {
				wp_send_json_error(
					array(
						'message' => $save_result->get_error_message(),
					),
					400
				);
			}

			$result   = $this->get_connection_test_result( $post_id );
			$edit_url = get_edit_post_link( $post_id, 'raw' );

			if ( ! $edit_url ) {
				$edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
			}

			if ( ! empty( $result['success'] ) ) {
				wp_send_json_success(
					array(
						'message'  => $result['message'],
						'edit_url' => $edit_url,
					)
				);
			}

			wp_send_json_error(
				array(
					'message'  => $result['message'] ?? __( 'Connection test failed.', 'mc-woo-remote-automations' ),
					'edit_url' => $edit_url,
				)
			);
		} catch ( Throwable $e ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %s: PHP error message. */
						__( 'Internal error while saving/testing the connection: %s', 'mc-woo-remote-automations' ),
						$e->getMessage()
					),
				),
				500
			);
		}
	}

	/**
	 * Tests a saved Connection and returns a normalized result.
	 *
	 * @param int $connection_id Connection post ID.
	 * @return array{success:bool,message:string}
	 */
	private function get_connection_test_result( $connection_id ) {
		$base_url      = get_post_meta( $connection_id, '_mc_base_url', true );
		$ping_endpoint = get_post_meta( $connection_id, '_mc_ping_endpoint', true ) ?: '/wp-json/mc/v1/ping';
		$secret        = get_post_meta( $connection_id, '_mc_remote_secret', true );
		if ( '' === $secret ) {
			$secret = get_post_meta( $connection_id, '_mc_create_secret', true );
		}
		$timeout = intval( get_option( 'mc_wra_default_timeout', 10 ) );
		$url     = mc_wra_build_url( $base_url, $ping_endpoint );
		$url_check = mc_wra_validate_remote_base_url( $base_url );

		if ( is_wp_error( $url_check ) || ! wp_http_validate_url( $url ) ) {
			return array(
				'success' => false,
				'message' => is_wp_error( $url_check ) ? $url_check->get_error_message() : __( 'Connection not tested: the Remote Site URL is empty or invalid.', 'mc-woo-remote-automations' ),
			);
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => $timeout,
				'headers' => array(
					'X-MC-SECRET' => $secret,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message. */
					__( 'Connection failed: %s', 'mc-woo-remote-automations' ),
					$response->get_error_message()
				),
			);
		}

		$code         = wp_remote_retrieve_response_code( $response );
		$body         = wp_remote_retrieve_body( $response );
		$body_preview = substr( wp_strip_all_tags( (string) $body ), 0, 300 );

		if ( $code >= 200 && $code < 300 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: HTTP code, 2: response body. */
					__( 'Connection successful. HTTP %1$d. Response: %2$s', 'mc-woo-remote-automations' ),
					$code,
					$body_preview
				),
			);
		}

		return array(
			'success' => false,
			'message' => sprintf(
				/* translators: 1: HTTP code, 2: response body. */
				__( 'Connection failed. HTTP %1$d. Response: %2$s', 'mc-woo-remote-automations' ),
				$code,
				$body_preview
			),
		);
	}

	/**
	 * Handles explicit log cleanup actions from the Logs screen.
	 */
	public function handle_logs_cleanup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'mc-woo-remote-automations' ) );
		}
		check_admin_referer( 'mc_wra_logs_cleanup' );

		$action   = sanitize_text_field( wp_unslash( $_POST['mc_wra_logs_action'] ?? '' ) );
		$redirect = admin_url( 'admin.php?page=mc-wra-logs' );
		global $wpdb;
		$table  = MC_Woo_Remote_Helpers::get_log_table_name();
		if ( '' === $table ) {
			wp_die( esc_html__( 'Invalid log table name.', 'mc-woo-remote-automations' ) );
		}
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $table !== $exists ) {
			$redirect = add_query_arg(
				array(
					'mc_wra_notice'      => rawurlencode( __( 'Log table not found. Reactivate the plugin to recreate it before using cleanup tools.', 'mc-woo-remote-automations' ) ),
					'mc_wra_notice_type' => 'warning',
				),
				$redirect
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( 'purge_old' === $action ) {
			$days = intval( get_option( 'mc_wra_log_retention_days', 90 ) );
			if ( $days <= 0 ) {
				$redirect = add_query_arg(
					array(
						'mc_wra_notice'      => rawurlencode( __( 'Log retention is disabled. Set retention days above 0 before purging old logs.', 'mc-woo-remote-automations' ) ),
						'mc_wra_notice_type' => 'warning',
					),
					$redirect
				);
			} else {
				$deleted  = MC_Woo_Remote_Helpers::delete_logs_older_than( $days );
				$redirect = add_query_arg(
					array(
						'mc_wra_notice'      => rawurlencode( sprintf( /* translators: %d: number of deleted logs. */ __( 'Deleted %d logs older than the retention period.', 'mc-woo-remote-automations' ), $deleted ) ),
						'mc_wra_notice_type' => 'success',
					),
					$redirect
				);
			}
		} elseif ( 'clear_all' === $action ) {
			$confirmed = isset( $_POST['mc_wra_confirm_clear'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['mc_wra_confirm_clear'] ) );
			if ( ! $confirmed ) {
				$redirect = add_query_arg(
					array(
						'mc_wra_notice'      => rawurlencode( __( 'Please confirm that you want to clear all logs.', 'mc-woo-remote-automations' ) ),
						'mc_wra_notice_type' => 'warning',
					),
					$redirect
				);
			} else {
				$deleted  = MC_Woo_Remote_Helpers::delete_all_logs();
				$redirect = add_query_arg(
					array(
						'mc_wra_notice'      => rawurlencode( sprintf( /* translators: %d: number of deleted logs. */ __( 'Cleared %d logs.', 'mc-woo-remote-automations' ), $deleted ) ),
						'mc_wra_notice_type' => 'success',
					),
					$redirect
				);
			}
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Displays a test-connection result notice when redirected back.
	 */
	public function render_test_result_notice() {
		$test_result = isset( $_GET['mc_wra_test'] ) ? sanitize_text_field( wp_unslash( $_GET['mc_wra_test'] ) ) : '';
		$test_msg    = isset( $_GET['mc_wra_msg'] ) ? sanitize_text_field( wp_unslash( rawurldecode( $_GET['mc_wra_msg'] ) ) ) : '';
		if ( '' === $test_result || '' === $test_msg ) {
			$notice      = isset( $_GET['mc_wra_notice'] ) ? sanitize_text_field( wp_unslash( rawurldecode( $_GET['mc_wra_notice'] ) ) ) : '';
			$notice_type = isset( $_GET['mc_wra_notice_type'] ) ? sanitize_text_field( wp_unslash( $_GET['mc_wra_notice_type'] ) ) : 'success';
			if ( '' === $notice ) {
				return;
			}
			$class = 'warning' === $notice_type ? 'notice notice-warning' : 'notice notice-success';
			echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $notice ) . '</p></div>';
			return;
		}
		$class = '1' === $test_result ? 'notice notice-success' : 'notice notice-error';
		echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $test_msg ) . '</p></div>';
	}

	/**
	 * Adds custom columns to the Automation list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function automation_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new[ $key ] = __( 'Automation Name', 'mc-woo-remote-automations' );
				continue;
			}
			if ( 'date' === $key ) {
				$new['mc_connection']     = __( 'Connection', 'mc-woo-remote-automations' );
				$new['mc_status_trigger'] = __( 'Status Trigger', 'mc-woo-remote-automations' );
				$new['mc_products_count'] = __( 'Products', 'mc-woo-remote-automations' );
				$new['mc_last_run']       = __( 'Last Run', 'mc-woo-remote-automations' );
			}
			$new[ $key ] = $label;
		}
		return $new;
	}

	/**
	 * Renders content for custom Automation list table columns.
	 *
	 * @param string $column  Column identifier.
	 * @param int    $post_id Post ID.
	 */
	public function automation_column_content( $column, $post_id ) {
		global $wpdb;
		if ( 'mc_connection' === $column ) {
			$connection_id = intval( get_post_meta( $post_id, '_mc_connection_id', true ) );
			echo $connection_id ? esc_html( get_the_title( $connection_id ) ) : '&mdash;';
		} elseif ( 'mc_status_trigger' === $column ) {
			$status = get_post_meta( $post_id, '_mc_order_status', true );
			echo $status ? esc_html( $status ) : '&mdash;';
		} elseif ( 'mc_products_count' === $column ) {
			$product_ids = get_post_meta( $post_id, '_mc_product_ids', true );
			echo is_array( $product_ids ) ? esc_html( count( $product_ids ) ) : '0';
		} elseif ( 'mc_last_run' === $column ) {
			$table = MC_Woo_Remote_Helpers::get_log_table_name();
			if ( '' !== $table ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$last = $wpdb->get_var(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						"SELECT created_at FROM {$table} WHERE automation_id = %d ORDER BY id DESC LIMIT 1",
						$post_id
					)
				);
				echo $last ? esc_html( $last ) : '&mdash;';
			} else {
				echo '&mdash;';
			}
		}
	}

	/**
	 * Customises the "Enter title here" placeholder for CPT edit screens.
	 *
	 * @param string $title Default placeholder text.
	 * @return string
	 */
	public function custom_title_placeholder( $title ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return $title;
		}
		if ( 'mcwra_automation' === $screen->post_type ) {
			return __( 'Automation Name', 'mc-woo-remote-automations' );
		}
		if ( 'mcwra_connection' === $screen->post_type ) {
			return __( 'Connection Name', 'mc-woo-remote-automations' );
		}
		return $title;
	}

	/**
	 * Renders the Logs admin page.
	 */
	public function render_logs_page() {
		global $wpdb;

		$table      = MC_Woo_Remote_Helpers::get_log_table_name();
		if ( '' === $table ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Execution Logs', 'mc-woo-remote-automations' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid log table name configuration.', 'mc-woo-remote-automations' ) . '</p></div></div>';
			return;
		}
		$exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $table !== $exists ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Execution Logs', 'mc-woo-remote-automations' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Log table was not found. Please deactivate and reactivate the plugin to recreate it.', 'mc-woo-remote-automations' ) . '</p></div></div>';
			return;
		}
		$per_page   = 20;
		$paged      = max( 1, intval( $_GET['paged'] ?? 1 ) );
		$offset     = ( $paged - 1 ) * $per_page;
		$status     = sanitize_text_field( wp_unslash( $_GET['status'] ?? '' ) );
		$action_key = sanitize_text_field( wp_unslash( $_GET['action_key'] ?? '' ) );
		$search     = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );

		$where = array();

		if ( in_array( $status, array( 'success', 'failed' ), true ) ) {
			$where[] = $wpdb->prepare( 'status = %s', $status );
		} else {
			$status = '';
		}

		if ( in_array( $action_key, array( 'create_user', 'assign_role' ), true ) ) {
			$where[] = $wpdb->prepare( 'action_key = %s', $action_key );
		} else {
			$action_key = '';
		}

		if ( '' !== $search ) {
			if ( ctype_digit( $search ) ) {
				$where[] = $wpdb->prepare( 'order_id = %d', intval( $search ) );
			} else {
				$like    = '%' . $wpdb->esc_like( $search ) . '%';
				$where[] = $wpdb->prepare( '(user_email LIKE %s OR message LIKE %s)', $like, $like );
			}
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = ' WHERE ' . implode( ' AND ', $where );
		}

		$count_sql = "SELECT COUNT(*) FROM {$table}{$where_sql}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = intval( $wpdb->get_var( $count_sql ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->prepare(
			"SELECT id, created_at, automation_id, connection_id, order_id, action_key, user_email, status, response_code, message, request_payload, response_body FROM {$table}{$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$logs        = $wpdb->get_results( $query, ARRAY_A );
		$total_pages = max( 1, (int) ceil( $total / $per_page ) );
		$retention = intval( get_option( 'mc_wra_log_retention_days', 90 ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Execution Logs', 'mc-woo-remote-automations' ); ?></h1>
			<p><?php esc_html_e( 'Use this screen to troubleshoot remote automation calls. Sensitive values in payload previews are redacted.', 'mc-woo-remote-automations' ); ?></p>

			<form method="get" style="margin-bottom: 16px;">
				<input type="hidden" name="page" value="mc-wra-logs">
				<select name="status">
					<option value=""><?php esc_html_e( 'All statuses', 'mc-woo-remote-automations' ); ?></option>
					<option value="success" <?php selected( $status, 'success' ); ?>><?php esc_html_e( 'Success', 'mc-woo-remote-automations' ); ?></option>
					<option value="failed" <?php selected( $status, 'failed' ); ?>><?php esc_html_e( 'Failed', 'mc-woo-remote-automations' ); ?></option>
				</select>
				<select name="action_key">
					<option value=""><?php esc_html_e( 'All actions', 'mc-woo-remote-automations' ); ?></option>
					<option value="create_user" <?php selected( $action_key, 'create_user' ); ?>><?php esc_html_e( 'Create User', 'mc-woo-remote-automations' ); ?></option>
					<option value="assign_role" <?php selected( $action_key, 'assign_role' ); ?>><?php esc_html_e( 'Assign Role', 'mc-woo-remote-automations' ); ?></option>
				</select>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search email, message, or order ID', 'mc-woo-remote-automations' ); ?>">
				<?php submit_button( __( 'Filter', 'mc-woo-remote-automations' ), 'secondary', '', false ); ?>
			</form>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date/Time', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Automation', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Connection', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Order ID', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Action', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'User Email', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Status', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Response Code', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Message', 'mc-woo-remote-automations' ); ?></th>
						<th><?php esc_html_e( 'Details', 'mc-woo-remote-automations' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr>
							<td colspan="10"><?php esc_html_e( 'No logs found for the current filter.', 'mc-woo-remote-automations' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( (string) $log['created_at'] ); ?></td>
								<td><?php echo esc_html( $this->format_log_object_label( intval( $log['automation_id'] ), 'automation' ) ); ?></td>
								<td><?php echo esc_html( $this->format_log_object_label( intval( $log['connection_id'] ), 'connection' ) ); ?></td>
								<td><?php echo esc_html( (string) $log['order_id'] ); ?></td>
								<td><?php echo esc_html( (string) $log['action_key'] ); ?></td>
								<td><?php echo esc_html( (string) $log['user_email'] ); ?></td>
								<td><?php echo esc_html( (string) $log['status'] ); ?></td>
								<td><?php echo esc_html( is_null( $log['response_code'] ) ? '—' : (string) $log['response_code'] ); ?></td>
								<td><?php echo esc_html( (string) $log['message'] ); ?></td>
								<td>
									<details>
										<summary><?php esc_html_e( 'View payloads', 'mc-woo-remote-automations' ); ?></summary>
										<strong><?php esc_html_e( 'Request payload', 'mc-woo-remote-automations' ); ?></strong>
										<pre style="white-space:pre-wrap;"><?php echo esc_html( $this->format_log_json_for_display( (string) $log['request_payload'] ) ); ?></pre>
										<strong><?php esc_html_e( 'Response body', 'mc-woo-remote-automations' ); ?></strong>
										<pre style="white-space:pre-wrap;"><?php echo esc_html( $this->format_log_json_for_display( (string) $log['response_body'] ) ); ?></pre>
									</details>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo wp_kses_post(
				paginate_links(
					array(
						'base'      => add_query_arg(
							array(
								'paged'      => '%#%',
								'status'     => $status,
								'action_key' => $action_key,
								's'          => $search,
							),
							admin_url( 'admin.php?page=mc-wra-logs' )
						),
						'format'    => '',
						'current'   => $paged,
						'total'     => $total_pages,
						'type'      => 'plain',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					)
				)
			);
			echo '</div></div>';
			?>

			<hr>
			<h2><?php esc_html_e( 'Log Cleanup Tools', 'mc-woo-remote-automations' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %d: retention days. */
					esc_html__( 'Current retention setting: %d days (0 disables retention-based cleanup).', 'mc-woo-remote-automations' ),
					$retention
				);
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom: 12px;">
				<?php wp_nonce_field( 'mc_wra_logs_cleanup' ); ?>
				<input type="hidden" name="action" value="mc_wra_logs_cleanup">
				<input type="hidden" name="mc_wra_logs_action" value="purge_old">
				<?php submit_button( __( 'Delete logs older than retention', 'mc-woo-remote-automations' ), 'secondary', '', false ); ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'mc_wra_logs_cleanup' ); ?>
				<input type="hidden" name="action" value="mc_wra_logs_cleanup">
				<input type="hidden" name="mc_wra_logs_action" value="clear_all">
				<label for="mc_wra_confirm_clear">
					<input type="checkbox" id="mc_wra_confirm_clear" name="mc_wra_confirm_clear" value="yes">
					<?php esc_html_e( 'I understand this will permanently delete all execution logs.', 'mc-woo-remote-automations' ); ?>
				</label>
				<p><?php submit_button( __( 'Clear all logs', 'mc-woo-remote-automations' ), 'delete', '', false ); ?></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Formats a post reference used in log rows.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $type    Human label (automation|connection).
	 * @return string
	 */
	private function format_log_object_label( $post_id, $type ) {
		if ( $post_id <= 0 ) {
			return '—';
		}
		$title = get_the_title( $post_id );
		if ( ! $title ) {
			return sprintf( /* translators: 1: item type, 2: numeric ID. */ __( '%1$s #%2$d (deleted)', 'mc-woo-remote-automations' ), ucfirst( $type ), $post_id );
		}
		return sprintf( '%1$s (#%2$d)', $title, $post_id );
	}

	/**
	 * Formats raw log payload text for safe admin display.
	 *
	 * @param string $value Raw payload text.
	 * @return string
	 */
	private function format_log_json_for_display( $value ) {
		$decoded = json_decode( (string) $value, true );
		if ( JSON_ERROR_NONE === json_last_error() ) {
			$redacted = MC_Woo_Remote_Helpers::redact_sensitive_data( $decoded );
			$json     = wp_json_encode( $redacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			return (string) $json;
		}
		return (string) MC_Woo_Remote_Helpers::redact_sensitive_data( (string) $value );
	}

	/**
	 * Renders the Settings admin page.
	 */
	public function render_settings_page() {
		$api_secret = get_option( 'mc_wra_api_secret', '' );
		if ( '' === $api_secret ) {
			$api_secret = wp_generate_password( 32, true, true );
			update_option( 'mc_wra_api_secret', $api_secret );
		}
		$mode = get_option( 'mc_wra_operating_mode', 'both' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Settings', 'mc-woo-remote-automations' ); ?></h1>
			<div class="notice notice-info">
				<h2><?php esc_html_e( 'Single Plugin Setup', 'mc-woo-remote-automations' ); ?></h2>
				<p><?php esc_html_e( 'Install this same plugin on both the WooCommerce source site and the destination site. Use Controller mode on the WooCommerce site and Remote API mode on the destination site, or Both when one site must perform both roles.', 'mc-woo-remote-automations' ); ?></p>
				<p><?php esc_html_e( 'Remote API endpoints exposed by this plugin:', 'mc-woo-remote-automations' ); ?></p>
				<ul style="list-style:disc;margin-left:20px;">
					<li><code>/wp-json/mc/v1/ping</code></li>
					<li><code>/wp-json/mc/v1/create-user</code></li>
					<li><code>/wp-json/mc/v1/assign-role</code></li>
				</ul>
			</div>
			<form method="post" action="options.php">
				<?php settings_fields( 'mc_wra_settings' ); ?>
				<table class="form-table">

					<tr>
						<th><label for="mc_wra_operating_mode"><?php esc_html_e( 'Operating mode', 'mc-woo-remote-automations' ); ?></label></th>
						<td>
							<select id="mc_wra_operating_mode" name="mc_wra_operating_mode">
								<option value="controller" <?php selected( $mode, 'controller' ); ?>><?php esc_html_e( 'Controller / Source site', 'mc-woo-remote-automations' ); ?></option>
								<option value="remote" <?php selected( $mode, 'remote' ); ?>><?php esc_html_e( 'Remote API / Destination site', 'mc-woo-remote-automations' ); ?></option>
								<option value="both" <?php selected( $mode, 'both' ); ?>><?php esc_html_e( 'Both', 'mc-woo-remote-automations' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Controller mode runs WooCommerce-triggered automations only. Remote mode exposes remote API endpoints only. Both enables both behaviors.', 'mc-woo-remote-automations' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="mc_wra_api_secret"><?php esc_html_e( 'Remote API Secret', 'mc-woo-remote-automations' ); ?></label></th>
						<td>
							<input type="password" class="regular-text" id="mc_wra_api_secret" name="mc_wra_api_secret" value="<?php echo esc_attr( $api_secret ); ?>">
							<button type="button" class="button" id="mc_wra_api_secret_toggle" data-mc-wra-toggle="mc_wra_api_secret" style="margin-left:8px;"><?php esc_html_e( 'Show', 'mc-woo-remote-automations' ); ?></button>
							<p class="description"><?php esc_html_e( 'Copy this secret into the Connection settings on the Controller site. It is checked against the X-MC-SECRET request header.', 'mc-woo-remote-automations' ); ?></p>
						</td>
					</tr>

					<tr>
						<th><label for="mc_wra_default_timeout"><?php esc_html_e( 'Default timeout (seconds)', 'mc-woo-remote-automations' ); ?></label></th>
						<td><input type="number" min="1" id="mc_wra_default_timeout" name="mc_wra_default_timeout" value="<?php echo esc_attr( get_option( 'mc_wra_default_timeout', 10 ) ); ?>"></td>
					</tr>
					<tr>
						<th><label for="mc_wra_log_retention_days"><?php esc_html_e( 'Log retention (days)', 'mc-woo-remote-automations' ); ?></label></th>
						<td>
							<input type="number" min="0" id="mc_wra_log_retention_days" name="mc_wra_log_retention_days" value="<?php echo esc_attr( get_option( 'mc_wra_log_retention_days', 90 ) ); ?>">
							<p class="description"><?php esc_html_e( 'Set to 0 to keep logs indefinitely. Use Logs → Cleanup to purge records older than this retention period.', 'mc-woo-remote-automations' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="mc_wra_delete_data_on_uninstall"><?php esc_html_e( 'Delete data on uninstall', 'mc-woo-remote-automations' ); ?></label></th>
						<td>
							<label>
								<input type="hidden" name="mc_wra_delete_data_on_uninstall" value="no">
								<input type="checkbox" id="mc_wra_delete_data_on_uninstall" name="mc_wra_delete_data_on_uninstall" value="yes" <?php checked( get_option( 'mc_wra_delete_data_on_uninstall', 'no' ), 'yes' ); ?>>
								<?php esc_html_e( 'When enabled, uninstall removes plugin logs, connections, automations, and plugin settings.', 'mc-woo-remote-automations' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
