# MC Remote API – Integration Examples

## PHP (cURL)

```php
<?php
/**
 * MC Remote API – PHP cURL integration helper.
 */

define( 'MC_API_BASE_URL', 'https://your-site.com/wp-json/mc/v1' );
define( 'MC_API_SECRET',   'your-secret-here' );

/**
 * Sends a request to MC Remote API.
 *
 * @param string $endpoint e.g. '/ping', '/create-user', '/assign-role'
 * @param string $method   'GET' or 'POST'
 * @param array  $payload  Body data (for POST requests)
 *
 * @return array Decoded JSON response.
 */
function mc_api_request( string $endpoint, string $method = 'GET', array $payload = [] ): array {
    $ch = curl_init( MC_API_BASE_URL . $endpoint );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, [
        'X-MC-SECRET: ' . MC_API_SECRET,
        'Content-Type: application/json',
        'Accept: application/json',
    ] );
    if ( 'POST' === $method ) {
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );
    }
    $response = curl_exec( $ch );
    curl_close( $ch );
    return json_decode( $response, true ) ?? [];
}

// Test connection
$ping = mc_api_request( '/ping' );
echo $ping['success'] ? "Connected!\n" : "Connection failed!\n";

// Create a user
$result = mc_api_request( '/create-user', 'POST', [
    'user_email' => 'jane@example.com',
    'first_name' => 'Jane',
    'last_name'  => 'Smith',
    'role'       => 'customer',
] );

if ( $result['success'] ) {
    echo 'User ID: ' . $result['user_id'] . "\n";
} else {
    echo 'Error: ' . $result['message'] . "\n";
}

// Assign a role
$result = mc_api_request( '/assign-role', 'POST', [
    'email' => 'jane@example.com',
    'role'  => 'editor',
] );
echo $result['success'] ? "Role assigned!\n" : 'Error: ' . $result['message'] . "\n";
```

---

## JavaScript (Fetch API)

```js
const MC_API_BASE = 'https://your-site.com/wp-json/mc/v1';
const MC_SECRET   = 'your-secret-here';

async function mcApiRequest(endpoint, method = 'GET', payload = null) {
  const options = {
    method,
    headers: {
      'X-MC-SECRET':  MC_SECRET,
      'Content-Type': 'application/json',
      'Accept':       'application/json',
    },
  };
  if (payload) {
    options.body = JSON.stringify(payload);
  }
  const response = await fetch(MC_API_BASE + endpoint, options);
  return response.json();
}

// Test connection
const ping = await mcApiRequest('/ping');
console.log(ping.success ? 'Connected!' : 'Failed!');

// Create user
const user = await mcApiRequest('/create-user', 'POST', {
  user_email: 'john@example.com',
  first_name: 'John',
  last_name:  'Doe',
  role:       'customer',
});
console.log('User ID:', user.user_id);

// Assign role
const role = await mcApiRequest('/assign-role', 'POST', {
  email: 'john@example.com',
  role:  'editor',
});
console.log('Result:', role.success ? 'Role assigned' : role.message);
```

---

## Using with MC-Woo Remote Automations

1. Install **MC Remote API** on the target WordPress site.
2. Copy the API secret from **Settings → MC Remote API**.
3. On the WooCommerce site, install **MC-Woo Remote Automations**.
4. Go to **MC Automations → Connections → Add Connection**.
5. Enter the target site URL and paste the secret into the Connection fields.
6. Create an Automation that maps an order status and product to the connection.

When an order reaches the configured status, the plugin will automatically call `/create-user` and/or `/assign-role` on the remote site.

---

## Custom Automation Script

```php
<?php
/**
 * Standalone automation script – call after WooCommerce order completion.
 * Requires MC Remote API on the target WordPress site.
 */

$order_email = 'customer@example.com';
$first_name  = 'Alice';
$last_name   = 'Johnson';

// Step 1: ensure user exists
$create = mc_api_request( '/create-user', 'POST', [
    'user_email' => $order_email,
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'role'       => 'customer',
] );

if ( ! $create['success'] ) {
    error_log( '[MC API] Could not create user: ' . $create['message'] );
    exit( 1 );
}

// Step 2: upgrade role after purchase
$assign = mc_api_request( '/assign-role', 'POST', [
    'email' => $order_email,
    'role'  => 'premium_member',
] );

if ( ! $assign['success'] ) {
    error_log( '[MC API] Could not assign role: ' . $assign['message'] );
    exit( 1 );
}

echo "Automation completed for {$order_email}\n";
```

---

## Webhook Integration

You can call MC Remote API from any webhook-capable service (Zapier, Make, n8n, etc.):

**n8n HTTP Request node settings:**
- Method: `POST`
- URL: `https://your-site.com/wp-json/mc/v1/create-user`
- Headers: `X-MC-SECRET` = `your-secret`
- Body: JSON with `user_email`, `first_name`, `last_name`, `role`
