# MC Remote API – Testing Guide

## Manual Test Cases

### Installation Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 1 | Install plugin via WordPress dashboard | Plugin installs without errors |
| 2 | Activate plugin | Activation completes; API secret generated in Settings |
| 3 | Deactivate and reactivate plugin | API secret preserved across reactivation |
| 4 | Access Settings → MC Remote API | Settings page renders correctly |

### API Endpoint Tests – `/ping`

| # | Test | Expected Result |
|---|------|-----------------|
| 5 | GET `/ping` with valid secret | `{"success":true,"plugin":"MC Remote API"}` HTTP 200 |
| 6 | GET `/ping` without header | HTTP 401, `success: false` |
| 7 | GET `/ping` with wrong secret | HTTP 401, `success: false` |

### API Endpoint Tests – `/create-user`

| # | Test | Expected Result |
|---|------|-----------------|
| 8  | POST with valid email + correct secret | HTTP 200, `user_created` or `user_exists` |
| 9  | POST with invalid email | HTTP 400, `"Invalid email"` |
| 10 | POST with missing email | HTTP 400 |
| 11 | POST without secret header | HTTP 401 |
| 12 | POST with duplicate email | HTTP 200, `user_exists`, returns existing user ID |
| 13 | POST with invalid role slug | Role defaults to `customer` |
| 14 | POST with valid custom role | User created with custom role |

### API Endpoint Tests – `/assign-role`

| # | Test | Expected Result |
|---|------|-----------------|
| 15 | POST valid email + valid role + correct secret | HTTP 200, `role_assigned` |
| 16 | POST non-existent email | HTTP 404, `"User not found"` |
| 17 | POST invalid role slug | HTTP 400, `"Invalid role"` |
| 18 | POST missing email | HTTP 400, `"Missing email or role"` |
| 19 | POST missing role | HTTP 400, `"Missing email or role"` |

### Authentication Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 20 | Empty secret in settings | All endpoints return 401 |
| 21 | Secret contains special characters | Works correctly |
| 22 | 32-character secret | Works correctly |

### Error Handling Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 23 | Malformed JSON body | WordPress returns 400 |
| 24 | POST to non-existent endpoint | WordPress 404 |
| 25 | Plugin active with no permalink structure | Endpoints return 404 until permalinks flushed |

### Performance Tests

| # | Test | Expected Result |
|---|------|-----------------|
| 26 | 10 sequential `/ping` requests | All return within 500ms |
| 27 | 10 sequential `/create-user` requests | All complete without timeouts |

---

## Automated Test Structure

Tests are located in `tests/`:

```
tests/
├── bootstrap.php          # WP test environment setup
├── test-api-endpoints.php # REST API endpoint tests
└── test-security.php      # Authentication & validation tests
```

To run tests (requires [WP-CLI](https://wp-cli.org/) and the WP test suite):

```bash
cd mc-remote-api
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
phpunit --configuration phpunit.xml
```
