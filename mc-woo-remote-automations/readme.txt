=== MC-Woo Remote Automations ===
Contributors: mambacoding
Tags: woocommerce, automation, remote, api, user-management
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.3
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically create users and assign roles on a remote WordPress/WooCommerce site when an order status changes.

== Description ==

MC-Woo Remote Automations lets you define **Automation** rules that fire whenever a WooCommerce order transitions to a specific status (e.g. "completed"). When triggered, the plugin calls the REST endpoints provided by [MC Remote API](https://mambacoding.com/) installed on the destination site to:

* **Create the customer** on the remote site if they do not yet have an account.
* **Assign a custom role** (e.g. `student`, `member`) to that user.

**Key features:**

* Visual Automation editor via custom post types – no code required.
* Multiple **Connections** (remote sites) supported simultaneously.
* Per-automation product filtering – trigger only for specific products.
* Per-automation timeout override.
* Built-in **Test Connection** button on each Connection record.
* Execution log table for debugging.
* Secure secret-key authentication via `X-MC-SECRET` HTTP header.

**Use cases:**

* Sell a course on your WooCommerce store → automatically enrol the buyer on a separate LMS site.
* Sell a membership → automatically upgrade the user's role on a community site.

== Installation ==

1. Make sure [MC Remote API](https://mambacoding.com/) is installed and activated on the **destination** WordPress site.
2. Upload the `mc-woo-remote-automations` folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** menu.
4. Go to **Woo Remote Automations → Connections** and add the destination site credentials.
5. Go to **Woo Remote Automations → Automations** and configure your automation rules.

== Frequently Asked Questions ==

= Do I need another plugin on the destination site? =

Yes. The destination WordPress site must have **MC Remote API** installed and activated.

= Can I have multiple destination sites? =

Yes. Create one Connection per destination site and assign each Automation to the desired Connection.

= What happens if the user already exists on the remote site? =

If "Create User If Missing" is enabled, the remote API will return a `user_exists` response which is treated as a success – no duplicate is created.

= Where can I see what happened? =

Go to **Woo Remote Automations → Logs** to see the execution history for all automations.

== Screenshots ==

1. Automations list table with connection, trigger status, and product count columns.
2. Automation editor showing all available settings.
3. Connection editor with Test Connection button.
4. Logs page showing execution history.

== Changelog ==

= 1.1.3 =
* Reorganized plugin files to follow WordPress.org marketplace standards.
* Extracted classes into `includes/` and `admin/` directories.
* Added proper plugin header fields (License, Author URI, WC version tags, Domain Path).
* Added i18n support via `load_plugin_textdomain`.
* Moved helper functions and logging logic to dedicated class files.

= 1.1.0 =
* Added per-automation timeout override.
* Added Test Connection button.
* Improved logging with request and response payload storage.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.3 =
Structural refactor – no changes to automation behaviour or stored data.
