# Manual Testing Guide

Complete step-by-step instructions for manually testing both MC plugins before WordPress.org submission.

## Pre-Testing Setup

1. [ ] Fresh WordPress installation (local or staging)
2. [ ] Both plugins installed and activated:
   - `mc-remote-api`
   - `mc-woo-remote-automations`
3. [ ] WooCommerce installed and activated
4. [ ] API secret generated (Settings → MC Remote API → copy the secret)
5. [ ] Connection configured in mc-woo-remote-automations pointing to the mc-remote-api site

---

## API Testing (`mc-remote-api`)

### Test 1: Ping (connection check)

```bash
curl -X GET http://localhost/wp-json/mc/v1/ping \
  -H "X-MC-SECRET: your_secret_here"
```

**Expected result:**

```json
{"success":true,"plugin":"MC Remote API","version":"1.1.0"}
```

---

### Test 2: Create User – success

```bash
curl -X POST http://localhost/wp-json/mc/v1/create-user \
  -H "X-MC-SECRET: your_secret_here" \
  -H "Content-Type: application/json" \
  -d '{
    "user_email": "test@example.com",
    "first_name": "Test",
    "last_name":  "User",
    "role":       "subscriber"
  }'
```

**Expected result:** `200 OK`

```json
{"success":true,"code":"user_created","user_id":42}
```

Verify the user exists: **WordPress Admin → Users → search for test@example.com**.

---

### Test 3: Create User – duplicate email

Run Test 2 again with the same email.

**Expected result:** `200 OK`

```json
{"success":true,"code":"user_exists","user_id":42}
```

---

### Test 4: Create User – invalid email

```bash
curl -X POST http://localhost/wp-json/mc/v1/create-user \
  -H "X-MC-SECRET: your_secret_here" \
  -H "Content-Type: application/json" \
  -d '{
    "user_email": "invalid-email",
    "first_name": "Test",
    "last_name":  "User",
    "role":       "subscriber"
  }'
```

**Expected result:** `400 Bad Request`

```json
{"success":false,"code":"invalid_email","message":"..."}
```

---

### Test 5: Create User – missing secret

```bash
curl -X POST http://localhost/wp-json/mc/v1/create-user \
  -H "Content-Type: application/json" \
  -d '{
    "user_email": "test@example.com",
    "first_name": "Test",
    "last_name":  "User",
    "role":       "subscriber"
  }'
```

**Expected result:** `401 Unauthorized`

---

### Test 6: Create User – wrong secret

```bash
curl -X POST http://localhost/wp-json/mc/v1/create-user \
  -H "X-MC-SECRET: wrong_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "user_email": "test@example.com",
    "first_name": "Test",
    "last_name":  "User",
    "role":       "subscriber"
  }'
```

**Expected result:** `401 Unauthorized`

---

### Test 7: Assign Role – success

First create the user (Test 2), then:

```bash
curl -X POST http://localhost/wp-json/mc/v1/assign-role \
  -H "X-MC-SECRET: your_secret_here" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "role":  "editor"
  }'
```

**Expected result:** `200 OK`

```json
{"success":true,"code":"role_assigned","role":"editor"}
```

Verify in **WordPress Admin → Users** that the role changed to Editor.

---

### Test 8: Assign Role – user not found

```bash
curl -X POST http://localhost/wp-json/mc/v1/assign-role \
  -H "X-MC-SECRET: your_secret_here" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "nobody@example.com",
    "role":  "editor"
  }'
```

**Expected result:** `404 Not Found`

---

### Test 9: Assign Role – invalid role

```bash
curl -X POST http://localhost/wp-json/mc/v1/assign-role \
  -H "X-MC-SECRET: your_secret_here" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "role":  "nonexistent_role_xyz"
  }'
```

**Expected result:** `400 Bad Request`

---

## WooCommerce Automation Testing (`mc-woo-remote-automations`)

### Test 1: Automation triggers on order completed

1. [ ] Navigate to **MC Automations → Add New**
2. [ ] Create automation: trigger = `woocommerce_order_status_completed`, action = `mc_create_user`
3. [ ] Save the automation
4. [ ] Create a test product (Products → Add New)
5. [ ] Place a test order as a customer
6. [ ] In **WooCommerce → Orders**, mark the order as **Completed**
7. [ ] Navigate to **MC Automations → Logs**
8. [ ] Verify a log entry exists for the order with status **success**
9. [ ] Verify the customer user was created via API

---

### Test 2: Automation logs are recorded

1. [ ] Navigate to **MC Automations → Logs**
2. [ ] Verify columns: Date, Order, Action, Email, Status, Response Code
3. [ ] Verify timestamps are correct
4. [ ] Verify the log entry links to the correct order

---

### Test 3: Error handling – API unavailable

1. [ ] In **MC Automations → Connections**, set an invalid URL (e.g. `http://127.0.0.1:9999/wp-json`)
2. [ ] Complete a WooCommerce order
3. [ ] Navigate to **MC Automations → Logs**
4. [ ] Verify a **failed** log entry exists
5. [ ] Verify the error message describes the connection failure

---

### Test 4: Product condition filter

1. [ ] Create an automation with a condition: only trigger for product ID = X
2. [ ] Place an order containing product X → verify automation triggered
3. [ ] Place an order containing a different product → verify automation **not** triggered

---

## Performance Testing

### Test 1: Bulk operations

1. [ ] Create an automation rule
2. [ ] Generate 20+ test orders programmatically (use WP-CLI or a test plugin)
3. [ ] Complete all orders
4. [ ] Verify all log entries are created
5. [ ] Check server response times and memory usage

### Test 2: Concurrent API requests

```bash
# Send 10 concurrent create-user requests
for i in $(seq 1 10); do
  curl -s -X POST http://localhost/wp-json/mc/v1/create-user \
    -H "X-MC-SECRET: your_secret_here" \
    -H "Content-Type: application/json" \
    -d "{\"user_email\":\"concurrent_${i}@example.com\",\"first_name\":\"User\",\"last_name\":\"${i}\",\"role\":\"subscriber\"}" &
done
wait
echo "All requests sent."
```

Verify all users were created without errors.

---

## Compatibility Testing

### WordPress versions

| WP Version | Tested | Status | Notes |
|------------|--------|--------|-------|
| 6.0        | [ ]    |        |       |
| 6.4        | [ ]    |        |       |
| 6.5        | [ ]    |        |       |

### PHP versions

| PHP Version | Tested | Status | Notes |
|-------------|--------|--------|-------|
| 7.4         | [ ]    |        |       |
| 8.0         | [ ]    |        |       |
| 8.1         | [ ]    |        |       |
| 8.2         | [ ]    |        |       |
| 8.3         | [ ]    |        |       |

### WooCommerce versions

| WC Version | Tested | Status | Notes |
|------------|--------|--------|-------|
| 7.0        | [ ]    |        |       |
| 8.0        | [ ]    |        |       |
| 8.5        | [ ]    |        |       |
| 9.0        | [ ]    |        |       |
