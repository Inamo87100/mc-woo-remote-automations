<?php
/**
 * Plugin Name: MC-Woo Remote Automations
 * Description: Automate remote user creation and role assignment from WooCommerce orders.
 * Version: 1.1.3
 * Author: Mamba Coding
 * Text Domain: mc-woo-remote-automations
 */
if (!defined('ABSPATH')) exit;

// ============================================
// ADMIN BANNER CLASS
// ============================================
class MC_Woo_Remote_Banner {

    public static function init() {
        add_action('admin_notices', [__CLASS__, 'render']);
    }

    protected static function is_plugin_screen() {
        if (!function_exists('get_current_screen')) return false;
        $screen = get_current_screen();
        if (!$screen) return false;

        $screen_id = isset($screen->id) ? (string) $screen->id : '';
        $post_type = isset($screen->post_type) ? (string) $screen->post_type : '';

        $allowed = [
            'edit-mcwra_automation',
            'mcwra_automation',
            'edit-mcwra_connection',
            'mcwra_connection',
            'settings_page_mc-wra-settings',
            'woocommerce_page_mc-wra-logs',
            'woocommerce_page_mc-wra-settings',
        ];

        return in_array($screen_id, $allowed, true) || $post_type === 'mcwra_automation' || $post_type === 'mcwra_connection';
    }

    protected static function premium_active() {
        return defined('MC_WOO_REMOTE_PREMIUM_VERSION') || class_exists('MC_Woo_Remote_Premium');
    }

