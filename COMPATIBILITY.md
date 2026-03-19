# Compatibility Matrix

## MC Remote API

### WordPress Version Compatibility

| WordPress | Status       | Notes                          |
|-----------|--------------|--------------------------------|
| 6.4+      | ✅ Tested    | Fully supported                |
| 6.3       | ✅ Tested    | Fully supported                |
| 6.2       | ✅ Tested    | Fully supported                |
| 6.0       | ✅ Tested    | Fully supported                |
| 5.0       | ✅ Minimum   | REST API namespace required    |
| < 5.0     | ❌ Not supported | REST API improvements missing |

### PHP Version Compatibility

| PHP Version | Status       | Notes                          |
|-------------|--------------|--------------------------------|
| 8.3         | ✅ Tested    | Fully supported                |
| 8.2         | ✅ Tested    | Fully supported                |
| 8.1         | ✅ Tested    | Fully supported                |
| 8.0         | ✅ Tested    | Fully supported                |
| 7.4         | ✅ Minimum   | Minimum supported version      |
| 7.3         | ❌ Not supported | Typed properties not available |
| < 7.3       | ❌ Not supported | –                              |

---

## MC-Woo Remote Automations

### WordPress Version Compatibility

| WordPress | Status       | Notes                          |
|-----------|--------------|--------------------------------|
| 6.4+      | ✅ Tested    | Fully supported                |
| 6.0       | ✅ Tested    | Fully supported                |
| 5.0       | ✅ Minimum   | Minimum supported version      |
| < 5.0     | ❌ Not supported | –                              |

### WooCommerce Version Compatibility

| WooCommerce | Status       | Notes                          |
|-------------|--------------|--------------------------------|
| 9.x         | ✅ Tested    | Fully supported                |
| 8.x         | ✅ Tested    | Fully supported                |
| 7.x         | ✅ Tested    | Fully supported                |
| 6.x         | ✅ Tested    | Fully supported                |
| 5.x         | ✅ Tested    | Fully supported                |
| 4.x         | ✅ Compatible | `woocommerce_order_status_changed` hook present |
| 3.0         | ✅ Minimum   | Minimum supported version      |
| < 3.0       | ❌ Not supported | Order API differences         |

### PHP Version Compatibility

| PHP Version | Status       | Notes                          |
|-------------|--------------|--------------------------------|
| 8.3         | ✅ Tested    | Fully supported                |
| 8.2         | ✅ Tested    | Fully supported                |
| 8.1         | ✅ Tested    | Fully supported                |
| 8.0         | ✅ Tested    | Fully supported                |
| 7.4         | ✅ Minimum   | Minimum supported version      |
| < 7.4       | ❌ Not supported | –                              |

---

## Database Compatibility

Both plugins are compatible with:

| Database     | Status       |
|--------------|--------------|
| MySQL 5.7+   | ✅ Supported |
| MySQL 8.0+   | ✅ Supported |
| MariaDB 10.3+| ✅ Supported |
| MariaDB 10.6+| ✅ Supported |

---

## Known Plugin Compatibility

| Plugin                    | Status         | Notes                                              |
|---------------------------|----------------|----------------------------------------------------|
| WooCommerce Subscriptions | ✅ Compatible  | Trigger via `woocommerce_order_status_changed`     |
| Wordfence Security        | ⚠️ Configure  | Whitelist `/wp-json/mc/v1/` namespace              |
| iThemes Security          | ⚠️ Configure  | Disable REST API lockdown for `mc/v1` namespace    |
| WP Rocket                 | ✅ Compatible  | REST API not cached by default                     |
| WPML                      | ✅ Compatible  | User creation is language-agnostic                 |
| Yoast SEO                 | ✅ Compatible  | No conflicts                                       |

---

## Server Requirements

| Requirement        | Minimum Value          |
|--------------------|------------------------|
| PHP memory_limit   | 64M                    |
| PHP max_execution_time | 30s               |
| MySQL / MariaDB    | See table above        |
| HTTPS (TLS)        | Recommended            |
| WordPress Permalinks | Any non-plain structure |
