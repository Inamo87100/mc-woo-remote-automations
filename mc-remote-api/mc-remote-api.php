<?php
/**
 * Plugin Name: MC Remote API
 * Description: API endpoints for remote user creation and role assignment.
 * Version: 1.1.0
 * Author: Mamba Coding
 * Text Domain: mc-remote-api
 */
if (!defined('ABSPATH')) exit;

// ============================================
// ADMIN BANNER CLASS
// ============================================
class MC_Remote_API_Banner {

    public static function init() {
        add_action('admin_notices', [__CLASS__, 'render']);
    }

    protected static function is_plugin_screen() {
        if (!function_exists('get_current_screen')) return false;
        $screen = get_current_screen();
        if (!$screen) return false;

        $screen_id = isset($screen->id) ? (string) $screen->id : '';

        $allowed = [
            'settings_page_mc-remote-api',
            'options-general',
        ];

        return in_array($screen_id, $allowed, true);
    }

    protected static function premium_active() {
        return defined('MC_REMOTE_API_PREMIUM_VERSION') || class_exists('MC_Remote_API_Premium');
    }

    public static function render() {
        if (!current_user_can('manage_options')) return;
        if (!self::is_plugin_screen()) return;

        $is_premium = self::premium_active();
        $logo_url = plugin_dir_url(__FILE__) . 'assets/images/mamba-logo.png';
        $target = 'https://mambacoding.com/';

        $headline = $is_premium
            ? __('Powered by Mamba Coding', 'mc-remote-api')
            : __('Upgrade to Premium and unlock advanced API features', 'mc-remote-api');

        $text = $is_premium
            ? __('Discover more tools, updates and WordPress solutions on Mamba Coding.', 'mc-remote-api')
            : __('Get advanced authentication methods, webhook support, role management, and priority support designed to enhance your API integration.', 'mc-remote-api');

        $button = $is_premium
            ? __('Visit Mamba Coding', 'mc-remote-api')
            : __('Buy Premium now', 'mc-remote-api');

        echo '<div class="notice" style="padding:0;border:none;background:transparent;box-shadow:none;margin:16px 0 18px 0;">';
        echo '<div style="background:linear-gradient(135deg,#31006F 0%,#4a1590 45%,#FDB927 100%);border-radius:18px;padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:24px;box-shadow:0 10px 24px rgba(49,0,111,.18);">';
        echo '<div style="display:flex;align-items:center;gap:20px;min-width:0;">';
        echo '<a href="' . esc_url($target) . '" target="_blank" rel="noopener noreferrer" style="display:block;flex:0 0 auto;background:#fff;border-radius:14px;padding:10px 14px;line-height:0;">';
        echo '<img src="' . esc_url($logo_url) . '" alt="Mamba Coding" style="display:block;height:64px;max-width:100%;width:auto;">';
        echo '</a>';
        echo '<div style="min-width:0;">';
        echo '<div style="font-size:24px;font-weight:700;line-height:1.2;color:#fff;margin:0 0 6px 0;">' . esc_html($headline) . '</div>';
        echo '<div style="font-size:14px;line-height:1.5;color:rgba(255,255,255,.92);max-width:760px;">' . esc_html($text) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div style="flex:0 0 auto;">';
        echo '<a href="' . esc_url($target) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:12px;background:#FDB927;color:#31006F;text-decoration:none;font-size:14px;font-weight:700;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.18);">' . esc_html($button) . '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

// ============================================
// MAIN PLUGIN CLASS
// ============================================
class MC_Remote_API {
    
    public function __construct(){
        add_action('rest_api_init', [$this,'mc_routes']);
        add_action('admin_menu', [$this,'mc_menu']);
        add_action('admin_init', [$this,'mc_settings']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        MC_Remote_API_Banner::init();
    }
    
    public function activate() {
        if (!get_option('mc_api_secret')) update_option('mc_api_secret', wp_generate_password(32, true, true));
    }
    
    public function mc_routes(){
        register_rest_route('mc/v1','/create-user',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'mc_create_user']]);
        register_rest_route('mc/v1','/assign-role',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>[$this,'mc_assign_role']]);
        register_rest_route('mc/v1','/ping',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'mc_ping']]);
    }
    
    private function valid($request){
        $secret=(string)get_option('mc_api_secret');
        $header=(string)$request->get_header('X-MC-SECRET');
        return $secret!=='' && hash_equals($secret,$header);
    }
    
    public function mc_ping($request){
        if(!$this->valid($request)) return new WP_REST_Response(['success'=>false,'message'=>'Invalid secret'],401);
        return ['success'=>true,'plugin'=>'MC Remote API'];
    }
    
    public function mc_create_user($request){
        if(!$this->valid($request)) return new WP_REST_Response(['success'=>false,'message'=>'Invalid secret'],401);
        $email=sanitize_email($request['user_email']);
        $first=sanitize_text_field($request['first_name']);
        $last=sanitize_text_field($request['last_name']);
        $role=sanitize_text_field($request['role'] ?: 'customer');
        if(!$email || !is_email($email)) return new WP_REST_Response(['success'=>false,'message'=>'Invalid email'],400);
        if(email_exists($email)){ $user=get_user_by('email',$email); return ['success'=>true,'code'=>'user_exists','user_id'=>$user?$user->ID:0]; }
        $user_id=wp_create_user($email,$email,$email);
        if(is_wp_error($user_id)) return new WP_REST_Response(['success'=>false,'message'=>$user_id->get_error_message()],500);
        wp_update_user(['ID'=>$user_id,'first_name'=>$first,'last_name'=>$last,'role'=>$role]);
        return ['success'=>true,'code'=>'user_created','user_id'=>$user_id];
    }
    
    public function mc_assign_role($request){
        if(!$this->valid($request)) return new WP_REST_Response(['success'=>false,'message'=>'Invalid secret'],401);
        $email=sanitize_email($request['email']);
        $role=sanitize_text_field($request['role']);
        if(!$email || !$role) return new WP_REST_Response(['success'=>false,'message'=>'Missing email or role'],400);
        $user=get_user_by('email',$email);
        if(!$user) return new WP_REST_Response(['success'=>false,'message'=>'User not found'],404);
        $user->set_role($role);
        return ['success'=>true,'code'=>'role_assigned','role'=>$role];
    }
    
    public function mc_menu(){ 
        add_options_page('MC Remote API','MC Remote API','manage_options','mc-remote-api',[$this,'mc_settings_page']);
    }
    
    public function mc_settings(){ 
        register_setting('mc_api_settings','mc_api_secret'); 
    }
    
    public function mc_settings_page(){ ?>
        <div class="wrap"><h1>MC Remote API</h1>
        <form method="post" action="options.php"><?php settings_fields('mc_api_settings'); ?>
        <table class="form-table"><tr><th>API Secret</th><td><input type="text" class="regular-text" name="mc_api_secret" value="<?php echo esc_attr(get_option('mc_api_secret')); ?>"></td></tr></table>
        <?php submit_button(); ?></form>
        <h2>Endpoints</h2><p><code>/wp-json/mc/v1/create-user</code></p><p><code>/wp-json/mc/v1/assign-role</code></p><p><code>/wp-json/mc/v1/ping</code></p></div>
    <?php }
}

new MC_Remote_API();
?>