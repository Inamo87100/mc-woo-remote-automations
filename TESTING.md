# Testing Guide

Comprehensive testing guide for the MC Plugins suite.

---

## Unit Test Structure

Tests are located in each plugin's `tests/` directory:

```
mc-remote-api/tests/
├── bootstrap.php          # WP test environment setup
├── test-api-endpoints.php # REST API endpoint tests
└── test-security.php      # Authentication & input validation tests

mc-woo-remote-automations/tests/
├── bootstrap.php          # WP test environment setup
├── test-automation.php    # Automation logic and log helper tests
└── test-security.php      # Input sanitisation and access control tests
```

---

## Running Automated Tests

### Prerequisites

- PHP 7.4+
- Composer
- WP-CLI
- A test database (MySQL / MariaDB)

### Install WP Test Suite

```bash
# Install WP-CLI if not present
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp

# Install WordPress test suite
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

### Run Tests for MC Remote API

```bash
cd mc-remote-api
phpunit --bootstrap tests/bootstrap.php tests/
```

### Run Tests for MC-Woo Remote Automations

```bash
cd mc-woo-remote-automations
phpunit --bootstrap tests/bootstrap.php tests/
```

---

## Manual Test Cases

See individual plugin testing guides:
- [mc-remote-api/docs/TESTING.md](mc-remote-api/docs/TESTING.md)
- [mc-woo-remote-automations/docs/TESTING.md](mc-woo-remote-automations/docs/TESTING.md)

---

## Integration Test Examples

### End-to-End Test Scenario

1. Install **MC Remote API** on Site B (target site).
2. Copy the API secret from Site B Settings → MC Remote API.
3. Install **MC-Woo Remote Automations** on Site A (WooCommerce site).
4. Create a Connection on Site A pointing to Site B.
5. Create an Automation on Site A for product ID X with trigger `completed`.
6. Place an order on Site A containing product X.
7. Change the order status to `completed`.
8. Verify:
   - Log entry appears in MC Automations → Logs on Site A.
   - User was created/role assigned on Site B.

---

## Compatibility Matrix

See [COMPATIBILITY.md](COMPATIBILITY.md) for full version compatibility information.

---

## Performance Benchmarks

| Scenario | Typical Time |
|----------|-------------|
| `/ping` endpoint response | < 100ms |
| `/create-user` (new user) | < 500ms |
| `/assign-role` | < 300ms |
| Automation trigger (single action) | < 2s (network-dependent) |

---

## Security Test Cases

| Test | Expected Result |
|------|-----------------|
| XSS in user_email parameter | Sanitised by `sanitize_email()`, rejected |
| XSS in role parameter | Rejected as invalid role |
| Missing X-MC-SECRET header | HTTP 401 |
| Timing attack on secret comparison | `hash_equals()` prevents timing leaks |
| SQL injection in log fields | `$wpdb->prepare()` / `sanitize_text_field()` prevents injection |
