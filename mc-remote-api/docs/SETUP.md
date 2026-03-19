# MC Remote API – Setup Guide

## Prerequisites

- WordPress 5.0 or later
- PHP 7.4 or later
- SSL/HTTPS enabled on the target site (strongly recommended)
- Admin access to the WordPress dashboard

---

## 1. Installation

### Option A – Upload via WordPress Dashboard

1. Download the `mc-remote-api.zip` plugin file.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**.
4. Click **Activate Plugin**.

### Option B – Manual Upload via FTP/SFTP

1. Extract `mc-remote-api.zip` to your local machine.
2. Upload the `mc-remote-api/` folder to `/wp-content/plugins/` on your server.
3. In the WordPress dashboard, go to **Plugins → Installed Plugins**.
4. Locate **MC Remote API** and click **Activate**.

---

## 2. Plugin Activation

Upon activation the plugin automatically generates a secure 32-character API secret and stores it in the WordPress options table. No manual configuration is required for the secret unless you wish to use your own value.

---

## 3. Viewing and Updating the API Secret

1. In the WordPress admin, go to **Settings → MC Remote API**.
2. The **API Secret** field shows the current secret.
3. To change the secret, clear the field, enter a new value (minimum 32 characters recommended), and click **Save Changes**.
4. Copy the secret – you will need it in every API call via the `X-MC-SECRET` header.

---

## 4. Testing the Connection

Use the `/ping` endpoint to verify the plugin is active and the secret is correct.

```bash
curl -X GET "https://your-site.com/wp-json/mc/v1/ping" \
  -H "X-MC-SECRET: YOUR_SECRET_HERE"
```

Expected response:

```json
{"success":true,"plugin":"MC Remote API"}
```

---

## 5. Enabling Pretty Permalinks

WordPress REST API requires pretty permalinks to be enabled.

1. Go to **Settings → Permalinks**.
2. Select any option other than **Plain** (e.g., **Post name**).
3. Click **Save Changes**.

---

## 6. Available Endpoints

| Endpoint                          | Method | Description              |
|-----------------------------------|--------|--------------------------|
| `/wp-json/mc/v1/ping`             | GET    | Test connection          |
| `/wp-json/mc/v1/create-user`      | POST   | Create a WordPress user  |
| `/wp-json/mc/v1/assign-role`      | POST   | Assign role to a user    |

See [`API.md`](API.md) for full parameter documentation.

---

## 7. Integrating with MC-Woo Remote Automations

If you are using **MC-Woo Remote Automations** on a WooCommerce site, add this site's URL and API secret as a Connection in that plugin. See the WooCommerce plugin's setup guide for details.

---

## Troubleshooting

See [`TROUBLESHOOTING.md`](TROUBLESHOOTING.md) for common issues and solutions.
