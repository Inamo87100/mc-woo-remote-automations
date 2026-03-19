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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_mcwra_connection', array( $this, 'save_connection' ), 10, 2 );
		add_action( 'save_post_mcwra_automation', array( $this, 'save_automation' ), 10, 2 );
		add_action( 'admin_post_mc_wra_test_connection', array( $this, 'handle_test_connection' ) );
		add_filter( 'manage_mcwra_automation_posts_columns', array( $this, 'automation_columns' ) );
		add_action( 'manage_mcwra_automation_posts_custom_column', array( $this, 'automation_column_content' ), 10, 2 );
		add_filter( 'enter_title_here', array( $this, 'custom_title_placeholder' ) );
		add_action( 'admin_notices', array( $this, 'render_test_result_notice' ) );
	}

	/**
	 * Registers the admin menu.
	 */
	public function admin_menu() {
		$parent_slug = 'edit.php?post_type=mcwra_automation';
		add_menu_page(
			__( 'Woo Remote Automations', 'mc-woo-remote-automations' ),
			__( 'Woo Remote Automations', 'mc-woo-remote-automations' ),
			'manage_woocommerce',
			$parent_slug,
			'',
			'dashicons-randomize',
			56
		);
		add_submenu_page( $parent_slug, __( 'Automations', 'mc-woo-remote-automations' ), __( 'Automations', 'mc-woo-remote-automations' ), 'manage_woocommerce', $parent_slug );
		add_submenu_page( $parent_slug, __( 'Connections', 'mc-woo-remote-automations' ), __( 'Connections', 'mc-woo-remote-automations' ), 'manage_woocommerce', 'edit.php?post_type=mcwra_connection' );
		add_submenu_page( $parent_slug, __( 'Logs', 'mc-woo-remote-automations' ), __( 'Logs', 'mc-woo-remote-automations' ), 'manage_woocommerce', 'mc-wra-logs', array( $this, 'render_logs_page' ) );
		add_submenu_page( $parent_slug, __( 'Settings', 'mc-woo-remote-automations' ), __( 'Settings', 'mc-woo-remote-automations' ), 'manage_woocommerce', 'mc-wra-settings', array( $this, 'render_settings_page' ) );
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
		$create_secret   = get_post_meta( $post->ID, '_mc_create_secret', true );
		$role_secret     = get_post_meta( $post->ID, '_mc_role_secret', true );
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
				<th><label for="mc_create_secret"><?php esc_html_e( 'Create Secret', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="password" class="regular-text" id="mc_create_secret" name="mc_create_secret" value="<?php echo esc_attr( $create_secret ); ?>"></td>
			</tr>
			<tr>
				<th><label for="mc_role_secret"><?php esc_html_e( 'Role Secret', 'mc-woo-remote-automations' ); ?></label></th>
				<td><input type="password" class="regular-text" id="mc_role_secret" name="mc_role_secret" value="<?php echo esc_attr( $role_secret ); ?>"></td>
			</tr>
		</table>
		<?php
		if ( $post->ID ) {
			$url = wp_nonce_url(
				admin_url( 'admin-post.php?action=mc_wra_test_connection&connection_id=' . $post->ID ),
				'mc_wra_test_connection_' . $post->ID
			);
			echo '<p><a class="button button-secondary" href="' . esc_url( $url ) . '">' . esc_html__( 'Test Connection', 'mc-woo-remote-automations' ) . '</a></p>';
		}
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
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		update_post_meta( $post_id, '_mc_enabled', isset( $_POST['mc_enabled'] ) ? 'yes' : 'no' );
		update_post_meta( $post_id, '_mc_base_url', esc_url_raw( wp_unslash( $_POST['mc_base_url'] ?? '' ) ) );
		update_post_meta( $post_id, '_mc_create_endpoint', sanitize_text_field( wp_unslash( $_POST['mc_create_endpoint'] ?? '' ) ) );
		update_post_meta( $post_id, '_mc_role_endpoint', sanitize_text_field( wp_unslash( $_POST['mc_role_endpoint'] ?? '' ) ) );
		update_post_meta( $post_id, '_mc_ping_endpoint', sanitize_text_field( wp_unslash( $_POST['mc_ping_endpoint'] ?? '' ) ) );
		update_post_meta( $post_id, '_mc_create_secret', sanitize_text_field( wp_unslash( $_POST['mc_create_secret'] ?? '' ) ) );
		update_post_meta( $post_id, '_mc_role_secret', sanitize_text_field( wp_unslash( $_POST['mc_role_secret'] ?? '' ) ) );
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
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
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
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permission denied', 'mc-woo-remote-automations' ) );
		}
		$connection_id = intval( $_GET['connection_id'] ?? 0 );
		check_admin_referer( 'mc_wra_test_connection_' . $connection_id );

		$base_url      = get_post_meta( $connection_id, '_mc_base_url', true );
		$ping_endpoint = get_post_meta( $connection_id, '_mc_ping_endpoint', true ) ?: '/wp-json/mc/v1/ping';
		$secret        = get_post_meta( $connection_id, '_mc_create_secret', true );
		$timeout       = intval( get_option( 'mc_wra_default_timeout', 10 ) );
		$url           = mc_wra_build_url( $base_url, $ping_endpoint );

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => $timeout,
				'headers' => array( 'X-MC-SECRET' => $secret ),
			)
		);

		$redirect = admin_url( 'post.php?post=' . $connection_id . '&action=edit' );
		if ( is_wp_error( $response ) ) {
			$redirect = add_query_arg(
				array(
					'mc_wra_test' => '0',
					'mc_wra_msg'  => rawurlencode( $response->get_error_message() ),
				),
				$redirect
			);
		} else {
			$code         = wp_remote_retrieve_response_code( $response );
			$body         = wp_remote_retrieve_body( $response );
			$body_preview = substr( (string) $body, 0, 200 );
			$ok           = ( $code >= 200 && $code < 300 );
			$redirect     = add_query_arg(
				array(
					'mc_wra_test' => $ok ? '1' : '0',
					'mc_wra_msg'  => rawurlencode( 'HTTP ' . $code . ' ' . $body_preview ),
				),
				$redirect
			);
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
			$table = $wpdb->prefix . MC_Woo_Remote_Helpers::LOG_TABLE;
			$last  = $wpdb->get_var( $wpdb->prepare( "SELECT created_at FROM {$table} WHERE automation_id = %d ORDER BY id DESC LIMIT 1", $post_id ) );
			echo $last ? esc_html( $last ) : '&mdash;';
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
		echo '<div class="wrap"><h1>' . esc_html__( 'Execution Logs', 'mc-woo-remote-automations' ) . '</h1><p>' . esc_html__( 'Logs page available.', 'mc-woo-remote-automations' ) . '</p></div>';
	}

	/**
	 * Renders the Settings admin page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Settings', 'mc-woo-remote-automations' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'mc_wra_settings' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="mc_wra_default_timeout"><?php esc_html_e( 'Default timeout (seconds)', 'mc-woo-remote-automations' ); ?></label></th>
						<td><input type="number" min="1" id="mc_wra_default_timeout" name="mc_wra_default_timeout" value="<?php echo esc_attr( get_option( 'mc_wra_default_timeout', 10 ) ); ?>"></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
