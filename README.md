# MC WordPress Plugins

Two WordPress plugins that work together to automate cross-site user management via WooCommerce orders.

---

## Plugins

### mc-remote-api

Exposes secure REST API endpoints on a **destination** WordPress site:

| Endpoint | Method | Description |
|---|---|---|
| `/wp-json/mc/v1/ping` | GET | Health check |
| `/wp-json/mc/v1/create-user` | POST | Create a user or return existing |
| `/wp-json/mc/v1/assign-role` | POST | Assign a role to a user by email |

All endpoints require a shared secret passed as the `X-MC-SECRET` HTTP header.

**Requirements:** WordPress ≥ 5.0, PHP ≥ 7.4

---

### mc-woo-remote-automations

Listens for WooCommerce order status changes on a **source** store and calls the `mc-remote-api` endpoints on one or more remote WordPress sites.

**Features:**
- Visual Automation editor (Custom Post Types – no code required)
- Multiple remote Connections supported simultaneously
- Per-product filtering
- Execution log
- Test Connection button

**Requirements:** WordPress ≥ 5.0, WooCommerce ≥ 3.0, PHP ≥ 7.4

---

## Plugin Structure

Both plugins follow the [WordPress.org marketplace](https://developer.wordpress.org/plugins/) directory structure:

```
plugin-name/
├── plugin-name.php          # Loader: constants, require, bootstrap
├── readme.txt               # WordPress.org marketplace description
├── LICENSE                  # GPL v2
├── .gitignore
├── includes/
│   ├── class-admin-banner.php
│   ├── class-main.php
│   └── functions.php
├── admin/
│   ├── class-admin.php      # (mc-woo-remote-automations only)
│   ├── css/
│   └── js/
├── public/
│   ├── css/
│   └── js/
├── assets/
│   └── images/
└── languages/
```

---

## How They Work Together

```
[WooCommerce Store]                       [Remote WordPress Site]
mc-woo-remote-automations                 mc-remote-api
        |                                         |
Order status → "completed"                        |
        |                                         |
        |--- POST /wp-json/mc/v1/create-user ---> |
        |--- POST /wp-json/mc/v1/assign-role ---> |
        |                                         |
        |<-- { success: true } ------------------|
```

1. A customer purchases a product on the WooCommerce store.
2. When the order reaches the configured status, `mc-woo-remote-automations` fires.
3. It sends HTTP requests to the remote site's `mc-remote-api` endpoints.
4. The remote site creates the user (if missing) and assigns the specified role.

---

## License

GPL v2 or later — see [LICENSE](LICENSE) and each plugin's own `LICENSE` file.

## Author

[Mamba Coding](https://mambacoding.com/)
