# MC-Woo Remote Automations – Setup Guide

## Prerequisites

- WordPress 5.0 or later
- WooCommerce 3.0 or later
- PHP 7.4 or later
- At least one remote WordPress site with **MC Remote API** installed and active

---

## 1. Installation

### Option A – Upload via WordPress Dashboard

1. Download the `mc-woo-remote-automations.zip` plugin file.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**.
4. Click **Activate Plugin**.

### Option B – Manual Upload via FTP/SFTP

1. Extract the zip file locally.
2. Upload the `mc-woo-remote-automations/` folder to `/wp-content/plugins/`.
3. In WordPress admin, go to **Plugins → Installed Plugins** and activate **MC-Woo Remote Automations**.

---

## 2. Creating a Connection

A **Connection** stores the URL and credentials for one remote WordPress site.

1. Go to **MC Automations → Connections → Add Connection**.
2. Fill in:
   - **Title** – a friendly name (e.g., "Production Site").
   - **Base URL** – the remote site URL (e.g., `https://remote-site.com`).
   - **Create User Secret** – the `X-MC-SECRET` value from the remote site's Settings → MC Remote API.
   - **Assign Role Secret** – leave blank to use the same secret for both operations.
   - **Status** – set to **Enabled**.
3. Click **Publish** to save.

---

## 3. Creating an Automation

An **Automation** maps a WooCommerce order trigger to a remote action.

1. Go to **MC Automations → Automations → Add Automation**.
2. Configure:
   - **Title** – descriptive name (e.g., "Premium Course Access").
   - **Connection** – select the connection created above.
   - **Order Status** – the WooCommerce status that triggers the automation (e.g., `completed`).
   - **Product IDs** – comma-separated product IDs; the automation fires only if the order contains at least one of these products.
   - **Create User if Missing** – check to call `/create-user` before role assignment.
   - **Assign Role** – check and enter the remote role slug to assign.
   - **Timeout (seconds)** – override the default HTTP timeout (default: 10s).
   - **Status** – set `_mc_enabled` meta to `yes` to activate.
3. Click **Publish** to save.

---

## 4. Viewing Execution Logs

1. Go to **MC Automations → Logs**.
2. Each row shows: date, automation, connection, order, action, email, status, and response.
3. Use logs to diagnose failures and verify successful executions.

---

## 5. Testing an Automation

1. Create a test WooCommerce order with one of the configured product IDs.
2. Manually move the order to the trigger status via **Orders → Edit Order → Order status**.
3. Check **MC Automations → Logs** for the result.
4. Verify the user was created/updated on the remote site.

---

## Troubleshooting

See [`TROUBLESHOOTING.md`](TROUBLESHOOTING.md) for common issues and solutions.
