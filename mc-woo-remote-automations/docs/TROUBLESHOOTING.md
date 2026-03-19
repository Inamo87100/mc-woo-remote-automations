# MC-Woo Remote Automations – Troubleshooting Guide

## Automation not firing

**Symptom:** An order reaches the trigger status but no log entry appears.

**Solutions:**
1. Ensure the Automation's `_mc_enabled` meta is set to `yes` (Automation status = Enabled).
2. Confirm the order contains at least one of the configured product IDs.
3. Verify the order status matches exactly (e.g., `completed`, not `wc-completed`).
4. Check that WooCommerce fires the `woocommerce_order_status_changed` hook. Some order management plugins suppress this.

---

## "Invalid secret" in logs

**Symptom:** Log entries show `status: failed` with HTTP 401.

**Solutions:**
1. Open the Connection and verify the **Create User Secret** matches the API secret on the remote site (**Settings → MC Remote API**).
2. Look for copy-paste whitespace issues.
3. If the remote site recently regenerated its secret, update the Connection.

---

## Connection refused / timeout in logs

**Symptom:** Log entries show `status: failed` with a WP_Error message.

**Solutions:**
1. Verify the **Base URL** in the Connection is correct and accessible.
2. Ensure HTTPS is working on the remote site.
3. Check firewall rules on both servers.
4. Increase the **Timeout** setting on the Automation.
5. Test the remote site with curl: `curl https://remote-site.com/wp-json/mc/v1/ping -H "X-MC-SECRET: secret"`.

---

## User not created on remote site

**Symptom:** Log shows HTTP 200 but user doesn't exist on remote site.

**Solutions:**
1. Confirm **Create User if Missing** is checked on the Automation.
2. Check the response body in the log – it may indicate a server-side error.
3. Verify MC Remote API is active on the remote site.
4. Ensure the billing email is present on the order.

---

## Role not assigned

**Symptom:** User created successfully but role assignment fails.

**Solutions:**
1. Confirm **Assign Role** is checked on the Automation and a role slug is entered.
2. Verify the role slug exists on the remote site.
3. If using a separate secret for role assignment, verify **Assign Role Secret** is correct.
4. Check logs for HTTP 400 "Invalid role" – the role may not exist on the remote WordPress.

---

## Duplicate log entries

**Symptom:** Multiple log entries for the same order.

**Solutions:**
1. Check if multiple Automations match the same order (multiple products configured).
2. Some payment gateways change order status multiple times – expected behaviour.
3. Consider adding order meta to prevent duplicate processing if needed.

---

## Log table missing

**Symptom:** Logs page shows a database error.

**Solution:** Deactivate and reactivate the plugin. Activation creates the `wp_mc_wra_logs` table via `dbDelta()`.

---

## Still stuck?

Open an issue at [https://github.com/Inamo87100/mc-woo-remote-automations/issues](https://github.com/Inamo87100/mc-woo-remote-automations/issues) with:
- WordPress and WooCommerce versions
- PHP version
- Full log entry details
- Steps to reproduce
