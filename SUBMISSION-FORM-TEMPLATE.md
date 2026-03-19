# Submission Form Template

Pre-filled content for the WordPress.org plugin submission form.

---

## mc-woo-remote-automations

| Field | Value |
|-------|-------|
| **Plugin Name** | MC-Woo Remote Automations |
| **Plugin Slug** | mc-woo-remote-automations |
| **Author** | Mamba Coding |
| **Author URI** | https://mambacoding.com/ |
| **Donate Link** | *(optional)* |

### Short Description (≤ 150 characters)

```
Automate remote user creation and role assignment on WordPress sites from WooCommerce orders via a secure REST API.
```

### Long Description (for plugin page)

```markdown
**MC-Woo Remote Automations** bridges your WooCommerce store with remote WordPress installations.
When an order is placed, the plugin automatically creates a user account on one or more remote
WordPress sites and assigns the correct role — no manual intervention required.

**Key Features:**

* 🔗 **Remote connection management** — configure multiple target WordPress sites
* 👤 **Automatic user creation** — triggered on WooCommerce order completion
* 🎭 **Role assignment** — map WooCommerce products to WordPress roles
* 🔒 **Secure API communication** — uses application passwords and HTTPS
* 📊 **Execution log** — track all automation runs with status and timestamps
* 🔄 **Retry mechanism** — automatically retries failed automations
* ⚡ **Async processing** — background processing to keep checkout fast

**Use Cases:**

* Sell access to membership sites via WooCommerce
* Automatically enroll students in LMS platforms (LearnDash, LifterLMS, etc.)
* Manage multi-site user access from a central store

**Requirements:**

* WordPress 5.0+
* WooCommerce 3.0+
* PHP 7.4+
* The [MC Remote API](https://wordpress.org/plugins/mc-remote-api/) plugin on the remote site
```

### Category

`E-Commerce` (primary), `Automation`

### Tags

```
woocommerce, automation, remote, user-management, rest-api, membership, e-commerce, role-assignment
```

### Support URL

```
https://wordpress.org/support/plugin/mc-woo-remote-automations/
```

### Documentation URL

```
https://github.com/Inamo87100/mc-woo-remote-automations/tree/main/mc-woo-remote-automations/docs
```

---

## mc-remote-api

| Field | Value |
|-------|-------|
| **Plugin Name** | MC Remote API |
| **Plugin Slug** | mc-remote-api |
| **Author** | Mamba Coding |
| **Author URI** | https://mambacoding.com/ |
| **Donate Link** | *(optional)* |

### Short Description (≤ 150 characters)

```
Expose a secure REST API endpoint to allow remote WordPress sites to create users and assign roles programmatically.
```

### Long Description (for plugin page)

```markdown
**MC Remote API** is the receiver-side companion plugin for [MC-Woo Remote Automations](https://wordpress.org/plugins/mc-woo-remote-automations/).

Install it on any WordPress site you want to manage remotely. It adds a secure REST API endpoint
that accepts authenticated requests to create users and assign roles.

**Key Features:**

* 🔒 **Secure endpoint** — API key authentication with rate limiting
* 👤 **User creation** — create WordPress users via REST API
* 🎭 **Role assignment** — assign any registered WordPress role
* 📋 **Audit log** — record all incoming API requests
* ⚙️ **Admin settings** — manage API keys from the WordPress dashboard
* 🛡️ **IP allowlist** — restrict access to specific IP addresses (optional)

**Use Cases:**

* Receive automated user provisioning from a WooCommerce store
* Integrate with any system that can make HTTP requests
* Build custom user onboarding workflows

**Requirements:**

* WordPress 5.0+
* PHP 7.4+
```

### Category

`Developer Tools`, `Security`

### Tags

```
rest-api, user-management, remote, automation, api, security, developer-tools
```

### Support URL

```
https://wordpress.org/support/plugin/mc-remote-api/
```

### Documentation URL

```
https://github.com/Inamo87100/mc-woo-remote-automations/tree/main/mc-remote-api/docs
```
