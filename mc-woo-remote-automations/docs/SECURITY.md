# MC-Woo Remote Automations – Security Guide

## Overview

MC-Woo Remote Automations processes WooCommerce order data and sends HTTP requests to remote WordPress sites. This document describes the security measures in place and best practices to follow.

---

## Authentication of Remote Requests

All HTTP requests to remote sites include the `X-MC-SECRET` header. Secrets are stored per-Connection as WordPress post meta and retrieved at runtime.

**Best practices:**
- Use a unique secret per Connection (one per remote site).
- Secrets should be at least 32 characters with mixed character types.
- Rotate secrets periodically or after any suspected compromise.
- Never share secrets in plain-text channels (email, Slack, etc.).

---

## Data Transmitted

The following customer data is sent to the remote site in HTTP request bodies:

| Field        | Source                        | Sanitisation               |
|--------------|-------------------------------|----------------------------|
| `user_email` | `$order->get_billing_email()` | `sanitize_email()`         |
| `first_name` | `$order->get_billing_first_name()` | `sanitize_text_field()` |
| `last_name`  | `$order->get_billing_last_name()` | `sanitize_text_field()`  |
| `role`       | Admin-configured meta value   | `sanitize_text_field()`    |

No payment data, addresses, or sensitive financial information is transmitted.

---

## Log Data Privacy

Execution logs stored in `wp_mc_wra_logs` contain:
- Customer email addresses (personal data under GDPR).
- Request payloads (including name fields).
- Response bodies from the remote site.

**Recommendations:**
- Restrict database access to trusted administrators only.
- Consider a log retention policy (delete logs older than 90 days).
- Ensure `wp-admin` access is restricted with strong passwords and 2FA.

---

## Input Validation

All admin form inputs are validated:
- Connection URLs must be valid HTTP/HTTPS URLs.
- Product IDs are cast to integers: `array_map( 'intval', $product_ids )`.
- Timeout values are cast to integers with a minimum of 1 second.

---

## Output Escaping

All admin-rendered output is escaped with appropriate WordPress functions:
- `esc_html()` for displayed text.
- `esc_attr()` for HTML attribute values.
- `wp_kses_post()` for log message content.

---

## Nonce Protection

All admin forms use WordPress nonces to prevent CSRF attacks.

---

## Network Security

- Always use HTTPS for remote site URLs in Connections.
- Consider restricting the WooCommerce server's outbound connections to known remote IPs.
- Implement rate limiting on the remote site to prevent abuse.

---

## GDPR Compliance

- Ensure your Privacy Policy covers the transmission of customer data to remote sites.
- Provide a mechanism for customers to request deletion of their data on remote sites.
- Document your data processing activities (Article 30 GDPR record).

---

## Reporting Vulnerabilities

To report a security vulnerability, contact **security@mambacoding.com** privately. Do not open a public issue for security matters. We aim to respond within 72 hours.
