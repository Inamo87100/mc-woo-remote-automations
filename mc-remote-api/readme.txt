=== MC Remote API ===
Contributors: mambacoding
Tags: api, rest-api, user-management, woocommerce, remote
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Expose secure REST API endpoints for remote user creation and role assignment.

== Description ==

MC Remote API provides a lightweight, secure REST API layer that allows external services (such as WooCommerce stores running MC-Woo Remote Automations) to create WordPress users and manage their roles programmatically.

**Key features:**

* `/wp-json/mc/v1/create-user` – create a new WordPress user or retrieve the existing one.
* `/wp-json/mc/v1/assign-role` – assign a WordPress role to an existing user by email.
* `/wp-json/mc/v1/ping` – health-check endpoint to verify the connection and secret key.
* All endpoints are protected by a shared secret key (`X-MC-SECRET` header).
* Auto-generates a strong secret on activation.
* Settings page under **Settings → MC Remote API**.

This plugin is designed to work alongside [MC-Woo Remote Automations](https://mambacoding.com/) for a complete cross-site automation solution.

== Installation ==

1. Upload the `mc-remote-api` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → MC Remote API** to copy your auto-generated API secret.
4. Use that secret as the `X-MC-SECRET` header when calling the REST endpoints.

== Frequently Asked Questions ==

= How do I find my API secret? =

Go to **Settings → MC Remote API**. The secret is generated automatically on first activation.

= Can I regenerate the secret? =

Yes. Edit the value in **Settings → MC Remote API** and save.

= Is the API secure? =

All endpoints require the `X-MC-SECRET` header to match the stored secret. Communication should always happen over HTTPS.

= Which roles can be assigned? =

Any valid WordPress role slug (e.g. `customer`, `subscriber`, `editor`, `student`).

== Screenshots ==

1. Settings page showing the API secret and available endpoints.

== Changelog ==

= 1.1.0 =
* Reorganized plugin files to follow WordPress.org marketplace standards.
* Extracted classes into `includes/` directory.
* Added proper plugin header fields (License, Author URI, Domain Path).
* Added i18n support via `load_plugin_textdomain`.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
Structural refactor – no changes to API behaviour or stored data.
