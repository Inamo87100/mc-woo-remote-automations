# MC-Woo Remote Automations – Testing Guide

## Manual Test Cases

### Installation Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 1 | Install plugin via WordPress dashboard | No errors; plugin appears in Installed Plugins |
| 2 | Activate plugin | Log table `wp_mc_wra_logs` created; no errors |
| 3 | Check WooCommerce dependency | If WooCommerce missing, plugin should gracefully degrade |
| 4 | Deactivate and reactivate | Log table preserved; settings intact |

### Connection Management Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 5  | Create a Connection with valid URL and secret | Connection saved; appears in Connections list |
| 6  | Create Connection with empty URL | Connection saved but automations skip it |
| 7  | Disable a Connection (`_mc_enabled` ≠ `yes`) | Automations skip the disabled connection |
| 8  | Delete a Connection | Automations referencing it should log errors, not crash |

### Automation Configuration Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 9  | Create Automation with valid Connection and product | Automation saved and enabled |
| 10 | Create Automation with no product IDs | Automation fires for all orders (if enabled) |
| 11 | Set trigger status to `completed` | Only fires when order status → completed |
| 12 | Set trigger status to `processing` | Only fires when order status → processing |

### Automation Execution Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 13 | Order reaches trigger status with matching product | Automation fires; log entry created |
| 14 | Order reaches trigger status with non-matching product | Automation does not fire |
| 15 | Automation fires; remote site unreachable | Log entry with `failed` status and WP_Error message |
| 16 | Automation fires; invalid secret on remote | Log entry with `failed` status, HTTP 401 |
| 17 | Automation fires; user already exists on remote | Log entry with `success` (user_exists accepted) |
| 18 | Assign Role enabled; valid role | Log entry `assign_role` with `success` |
| 19 | Assign Role enabled; invalid role slug | Log entry `assign_role` with `failed`, HTTP 400 |

### Log Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 20 | View Logs admin page | All log entries displayed correctly |
| 21 | Log table columns present | id, created_at, automation_id, connection_id, order_id, action_key, user_email, status, response_code, message |

### Performance Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 22 | 10 automations configured; one order triggers all | All 10 fire within 30 seconds (depending on timeout) |
| 23 | Automation with 1-second timeout on slow server | Logs show timeout error, does not block order processing |

---

## Automated Test Structure

Tests are located in `tests/`:

```
tests/
├── bootstrap.php       # WP test environment setup
├── test-automation.php # Automation trigger and execution tests
└── test-security.php   # Input sanitisation and output escaping tests
```

To run tests (requires WP-CLI and WP test suite):

```bash
cd mc-woo-remote-automations
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
phpunit --configuration phpunit.xml
```
