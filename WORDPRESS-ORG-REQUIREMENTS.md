# WordPress.org Plugin Directory Requirements

Complete reference for submitting and maintaining plugins on WordPress.org.

---

## Account Requirements

- A valid WordPress.org user account (free registration at [login.wordpress.org/register](https://login.wordpress.org/register))
- Email address must be verified
- Account must be in good standing (no prior policy violations)

---

## Plugin Submission Guidelines

1. **One submission per plugin** — do not submit the same plugin multiple times.
2. **Original work** — the plugin must be primarily your own work or used with appropriate permissions.
3. **Functional plugin** — the plugin must do something useful and work as described.
4. **No spam** — plugins existing solely to drive traffic to external sites are rejected.
5. **No hidden functionality** — all plugin behavior must be documented.

---

## Code Standards

- Follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) (PHP, JS, CSS, HTML).
- Use [WordPress APIs](https://codex.wordpress.org/WordPress_API%27s) instead of re-implementing core functionality.
- Use `$wpdb->prepare()` for all database queries with variables.
- Escape all output; sanitize all input.
- Prefix all functions, classes, and global variables with a unique prefix (e.g. `mc_woo_`, `MC_WOO_`).

---

## Security Requirements

- No remote code execution vulnerabilities.
- No SQL injection.
- No Cross-Site Scripting (XSS).
- No Cross-Site Request Forgery (CSRF) — use nonces.
- No privilege escalation — always verify capabilities with `current_user_can()`.
- No hardcoded credentials.
- No obfuscated code.
- No hidden outbound connections.

---

## Trademark Usage

- Do not use "WordPress" in your plugin name without explicit permission (e.g. "WordPress SEO" requires approval).
- Do not use third-party trademarks (WooCommerce, Stripe, etc.) in your plugin slug.
- Descriptive references (e.g. "for WooCommerce") are acceptable in the plugin description.

---

## License Requirements

- The plugin must be licensed under GPL v2 or a compatible license.
- A `LICENSE` file must be present in the plugin root.
- Any bundled third-party libraries must also be GPL-compatible.
- Attribution for third-party code must be included.

---

## readme.txt Requirements

See [How Your readme.txt Works](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/) for the full spec.

Required sections:
- `=== Plugin Name ===` header block
- `== Description ==`
- `== Installation ==`
- `== Changelog ==`

Recommended sections:
- `== Frequently Asked Questions ==`
- `== Screenshots ==`
- `== Upgrade Notice ==`

---

## Support Expectations

- Respond to support forum threads within a reasonable time (recommended: 7 days).
- Acknowledge security issues within 48 hours.
- Keep `Tested up to` tag updated within 2 weeks of new WordPress releases.

---

## Update Frequency

- Security fixes must be released as soon as possible (within days).
- Compatibility fixes should be released within 30 days of a WordPress major release.
- Feature updates have no required cadence.
- Plugins not updated in 2+ years may be closed by the review team.

---

## Deprecation Policy

- Announce deprecated functions/hooks in the plugin changelog at least one major version before removal.
- Use `_deprecated_function()` and `_deprecated_hook()` for in-code deprecation notices.
- Provide migration guides for breaking changes.

---

## External Services

If your plugin communicates with an external service:
- Document the service in `readme.txt` under a dedicated section.
- Link to the service's privacy policy and terms of service.
- Only send data when explicitly triggered by user action (not silently).
