<?php
/**
 * Admin banner class for MC Remote API.
 *
 * @package MC_Remote_API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the Mamba Coding promotional banner in the WordPress admin.
 */
class MC_Remote_API_Banner {

	/**
	 * Registers the admin_notices action hook.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render' ) );
	}

	/**
	 * Returns true when the current admin screen belongs to this plugin.
	 *
	 * @return bool
	 */
	protected static function is_plugin_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		$screen_id = isset( $screen->id ) ? (string) $screen->id : '';

		$allowed = array(
			'settings_page_mc-remote-api',
			'options-general',
		);

		return in_array( $screen_id, $allowed, true );
	}

	/**
	 * Returns true when the premium version of the plugin is active.
	 *
	 * @return bool
	 */
	protected static function premium_active() {
		return defined( 'MC_REMOTE_API_PREMIUM_VERSION' ) || class_exists( 'MC_Remote_API_Premium' );
	}

	/**
	 * Renders the banner HTML.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! self::is_plugin_screen() ) {
			return;
		}

		$is_premium = self::premium_active();
		$logo_url   = MC_REMOTE_API_URL . 'assets/images/mamba-logo.png';
		$target     = 'https://mambacoding.com/';

		$headline = $is_premium
			? __( 'Powered by Mamba Coding', 'mc-remote-api' )
			: __( 'Upgrade to Premium and unlock advanced API features', 'mc-remote-api' );

		$text = $is_premium
			? __( 'Discover more tools, updates and WordPress solutions on Mamba Coding.', 'mc-remote-api' )
			: __( 'Get advanced authentication methods, webhook support, role management, and priority support designed to enhance your API integration.', 'mc-remote-api' );

		$button = $is_premium
			? __( 'Visit Mamba Coding', 'mc-remote-api' )
			: __( 'Buy Premium now', 'mc-remote-api' );

		echo '<div class="notice" style="padding:0;border:none;background:transparent;box-shadow:none;margin:16px 0 18px 0;">';
		echo '<div style="background:linear-gradient(135deg,#31006F 0%,#4a1590 45%,#FDB927 100%);border-radius:18px;padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:24px;box-shadow:0 10px 24px rgba(49,0,111,.18);">';
		echo '<div style="display:flex;align-items:center;gap:20px;min-width:0;">';
		echo '<a href="' . esc_url( $target ) . '" target="_blank" rel="noopener noreferrer" style="display:block;flex:0 0 auto;background:#fff;border-radius:14px;padding:10px 14px;line-height:0;">';
		echo '<img src="' . esc_url( $logo_url ) . '" alt="Mamba Coding" style="display:block;height:64px;max-width:100%;width:auto;">';
		echo '</a>';
		echo '<div style="min-width:0;">';
		echo '<div style="font-size:24px;font-weight:700;line-height:1.2;color:#fff;margin:0 0 6px 0;">' . esc_html( $headline ) . '</div>';
		echo '<div style="font-size:14px;line-height:1.5;color:rgba(255,255,255,.92);max-width:760px;">' . esc_html( $text ) . '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div style="flex:0 0 auto;">';
		echo '<a href="' . esc_url( $target ) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:12px;background:#FDB927;color:#31006F;text-decoration:none;font-size:14px;font-weight:700;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.18);">' . esc_html( $button ) . '</a>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}
