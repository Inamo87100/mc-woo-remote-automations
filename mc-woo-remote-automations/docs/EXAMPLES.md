# MC-Woo Remote Automations – Integration Examples

## Basic Setup via PHP (WP-CLI / Custom Script)

```php
<?php
/**
 * Programmatically create a Connection and Automation via WP post meta.
 * Run via WP-CLI: wp eval-file setup-automation.php
 */

// Create a Connection post
$connection_id = wp_insert_post( [
    'post_type'   => 'mcwra_connection',
    'post_status' => 'publish',
    'post_title'  => 'Remote Learning Site',
] );

update_post_meta( $connection_id, '_mc_enabled',         'yes' );
update_post_meta( $connection_id, '_mc_base_url',        'https://remote-site.com' );
update_post_meta( $connection_id, '_mc_create_secret',   'your-secret-here' );
update_post_meta( $connection_id, '_mc_create_endpoint', '/wp-json/mc/v1/create-user' );
update_post_meta( $connection_id, '_mc_role_endpoint',   '/wp-json/mc/v1/assign-role' );

// Create an Automation post
$automation_id = wp_insert_post( [
    'post_type'   => 'mcwra_automation',
    'post_status' => 'publish',
    'post_title'  => 'Grant Premium Access on Order Completed',
] );

update_post_meta( $automation_id, '_mc_enabled',          'yes' );
update_post_meta( $automation_id, '_mc_connection_id',    $connection_id );
update_post_meta( $automation_id, '_mc_order_status',     'completed' );
update_post_meta( $automation_id, '_mc_product_ids',      [ 42, 43 ] ); // WooCommerce product IDs
update_post_meta( $automation_id, '_mc_create_if_missing','yes' );
update_post_meta( $automation_id, '_mc_assign_role',      'yes' );
update_post_meta( $automation_id, '_mc_remote_role',      'premium_member' );
update_post_meta( $automation_id, '_mc_timeout',          15 );

echo "Connection #{$connection_id} and Automation #{$automation_id} created.\n";
```

---

## Manually Triggering an Automation (Testing)

```php
<?php
/**
 * Simulate an order status change to test an automation.
 * Run via WP-CLI: wp eval-file test-trigger.php
 */

$order_id = 1001; // Replace with a real order ID
$order    = wc_get_order( $order_id );

if ( $order ) {
    // Trigger the automation hook directly
    do_action(
        'woocommerce_order_status_changed',
        $order_id,
        'processing',
        'completed',
        $order
    );
    echo "Trigger fired for order #{$order_id}\n";
} else {
    echo "Order not found.\n";
}
```

---

## Reading Automation Logs

```php
<?php
/**
 * Query the MC-Woo Remote Automations log table.
 */

global $wpdb;
$table = $wpdb->prefix . 'mc_wra_logs';

$logs = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$table} WHERE order_id = %d ORDER BY created_at DESC LIMIT 10",
        1001
    )
);

foreach ( $logs as $log ) {
    echo sprintf(
        "[%s] %s | %s | %s | HTTP %s\n",
        $log->created_at,
        $log->action_key,
        $log->user_email,
        $log->status,
        $log->response_code
    );
}
```

---

## Integration with Custom WooCommerce Subscription Plugin

```php
<?php
/**
 * Example: Trigger remote role assignment when a subscription is renewed.
 * Add to your theme's functions.php or a custom plugin.
 */

add_action( 'woocommerce_subscription_renewal_payment_complete', function( $subscription ) {
    $order = $subscription->get_last_order( 'all', 'renewal' );
    if ( ! $order ) {
        return;
    }

    // Simulate a status change to trigger MC-Woo Remote Automations
    do_action(
        'woocommerce_order_status_changed',
        $order->get_id(),
        'processing',
        'completed',
        $order
    );
} );
```

---

## REST API Webhook to External Service

Forward MC Remote API calls from an external service (e.g., a CRM) to create users on order events:

```bash
# Test from external service using curl
curl -X POST "https://your-woocommerce-site.com/wp-json/mc/v1/create-user" \
  -H "X-MC-SECRET: your-secret" \
  -H "Content-Type: application/json" \
  -d '{"user_email":"crm-user@example.com","first_name":"CRM","last_name":"User","role":"subscriber"}'
```
