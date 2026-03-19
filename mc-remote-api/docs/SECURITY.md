# MC Remote API – Security Guide

## Overview

Security is the top priority for MC Remote API. This document outlines the security model, recommendations, and best practices.

---

## Authentication Model

All API endpoints are protected by a shared secret passed in the `X-MC-SECRET` HTTP header. The plugin uses `hash_equals()` for constant-time comparison to prevent timing attacks.

```php
hash_equals( $stored_secret, $provided_secret )
```

**Never** transmit the secret over plain HTTP (non-HTTPS) connections.

---

## Secret Management

### Generation

On first activation the plugin generates a 32-character cryptographically random password using `wp_generate_password()` (backed by `random_bytes()`). This is stored as a WordPress option (`mc_api_secret`).

### Rotation

Rotate the secret regularly or immediately if:
- A team member with access leaves.
- You suspect the secret has been leaked.
- A security audit requires it.

**To rotate:**
1. Go to **Settings → MC Remote API**.
2. Clear the API Secret field.
3. Enter a new secret (minimum 32 characters, mix of letters, numbers, symbols).
4. Click **Save Changes**.
5. Update the secret in all clients (MC-Woo Remote Automations connections, scripts, etc.).

### Storage

- Secrets are stored in `wp_options`. Ensure your database is not publicly accessible.
- Do not hard-code the secret in scripts checked into version control.
- Use environment variables or a secrets manager in CI/CD pipelines.

---

## Network Security

- **Always use HTTPS** – TLS encrypts the `X-MC-SECRET` header in transit.
- Consider restricting access to `/wp-json/mc/v1/` to known IP ranges using nginx or Apache:

```nginx
location ~ ^/wp-json/mc/v1/ {
    allow 203.0.113.0/24;  # WooCommerce server IP range
    deny all;
}
```

- Enable HTTP Strict Transport Security (HSTS) on your server.

---

## Input Validation

All user-provided data is validated and sanitised before use:

| Input         | Sanitisation function    |
|---------------|--------------------------|
| `user_email`  | `sanitize_email()` + `is_email()` |
| `first_name`  | `sanitize_text_field()`  |
| `last_name`   | `sanitize_text_field()`  |
| `role`        | `wp_roles()->is_role()`  |
| `email`       | `sanitize_email()`       |

Invalid input returns HTTP 400 before any database operation is attempted.

---

## Output Escaping

All output rendered in the admin UI is escaped:
- `esc_html()` for text content.
- `esc_attr()` for HTML attributes.

---

## Capability Checks

The admin settings page requires the `manage_options` capability (administrators only).

---

## Data Privacy

- User passwords are generated with `wp_generate_password()` and never stored in logs or transmitted in responses.
- API request/response logs in MC-Woo Remote Automations contain email addresses; ensure log access is restricted.

---

## Logging Best Practices

- Enable WordPress debug logging only in development (`WP_DEBUG_LOG`).
- Ensure `wp-content/debug.log` is not web-accessible in production.
- Rotate and archive logs regularly.

---

## Compliance Considerations

- **GDPR**: Email addresses processed via the API are personal data. Ensure you have a lawful basis for processing and document your data flows.
- **PCI DSS**: This plugin does not process payment data; standard WordPress hardening applies.
- Keep WordPress core, PHP, and all plugins up to date.

---

## Reporting Vulnerabilities

To report a security vulnerability, please contact us at **security@mambacoding.com** rather than opening a public issue. We aim to respond within 72 hours.