    public static function render() {
        if (!current_user_can('manage_woocommerce')) return;
        if (!self::is_plugin_screen()) return;

        $is_premium = self::premium_active();
        $logo_url = plugin_dir_url(__FILE__) . 'assets/images/mamba-logo.png';
        $target = 'https://mambacoding.com/';

        $headline = $is_premium
            ? __('Powered by Mamba Coding', 'mc-woo-remote-automations')
            : __('Upgrade to Premium and unlock advanced automation features', 'mc-woo-remote-automations');

        $text = $is_premium
            ? __('Discover more tools, updates and WordPress solutions on Mamba Coding.', 'mc-woo-remote-automations')
            : __('Get advanced automation workflows, priority support, and exclusive features designed to save you time and boost productivity.', 'mc-woo-remote-automations');

        $button = $is_premium
            ? __('Visit Mamba Coding', 'mc-woo-remote-automations')
            : __('Buy Premium now', 'mc-woo-remote-automations');

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
class MC_Woo_Remote_Automations {
    const LOG_TABLE = 'mc_wra_logs';
    
    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('init', [$this, 'register_post_types']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_mcwra_connection', [$this, 'save_connection'], 10, 2);
        add_action('save_post_mcwra_automation', [$this, 'save_automation'], 10, 2);
        add_action('admin_post_mc_wra_test_connection', [$this, 'handle_test_connection']);
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 4);
        add_filter('manage_mcwra_automation_posts_columns', [$this, 'automation_columns']);
        add_action('manage_mcwra_automation_posts_custom_column', [$this, 'automation_column_content'], 10, 2);
        add_filter('enter_title_here', [$this, 'custom_title_placeholder']);
        
        MC_Woo_Remote_Banner::init();
    }
    
    public function activate() {
        global $wpdb;
        $table = $wpdb->prefix . self::LOG_TABLE;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table} (
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
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    public function register_post_types() {
        register_post_type('mcwra_connection', [
            'labels' => [
                'name'=>'Connections','singular_name'=>'Connection','menu_name'=>'Connections','name_admin_bar'=>'Connection',
                'add_new'=>'Add Connection','add_new_item'=>'Add Connection','new_item'=>'New Connection','edit_item'=>'Edit Connection',
                'view_item'=>'View Connection','all_items'=>'Connections','search_items'=>'Search Connections',
                'not_found'=>'No connections found','not_found_in_trash'=>'No connections found in trash'
            ],
            'public'=>false,'show_ui'=>true,'show_in_menu'=>false,'supports'=>['title']
        ]);
        register_post_type('mcwra_automation', [
            'labels' => [
                'name'=>'Automations','singular_name'=>'Automation','menu_name'=>'Automations','name_admin_bar'=>'Automation',
                'add_new'=>'Add Automation','add_new_item'=>'Add Automation','new_item'=>'New Automation','edit_item'=>'Edit Automation',
                'view_item'=>'View Automation','all_items'=>'Automations','search_items'=>'Search Automations',
                'not_found'=>'No automations found','not_found_in_trash'=>'No automations found in trash'
            ],
            'public'=>false,'show_ui'=>true,'show_in_menu'=>false,'supports'=>['title']
        ]);
    }
    
    public function custom_title_placeholder($title) {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen) return $title;
        if ($screen->post_type === 'mcwra_automation') return 'Automation Name';
        if ($screen->post_type === 'mcwra_connection') return 'Connection Name';
        return $title;
    }
    
    public function admin_menu() {
        $parent_slug='edit.php?post_type=mcwra_automation';
        add_menu_page('Woo Remote Automations','Woo Remote Automations','manage_woocommerce',$parent_slug,'','dashicons-randomize',56);
        add_submenu_page($parent_slug,'Automations','Automations','manage_woocommerce',$parent_slug);
        add_submenu_page($parent_slug,'Connections','Connections','manage_woocommerce','edit.php?post_type=mcwra_connection');
        add_submenu_page($parent_slug,'Logs','Logs','manage_woocommerce','mc-wra-logs',[$this,'render_logs_page']);
        add_submenu_page($parent_slug,'Settings','Settings','manage_woocommerce','mc-wra-settings',[$this,'render_settings_page']);
    }
    
    public function register_settings() {
        register_setting('mc_wra_settings','mc_wra_default_timeout',['type'=>'integer','sanitize_callback'=>function($v){return max(1,intval($v));},'default'=>10]);
    }
    
    public function add_meta_boxes() {
        add_meta_box('mc_wra_connection_box','Connection Settings',[$this,'render_connection_box'],'mcwra_connection','normal','default');
        add_meta_box('mc_wra_automation_box','Automation Settings',[$this,'render_automation_box'],'mcwra_automation','normal','default');
    }
    
    public function render_connection_box($post) {
        wp_nonce_field('mc_wra_save_connection','mc_wra_connection_nonce');
        $enabled=get_post_meta($post->ID,'_mc_enabled',true);
        $base_url=get_post_meta($post->ID,'_mc_base_url',true);
        $create_endpoint=get_post_meta($post->ID,'_mc_create_endpoint',true) ?: '/wp-json/mc/v1/create-user';
        $role_endpoint=get_post_meta($post->ID,'_mc_role_endpoint',true) ?: '/wp-json/mc/v1/assign-role';
        $ping_endpoint=get_post_meta($post->ID,'_mc_ping_endpoint',true) ?: '/wp-json/mc/v1/ping';
        $create_secret=get_post_meta($post->ID,'_mc_create_secret',true);
        $role_secret=get_post_meta($post->ID,'_mc_role_secret',true); ?>
        <table class="form-table">
        <tr><th><label for="mc_enabled">Enabled</label></th><td><input type="checkbox" id="mc_enabled" name="mc_enabled" value="yes" <?php checked($enabled,'yes'); ?>></td></tr>
        <tr><th><label for="mc_base_url">Remote Site URL</label></th><td><input type="url" class="regular-text" id="mc_base_url" name="mc_base_url" value="<?php echo esc_attr($base_url); ?>" placeholder="https://example.com"></td></tr>
        <tr><th><label for="mc_create_endpoint">Create User Endpoint</label></th><td><input type="text" class="regular-text" id="mc_create_endpoint" name="mc_create_endpoint" value="<?php echo esc_attr($create_endpoint); ?>"></td></tr>
        <tr><th><label for="mc_role_endpoint">Assign Role Endpoint</label></th><td><input type="text" class="regular-text" id="mc_role_endpoint" name="mc_role_endpoint" value="<?php echo esc_attr($role_endpoint); ?>"></td></tr>
        <tr><th><label for="mc_ping_endpoint">Ping Endpoint</label></th><td><input type="text" class="regular-text" id="mc_ping_endpoint" name="mc_ping_endpoint" value="<?php echo esc_attr($ping_endpoint); ?>"></td></tr>
        <tr><th><label for="mc_create_secret">Create Secret</label></th><td><input type="password" class="regular-text" id="mc_create_secret" name="mc_create_secret" value="<?php echo esc_attr($create_secret); ?>"></td></tr>
        <tr><th><label for="mc_role_secret">Role Secret</label></th><td><input type="password" class="regular-text" id="mc_role_secret" name="mc_role_secret" value="<?php echo esc_attr($role_secret); ?>"></td></tr>
        </table>
        <?php if ($post->ID) { $url=wp_nonce_url(admin_url('admin-post.php?action=mc_wra_test_connection&connection_id='.$post->ID),'mc_wra_test_connection_'.$post->ID); echo '<p><a class="button button-secondary" href="'.esc_url($url).'">Test Connection</a></p>'; }
    }
    
    public function render_automation_box($post) {
        wp_nonce_field('mc_wra_save_automation','mc_wra_automation_nonce');
        $enabled=get_post_meta($post->ID,'_mc_enabled',true);
        $order_status=get_post_meta($post->ID,'_mc_order_status',true) ?: 'completed';
        $product_ids=get_post_meta($post->ID,'_mc_product_ids',true); if(!is_array($product_ids)) $product_ids=[];
        $connection_id=intval(get_post_meta($post->ID,'_mc_connection_id',true));
        $create_if_missing=get_post_meta($post->ID,'_mc_create_if_missing',true);
        $assign_role=get_post_meta($post->ID,'_mc_assign_role',true);
        $remote_role=get_post_meta($post->ID,'_mc_remote_role',true);
        $timeout=intval(get_post_meta($post->ID,'_mc_timeout',true));
        $statuses=function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
        $products=get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>300,'orderby'=>'title','order'=>'ASC']);
        $connections=get_posts(['post_type'=>'mcwra_connection','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC']); ?>
        <table class="form-table">
        <tr><th><label for="mc_enabled_auto">Enabled</label></th><td><input type="checkbox" id="mc_enabled_auto" name="mc_enabled" value="yes" <?php checked($enabled,'yes'); ?>></td></tr>
        <tr><th><label for="mc_order_status">Order Status Trigger</label></th><td><select id="mc_order_status" name="mc_order_status"><?php foreach($statuses as $key=>$label): $slug=str_replace('wc-','',$key); ?><option value="<?php echo esc_attr($slug); ?>" <?php selected($order_status,$slug); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
        <tr><th><label for="mc_product_ids">Products</label></th><td><select id="mc_product_ids" name="mc_product_ids[]" multiple size="12" style="min-width:420px;"><?php foreach($products as $product): ?><option value="<?php echo esc_attr($product->ID); ?>" <?php echo in_array($product->ID,array_map('intval',$product_ids),true)?'selected':''; ?>><?php echo esc_html($product->post_title.' (#'.$product->ID.')'); ?></option><?php endforeach; ?></select><p class="description">Hold Ctrl or Cmd to select multiple products.</p></td></tr>
        <tr><th><label for="mc_connection_id">Connection</label></th><td><select id="mc_connection_id" name="mc_connection_id"><option value="">Select a connection</option><?php foreach($connections as $connection): ?><option value="<?php echo esc_attr($connection->ID); ?>" <?php selected($connection_id,$connection->ID); ?>><?php echo esc_html($connection->post_title.' (#'.$connection->ID.')'); ?></option><?php endforeach; ?></select></td></tr>
        <tr><th><label for="mc_create_if_missing">Create User If Missing</label></th><td><input type="checkbox" id="mc_create_if_missing" name="mc_create_if_missing" value="yes" <?php checked($create_if_missing,'yes'); ?>></td></tr>
        <tr><th><label for="mc_assign_role">Assign Role</label></th><td><input type="checkbox" id="mc_assign_role" name="mc_assign_role" value="yes" <?php checked($assign_role,'yes'); ?>></td></tr>
        <tr><th><label for="mc_remote_role">Remote Role</label></th><td><input type="text" class="regular-text" id="mc_remote_role" name="mc_remote_role" value="<?php echo esc_attr($remote_role); ?>" placeholder="student"></td></tr>
        <tr><th><label for="mc_timeout">Override Timeout (seconds)</label></th><td><input type="number" min="1" id="mc_timeout" name="mc_timeout" value="<?php echo esc_attr($timeout ?: ''); ?>" placeholder="Use global default"></td></tr>
        </table><?php
    }
    
    public function save_connection($post_id,$post) {
        if(!isset($_POST['mc_wra_connection_nonce']) || !wp_verify_nonce($_POST['mc_wra_connection_nonce'],'mc_wra_save_connection')) return;
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if(!current_user_can('manage_woocommerce')) return;
        update_post_meta($post_id,'_mc_enabled',isset($_POST['mc_enabled'])?'yes':'no');
        update_post_meta($post_id,'_mc_base_url',esc_url_raw(wp_unslash($_POST['mc_base_url'] ?? '')));
        update_post_meta($post_id,'_mc_create_endpoint',sanitize_text_field(wp_unslash($_POST['mc_create_endpoint'] ?? '')));
        update_post_meta($post_id,'_mc_role_endpoint',sanitize_text_field(wp_unslash($_POST['mc_role_endpoint'] ?? '')));
        update_post_meta($post_id,'_mc_ping_endpoint',sanitize_text_field(wp_unslash($_POST['mc_ping_endpoint'] ?? '')));
        update_post_meta($post_id,'_mc_create_secret',sanitize_text_field(wp_unslash($_POST['mc_create_secret'] ?? '')));
        update_post_meta($post_id,'_mc_role_secret',sanitize_text_field(wp_unslash($_POST['mc_role_secret'] ?? '')));
    }
    
    public function save_automation($post_id,$post) {
        if(!isset($_POST['mc_wra_automation_nonce']) || !wp_verify_nonce($_POST['mc_wra_automation_nonce'],'mc_wra_save_automation')) return;
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if(!current_user_can('manage_woocommerce')) return;
        update_post_meta($post_id,'_mc_enabled',isset($_POST['mc_enabled'])?'yes':'no');
        update_post_meta($post_id,'_mc_order_status',sanitize_text_field(wp_unslash($_POST['mc_order_status'] ?? 'completed')));
        update_post_meta($post_id,'_mc_product_ids',array_map('intval',(array)($_POST['mc_product_ids'] ?? [])));
        update_post_meta($post_id,'_mc_connection_id',intval($_POST['mc_connection_id'] ?? 0));
        update_post_meta($post_id,'_mc_create_if_missing',isset($_POST['mc_create_if_missing'])?'yes':'no');
        update_post_meta($post_id,'_mc_assign_role',isset($_POST['mc_assign_role'])?'yes':'no');
        update_post_meta($post_id,'_mc_remote_role',sanitize_text_field(wp_unslash($_POST['mc_remote_role'] ?? '')));
        update_post_meta($post_id,'_mc_timeout',max(0,intval($_POST['mc_timeout'] ?? 0)));
    }
    
    public function handle_test_connection() {
        if(!current_user_can('manage_woocommerce')) wp_die('Permission denied');
        $connection_id=intval($_GET['connection_id'] ?? 0);
        check_admin_referer('mc_wra_test_connection_'.$connection_id);
        $base_url=get_post_meta($connection_id,'_mc_base_url',true);
        $ping_endpoint=get_post_meta($connection_id,'_mc_ping_endpoint',true) ?: '/wp-json/mc/v1/ping';
        $secret=get_post_meta($connection_id,'_mc_create_secret',true);
        $timeout=intval(get_option('mc_wra_default_timeout',10));
        $url=$this->build_url($base_url,$ping_endpoint);
        $response=wp_remote_get($url,['timeout'=>$timeout,'headers'=>['X-MC-SECRET'=>$secret]]);
        $redirect=admin_url('post.php?post='.$connection_id.'&action=edit');
        if(is_wp_error($response)) $redirect=add_query_arg(['mc_wra_test'=>'0','mc_wra_msg'=>rawurlencode($response->get_error_message())],$redirect);
        else { $code=wp_remote_retrieve_response_code($response); $body=wp_remote_retrieve_body($response); $ok=($code>=200 && $code<300); $redirect=add_query_arg(['mc_wra_test'=>$ok?'1':'0','mc_wra_msg'=>rawurlencode('HTTP '.$code.' '.$body)],$redirect); }
        wp_safe_redirect($redirect); exit;
    }
    
    private function build_url($base,$endpoint){ return rtrim((string)$base,'/').'/'.ltrim((string)$endpoint,'/'); }
    
    private function get_order_product_ids($order){ $ids=[]; foreach($order->get_items() as $item){ $pid=(int)$item->get_product_id(); $vid=(int)$item->get_variation_id(); if($pid) $ids[]=$pid; if($vid) $ids[]=$vid; } return array_values(array_unique($ids)); }
    
    public function handle_order_status_change($order_id,$old_status,$new_status,$order){
        if(!function_exists('wc_get_order')) return;
        if(!$order instanceof WC_Order) $order=wc_get_order($order_id);
        if(!$order) return;
        $automations=get_posts(['post_type'=>'mcwra_automation','post_status'=>['publish','draft','private'],'posts_per_page'=>-1,'meta_query'=>[['key'=>'_mc_enabled','value'=>'yes']]]);
        $order_product_ids=$this->get_order_product_ids($order);
        $email=sanitize_email($order->get_billing_email());
        $first=sanitize_text_field($order->get_billing_first_name());
        $last=sanitize_text_field($order->get_billing_last_name());
        foreach($automations as $automation){
            $trigger_status=get_post_meta($automation->ID,'_mc_order_status',true);
            if($trigger_status!==$new_status) continue;
            $product_ids=get_post_meta($automation->ID,'_mc_product_ids',true);
            if(!is_array($product_ids) || empty($product_ids)) continue;
            if(empty(array_intersect(array_map('intval',$product_ids),$order_product_ids))) continue;
            $connection_id=intval(get_post_meta($automation->ID,'_mc_connection_id',true));
            if(!$connection_id) continue;
            if(get_post_meta($connection_id,'_mc_enabled',true)!=='yes') continue;
            $base_url=get_post_meta($connection_id,'_mc_base_url',true);
            $create_endpoint=get_post_meta($connection_id,'_mc_create_endpoint',true) ?: '/wp-json/mc/v1/create-user';
            $role_endpoint=get_post_meta($connection_id,'_mc_role_endpoint',true) ?: '/wp-json/mc/v1/assign-role';
            $create_secret=get_post_meta($connection_id,'_mc_create_secret',true);
            $role_secret=get_post_meta($connection_id,'_mc_role_secret',true);
            $timeout=intval(get_post_meta($automation->ID,'_mc_timeout',true)); if(!$timeout) $timeout=intval(get_option('mc_wra_default_timeout',10)); if(!$timeout) $timeout=10;
            if(!$email) continue;
            if(get_post_meta($automation->ID,'_mc_create_if_missing',true)==='yes'){
                $request=['user_email'=>$email,'first_name'=>$first,'last_name'=>$last,'user_pass'=>$email,'role'=>'customer'];
                $response=wp_remote_post($this->build_url($base_url,$create_endpoint),['timeout'=>$timeout,'headers'=>['Content-Type'=>'application/json; charset=utf-8','Accept'=>'application/json','X-MC-SECRET'=>$create_secret],'body'=>wp_json_encode($request)]);
                $this->handle_response_log($automation->ID,$connection_id,$order_id,'create_user',$email,$request,$response,True);
            }
            $remote_role=get_post_meta($automation->ID,'_mc_remote_role',true);
            if(get_post_meta($automation->ID,'_mc_assign_role',true)==='yes' && $remote_role){
                $request=['email'=>$email,'role'=>$remote_role];
                $response=wp_remote_post($this->build_url($base_url,$role_endpoint),['timeout'=>$timeout,'headers'=>['Content-Type'=>'application/json; charset=utf-8','Accept'=>'application/json','X-MC-SECRET'=>($role_secret ?: $create_secret)],'body'=>wp_json_encode($request)]);
                $this->handle_response_log($automation->ID,$connection_id,$order_id,'assign_role',$email,$request,$response,False);
            }
        }
    }
    
    private function handle_response_log($automation_id,$connection_id,$order_id,$action_key,$email,$request,$response,$allow_exists){
        if(is_wp_error($response)){ $this->log_action($automation_id,$connection_id,$order_id,'failed',$action_key,$email,None,$response->get_error_message(),$request,''); return; }
        $code=(int)wp_remote_retrieve_response_code($response); $body=(string)wp_remote_retrieve_body($response);
        $status=($code>=200 && $code<300)?'success':'failed'; $message='HTTP '.$code;
        if($allow_exists){ $body_lc=strtolower($body); if(strpos($body_lc,'user_exists')!==false || strpos($body_lc,'already exists')!==false || $code===409){ $status='success'; $message='Existing user accepted'; } }
        $this->log_action($automation_id,$connection_id,$order_id,$status,$action_key,$email,$code,$message,$request,$body);
    }
    
    private function log_action($automation_id,$connection_id,$order_id,$status,$action_key,$email,$response_code,$message,$request_payload,$response_body){
        global $wpdb; $table=$wpdb->prefix.self::LOG_TABLE;
        $wpdb->insert($table,['created_at'=>current_time('mysql'),'automation_id'=>intval($automation_id),'connection_id'=>intval($connection_id),'order_id'=>intval($order_id),'action_key'=>sanitize_text_field($action_key),'user_email'=>sanitize_email($email),'status'=>sanitize_text_field($status),'response_code'=>is_null($response_code)?null:intval($response_code),'message'=>wp_kses_post((string)$message),'request_payload'=>wp_json_encode($request_payload),'response_body'=>(string)$response_body],['%s','%d','%d','%d','%s','%s','%s','%d','%s','%s','%s']);
    }
    
    public function automation_columns($columns){
        $new=[]; foreach($columns as $key=>$label){ if($key==='title'){ $new[$key]='Automation Name'; continue; } if($key==='date'){ $new['mc_connection']='Connection'; $new['mc_status_trigger']='Status Trigger'; $new['mc_products_count']='Products'; $new['mc_last_run']='Last Run'; } $new[$key]=$label; } return $new;
    }
    
    public function automation_column_content($column,$post_id){
        global $wpdb;
        if($column==='mc_connection'){ $connection_id=intval(get_post_meta($post_id,'_mc_connection_id',true)); echo $connection_id?esc_html(get_the_title($connection_id)):'—'; }
        elseif($column==='mc_status_trigger'){ $status=get_post_meta($post_id,'_mc_order_status',true); echo $status?esc_html($status):'—'; }
        elseif($column==='mc_products_count'){ $product_ids=get_post_meta($post_id,'_mc_product_ids',true); echo is_array($product_ids)?esc_html(count($product_ids)):'0'; }
        elseif($column==='mc_last_run'){ $table=$wpdb->prefix.self::LOG_TABLE; $last=$wpdb->get_var($wpdb->prepare("SELECT created_at FROM {$table} WHERE automation_id = %d ORDER BY id DESC LIMIT 1",$post_id)); echo $last?esc_html($last):'—'; }
    }
    
    public function render_logs_page(){ echo '<div class="wrap"><h1>Execution Logs</h1><p>Logs page available.</p></div>'; }
    
    public function render_settings_page(){ ?>
        <div class="wrap"><h1>Settings</h1><form method="post" action="options.php"><?php settings_fields('mc_wra_settings'); ?><table class="form-table"><tr><th><label for="mc_wra_default_timeout">Default timeout (seconds)</label></th><td><input type="number" min="1" id="mc_wra_default_timeout" name="mc_wra_default_timeout" value="<?php echo esc_attr(get_option('mc_wra_default_timeout',10)); ?>"></td></tr></table><?php submit_button(); ?></form></div>
    <?php }
}

new MC_Woo_Remote_Automations();

add_action('admin_notices', function(){ 
    if(!empty($_GET['mc_wra_test']) && !empty($_GET['mc_wra_msg'])){ 
        $class=$_GET['mc_wra_test']==='1'?'notice notice-success':'notice notice-error'; 
        echo '<div class="'.esc_attr($class).'"><p>'.esc_html(rawurldecode($_GET['mc_wra_msg'])).'</p></div>'; 
    } 
});
?>