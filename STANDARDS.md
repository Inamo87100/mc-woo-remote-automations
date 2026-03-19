# WordPress Coding Standards Documentation

This document describes the coding standards and conventions used across the MC Plugins suite.

---

## Naming Conventions

### PHP

| Type | Convention | Example |
|------|-----------|---------|
| Classes | `PascalCase` with plugin prefix | `MC_Remote_API_Main` |
| Functions | `snake_case` with plugin prefix | `mc_wra_build_url()` |
| Constants | `UPPER_SNAKE_CASE` | `MC_REMOTE_API_VERSION` |
| Variables | `snake_case` | `$user_email` |
| Hooks/Filters | `snake_case` with plugin prefix | `mc_wra_log_action` |

### Files

| Type | Convention | Example |
|------|-----------|---------|
| Class files | `class-{classname}.php` | `class-main.php` |
| Function files | `functions.php` | `functions.php` |
| Template files | `{template-name}.php` | `admin-page.php` |

### Database

| Type | Convention | Example |
|------|-----------|---------|
| Table names | `{wp_prefix}{plugin_prefix}_{table}` | `wp_mc_wra_logs` |
| Option names | `{plugin_prefix}_{option}` | `mc_api_secret` |
| Post meta keys | `_{plugin_prefix}_{key}` | `_mc_enabled` |

---

## Security Standards Applied

### Input Validation and Sanitisation

All user-supplied data is validated and sanitised at the entry point:

```php
// Email
$email = sanitize_email( $request['user_email'] );
if ( ! is_email( $email ) ) {
    return new WP_REST_Response( array( 'success' => false, 'message' => 'Invalid email' ), 400 );
}

// Text
$role = sanitize_text_field( $request['role'] );

// Integer
$timeout = intval( get_post_meta( $automation->ID, '_mc_timeout', true ) );
```

### Output Escaping

All output is escaped before rendering:

```php
echo esc_html__( 'MC Remote API', 'mc-remote-api' );
echo esc_attr( get_option( 'mc_api_secret' ) );
```

### Database Queries

All dynamic database queries use `$wpdb->prepare()` or `$wpdb->insert()`:

```php
$wpdb->get_row(
    $wpdb->prepare( "SELECT * FROM {$table} WHERE order_id = %d", $order_id )
);
```

### Authentication

API endpoints validate the shared secret using constant-time comparison:

```php
hash_equals( $stored_secret, $provided_secret )
```

---

## File Organisation Rationale

```
plugin-name/
├── plugin-name.php          # Plugin header and bootstrap (minimal logic)
├── includes/
│   ├── class-main.php       # Core plugin class (hooks, routing)
│   ├── class-helpers.php    # Utility/helper class (reusable logic)
│   └── functions.php        # Global helper functions
├── admin/
│   └── class-admin.php      # Admin-only functionality
├── assets/
│   ├── icon.svg             # Plugin icon (128×128)
│   ├── banner.svg           # Marketplace banner (772×250)
│   └── screenshots/         # Screenshot images
├── docs/                    # Developer and user documentation
├── tests/                   # PHPUnit test files
└── languages/               # Translation files (.pot, .po, .mo)
```

**Rationale:**
- `includes/` separates business logic from the plugin entry point
- `admin/` isolates admin-only code, not loaded on frontend requests
- `assets/` groups all static assets for easy identification
- `docs/` makes documentation discoverable without browsing source

---

## Performance Considerations

- Plugin classes are loaded only once via `require_once`.
- Admin-only code is loaded only when `is_admin()` or on admin hooks.
- Database queries in `handle_order_status_change` use targeted meta queries.
- No autoloading of large option values; options are fetched on demand.
- HTTP requests use WordPress's `wp_remote_post()` which respects WP HTTP API filters and caching.

---

## Internationalisation

All user-facing strings use WordPress i18n functions:

```php
__( 'Text', 'text-domain' )         // Returns translated string
_e( 'Text', 'text-domain' )         // Echoes translated string
esc_html__( 'Text', 'text-domain' ) // Returns escaped translated string
esc_html_e( 'Text', 'text-domain' ) // Echoes escaped translated string
```

Translations are loaded on the `plugins_loaded` hook:

```php
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'mc-remote-api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );
```
