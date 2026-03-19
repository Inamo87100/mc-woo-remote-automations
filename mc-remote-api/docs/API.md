# MC Remote API – API Documentation

## Overview

MC Remote API exposes a set of REST endpoints that allow remote WordPress sites (or any HTTP client) to create users and manage their roles securely.

**Base URL:** `https://your-site.com/wp-json/mc/v1/`

---

## Authentication

All endpoints (except where noted) require a shared secret passed via the `X-MC-SECRET` HTTP header.

```
X-MC-SECRET: your-secret-here
```

The secret is generated automatically on plugin activation and can be viewed or rotated in **Settings → MC Remote API**.

---

## Endpoints

### `GET /wp-json/mc/v1/ping`

Tests the connection and verifies the secret is valid.

**Headers:**

| Header        | Required | Description            |
|---------------|----------|------------------------|
| X-MC-SECRET   | Yes      | API shared secret      |

**Response – success (HTTP 200):**

```json
{
  "success": true,
  "plugin": "MC Remote API"
}
```

**Response – invalid secret (HTTP 401):**

```json
{
  "success": false,
  "message": "Invalid secret"
}
```

**cURL example:**

```bash
curl -X GET "https://your-site.com/wp-json/mc/v1/ping" \
  -H "X-MC-SECRET: your-secret-here"
```

---

### `POST /wp-json/mc/v1/create-user`

Creates a new WordPress user. If a user with the given email already exists the endpoint returns a success response with code `user_exists`.

**Headers:**

| Header        | Required | Description                          |
|---------------|----------|--------------------------------------|
| X-MC-SECRET   | Yes      | API shared secret                    |
| Content-Type  | Yes      | `application/json; charset=utf-8`    |
| Accept        | No       | `application/json`                   |

**Body parameters:**

| Parameter   | Type   | Required | Description                                |
|-------------|--------|----------|--------------------------------------------|
| user_email  | string | Yes      | Valid email address for the new user       |
| first_name  | string | No       | User's first name                          |
| last_name   | string | No       | User's last name                           |
| role        | string | No       | WordPress role slug (default: `customer`)  |

**Response – user created (HTTP 200):**

```json
{
  "success": true,
  "code": "user_created",
  "user_id": 42
}
```

**Response – user already exists (HTTP 200):**

```json
{
  "success": true,
  "code": "user_exists",
  "user_id": 17
}
```

**Response – invalid email (HTTP 400):**

```json
{
  "success": false,
  "message": "Invalid email"
}
```

**Response – invalid secret (HTTP 401):**

```json
{
  "success": false,
  "message": "Invalid secret"
}
```

**cURL example:**

```bash
curl -X POST "https://your-site.com/wp-json/mc/v1/create-user" \
  -H "X-MC-SECRET: your-secret-here" \
  -H "Content-Type: application/json" \
  -d '{"user_email":"john@example.com","first_name":"John","last_name":"Doe","role":"customer"}'
```

---

### `POST /wp-json/mc/v1/assign-role`

Assigns a WordPress role to an existing user.

**Headers:**

| Header        | Required | Description                          |
|---------------|----------|--------------------------------------|
| X-MC-SECRET   | Yes      | API shared secret                    |
| Content-Type  | Yes      | `application/json; charset=utf-8`    |
| Accept        | No       | `application/json`                   |

**Body parameters:**

| Parameter | Type   | Required | Description                            |
|-----------|--------|----------|----------------------------------------|
| email     | string | Yes      | Email address of the existing user     |
| role      | string | Yes      | WordPress role slug to assign          |

**Response – role assigned (HTTP 200):**

```json
{
  "success": true,
  "code": "role_assigned",
  "role": "editor"
}
```

**Response – user not found (HTTP 404):**

```json
{
  "success": false,
  "message": "User not found"
}
```

**Response – invalid role (HTTP 400):**

```json
{
  "success": false,
  "message": "Invalid role"
}
```

**Response – missing parameters (HTTP 400):**

```json
{
  "success": false,
  "message": "Missing email or role"
}
```

**cURL example:**

```bash
curl -X POST "https://your-site.com/wp-json/mc/v1/assign-role" \
  -H "X-MC-SECRET: your-secret-here" \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","role":"editor"}'
```

---

## HTTP Status Codes

| Code | Meaning                            |
|------|------------------------------------|
| 200  | Request succeeded                  |
| 400  | Bad request / invalid parameters   |
| 401  | Authentication failed              |
| 404  | Resource not found                 |
| 500  | Internal server error              |

---

## Error Handling

All error responses follow this structure:

```json
{
  "success": false,
  "message": "Human-readable error description"
}
```

---

## Rate Limiting

The plugin does not impose its own rate limits. Consider using a server-level firewall rule (e.g. nginx `limit_req`) or the WordPress `heartbeat` API settings to prevent abuse.

---

## Security Best Practices

- Keep the API secret at least 32 characters long.
- Rotate the secret regularly (see Settings → MC Remote API).
- Restrict network access to the `/wp-json/mc/v1/` namespace to trusted IP ranges where possible.
- Always use HTTPS in production.
- See [`SECURITY.md`](SECURITY.md) for full recommendations.
