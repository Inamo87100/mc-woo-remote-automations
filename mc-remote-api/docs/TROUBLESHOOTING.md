# MC Remote API – Troubleshooting Guide

## "Invalid secret" errors

**Symptom:** API calls return `{"success":false,"message":"Invalid secret"}` with HTTP 401.

**Solutions:**
1. Verify the `X-MC-SECRET` header matches the secret shown in **Settings → MC Remote API**.
2. Check for leading/trailing whitespace in the secret value.
3. Ensure the header name is exactly `X-MC-SECRET` (case-sensitive on some servers).
4. If you recently regenerated the secret, update it in all clients (e.g., MC-Woo Remote Automations connections).

---

## Connection failures / request timeout

**Symptom:** Requests hang or return connection errors.

**Solutions:**
1. Confirm the target WordPress site is accessible from the calling server.
2. Check that HTTPS is properly configured (valid SSL certificate).
3. Ensure WordPress REST API is not blocked by a security plugin (e.g., Wordfence, iThemes Security). Add the namespace `mc/v1` to the allow-list.
4. Check for firewall rules blocking outbound HTTP from the WooCommerce server.
5. Try increasing the timeout in the MC-Woo Remote Automations connection settings.

---

## 404 on API endpoints

**Symptom:** Requests return HTTP 404.

**Solutions:**
1. Go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules.
2. Confirm the plugin is active in **Plugins → Installed Plugins**.
3. Ensure PHP has no fatal errors by checking `wp-content/debug.log` (enable `WP_DEBUG` and `WP_DEBUG_LOG`).
4. Verify the request URL path is `/wp-json/mc/v1/...` – not `/wp-json/mc/v1.0/...`.

---

## User creation issues

**Symptom:** `/create-user` returns an error or unexpected response.

**Solutions:**
1. Confirm the `user_email` is a valid email address.
2. Check for a conflicting user registration plugin that overrides `wp_create_user`.
3. Review `wp-content/debug.log` for PHP errors.
4. If the response is `user_exists`, no action is needed – the user already has an account.

---

## Role assignment problems

**Symptom:** `/assign-role` returns "Invalid role" or "User not found".

**Solutions:**
1. Verify the role slug is a valid WordPress role (e.g., `administrator`, `editor`, `author`, `contributor`, `subscriber`, `customer`, `shop_manager`).
2. Confirm the user exists on the remote site before assigning a role.
3. Custom roles registered by themes or plugins are valid – use the exact slug.

---

## Timeout issues

**Symptom:** Requests to `/create-user` or `/assign-role` take too long.

**Solutions:**
1. Check the PHP `max_execution_time` and `default_socket_timeout` values on the calling server.
2. In MC-Woo Remote Automations, lower the per-automation timeout if needed.
3. Verify the target site's response time by testing `/ping` first.

---

## REST API is disabled

**Symptom:** Any `/wp-json/` URL returns a 403 or 404.

**Solutions:**
1. Some security plugins disable the REST API. Whitelist the `mc/v1` namespace.
2. Check if `add_filter('rest_enabled', '__return_false')` exists in your theme/plugins.
3. Verify no `.htaccess` rules block `wp-json`.

---

## Still stuck?

Open an issue at [https://github.com/Inamo87100/mc-woo-remote-automations/issues](https://github.com/Inamo87100/mc-woo-remote-automations/issues) with:
- WordPress version
- PHP version
- Description of the error
- Relevant log entries (anonymised)
