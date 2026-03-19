=== MC-Woo Remote Automations ===
Contributors: mambacoding
Tags: woocommerce, automation, remote, api, user-management, roles, integration
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.1.3
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 4.0
WC tested up to: 8.0

Automatically create users and assign roles on a remote WordPress site whenever a WooCommerce order status changes.

== Description ==

**MC-Woo Remote Automations** bridges your WooCommerce store and any number of remote WordPress installations. When an order reaches a configured status (e.g. "completed"), the plugin calls the REST API provided by [MC Remote API](https://mambacoding.com/) on the destination site to provision a user account and assign the correct role — all without any manual intervention.

No custom code. No cron jobs. No third-party SaaS. Just two WordPress plugins talking directly to each other over HTTPS.

= Why use MC-Woo Remote Automations? =

* **Sell courses, memberships, or access passes on WooCommerce** and automatically enrol the buyer on a separate platform.
* **Manage roles across sites** — upgrade a subscriber to a member, activate a student account, or revoke access, all triggered by order events.
* **Multiple remote sites** — one plugin handles unlimited Connections simultaneously, each with its own credentials and endpoints.
* **Product-level granularity** — each Automation rule targets one or more specific products, so different products can trigger different actions on different remote sites.
* **Full audit trail** — every API call is logged with its request payload, HTTP response code, and response body so you can diagnose issues instantly.

= Key Features =

**Order-Driven Automation**

* Triggers on any WooCommerce order status transition (completed, processing, refunded, and any custom status).
* Multiple Automations can be active simultaneously; each fires independently when its trigger matches.
* Automations can be enabled or disabled individually without deleting the configuration.

**Remote User Provisioning**

* *Create User If Missing* — calls `POST /wp-json/mc/v1/create-user` with the customer's e-mail, first name, and last name. If the account already exists on the remote site, no duplicate is created and processing continues.
* *Assign Role* — calls `POST /wp-json/mc/v1/assign-role` with the customer's e-mail and the configured role slug (e.g. `student`, `member`, `subscriber`).
* Both actions can be combined in a single Automation or used independently.

**Multiple Connections**

* Each Connection record stores the remote site URL, API endpoint paths, and the shared secret.
* A built-in **Test Connection** button pings the remote site and displays the result immediately.
* Separate secrets can be configured for the create-user and assign-role endpoints if your security policy requires it.

**Execution Logs**

* Every API call is written to a dedicated database table.
* The **Woo Remote Automations → Logs** page shows: automation name, connection, order ID, customer e-mail, action, HTTP status, and the raw request and response bodies.
* Logs are invaluable for debugging failed automations without enabling WordPress debug mode.

**Timeout Control**

* A global default timeout (in seconds) is configurable under **Woo Remote Automations → Settings**.
* Individual Automations can override the global timeout for slow or high-latency remote sites.

= Typical Use Cases =

* **Online courses** — Sell a course on your WooCommerce store; automatically enrol the buyer on an LMS site (LearnDash, LifterLMS, Tutor LMS) with the correct role.
* **Membership sites** — Sell a membership subscription; automatically provision the account and upgrade the role on a separate community or membership platform.
* **Multi-site networks** — Keep user roles in sync across a network of WordPress installations without a shared database.
* **Access management** — Grant or revoke access to gated content on a partner site based on purchase events.

= Requirements =

* WordPress 5.0 or higher on **both** the source (WooCommerce) site and the destination site.
* WooCommerce 4.0 or higher on the **source** site.
* PHP 7.4 or higher on both sites.
* [MC Remote API](https://mambacoding.com/) installed and activated on each **destination** site.
* HTTPS recommended on both sites to keep API secrets secure in transit.

== Installation ==

= Step 1 — Install MC Remote API on the destination site =

1. Log in to the WordPress dashboard of the **destination** site (the site where users will be created).
2. Go to **Plugins → Add New**, search for **MC Remote API**, and install and activate it.
3. Go to **Settings → MC Remote API** and copy the auto-generated API secret.

= Step 2 — Install MC-Woo Remote Automations on the source site =

1. Log in to the WordPress dashboard of your **WooCommerce store** (the source site).
2. Go to **Plugins → Add New**, search for **MC-Woo Remote Automations**, and install and activate it.
3. The **Woo Remote Automations** menu will appear in the admin sidebar.

= Step 3 — Add a Connection =

1. Go to **Woo Remote Automations → Connections → Add Connection**.
2. Enter a descriptive name (e.g. "LMS Site").
3. Fill in:
   * **Remote Site URL** — the full URL of the destination site, e.g. `https://lms.example.com`.
   * **Create User Endpoint** — leave as default `/wp-json/mc/v1/create-user` unless customised.
   * **Assign Role Endpoint** — leave as default `/wp-json/mc/v1/assign-role` unless customised.
   * **Ping Endpoint** — leave as default `/wp-json/mc/v1/ping` unless customised.
   * **Create Secret** — paste the API secret you copied from the destination site.
   * **Role Secret** — leave blank to reuse the Create Secret, or enter a separate secret.
4. Check the **Enabled** box.
5. Click **Publish**, then click **Test Connection** to verify the credentials.

= Step 4 — Create an Automation =

1. Go to **Woo Remote Automations → Automations → Add Automation**.
2. Enter a descriptive name (e.g. "Enrol in Course A on LMS").
3. Fill in:
   * **Order Status Trigger** — select the WooCommerce status that should fire this automation (usually "completed").
   * **Products** — select one or more products that must appear in the order for the automation to fire. Hold Ctrl/Cmd to select multiple products.
   * **Connection** — choose the Connection you created in Step 3.
   * **Create User If Missing** — check this to create the user on the remote site if they do not yet have an account.
   * **Assign Role** — check this to assign a role after creating (or finding) the user.
   * **Remote Role** — enter the WordPress role slug to assign, e.g. `student`.
   * **Override Timeout** — optionally set a per-automation timeout in seconds; leave blank to use the global default.
4. Check the **Enabled** box.
5. Click **Publish**.

= Step 5 — Verify =

1. Place a test order on your WooCommerce store containing one of the configured products.
2. Manually transition the order to the trigger status from **WooCommerce → Orders → [order] → Order status**.
3. Go to **Woo Remote Automations → Logs** to confirm the automation fired and the API returned a successful response.
4. Log in to the destination site and confirm the user account and role are correct.

== Usage ==

= Managing Connections =

Navigate to **Woo Remote Automations → Connections**. Each Connection represents one remote site. You can have as many Connections as you need.

To test a Connection without placing a real order, open the Connection record and click **Test Connection**. A success or error notice will appear at the top of the page with the HTTP status code and response body.

= Managing Automations =

Navigate to **Woo Remote Automations → Automations**. The list table shows each automation's associated connection, order status trigger, product count, and the time it last fired.

Click an Automation to edit it. You can enable or disable an automation using the **Enabled** checkbox without deleting the record.

= Reading the Logs =

Navigate to **Woo Remote Automations → Logs**. Each row represents one API call and shows:

* **Date/time** of the call.
* **Automation** and **Connection** involved.
* **Order ID** that triggered it.
* **Action** (`create_user` or `assign_role`).
* **Customer e-mail**.
* **HTTP status code** returned by the remote API.
* **Message** from the response body.

The full request payload and response body are stored for deep debugging.

= Global Settings =

Navigate to **Woo Remote Automations → Settings** to configure the **Default Timeout**. This value (in seconds) is used by all automations that do not have an individual override.

== Frequently Asked Questions ==

= Do I need to install two plugins? =

Yes. Install **MC-Woo Remote Automations** on your WooCommerce store and **MC Remote API** on each destination site. Both plugins are free.

= Can I connect to multiple remote sites? =

Yes. Create one Connection record per remote site. Each Automation is linked to exactly one Connection, so you can route different products to different remote sites.

= What happens if the remote site is unreachable? =

The API call will time out after the configured number of seconds. The failure is recorded in the Logs with the WP_Error message. No retry is attempted automatically; you can re-trigger the automation by re-saving the order or transitioning its status again.

= What happens if the customer already has an account on the remote site? =

If **Create User If Missing** is enabled, the remote `create-user` endpoint returns `{ "code": "user_exists" }`, which is treated as a success. Processing continues with the role assignment step if configured.

= Can I assign different roles for different products? =

Yes. Create a separate Automation for each product/role combination. Each Automation has its own Remote Role field and its own product filter.

= Does the automation fire for every order status change? =

No. Each Automation has exactly one **Order Status Trigger**. It fires only when the order transitions *to* that status (not away from it). Changing an order from "completed" to "refunded" will not re-fire an automation with a "completed" trigger, but will fire any automation configured with a "refunded" trigger.

= Can I test without a real order? =

Yes — use the **Test Connection** button on a Connection record to verify that the secret and URL are correct. To test a full end-to-end flow, place a free or coupon-discounted test order and manually update its status in the WooCommerce order editor.

= Where are the logs stored? =

Logs are stored in a dedicated custom database table (`{prefix}mc_wra_log`) created when the plugin is activated. They are viewable from **Woo Remote Automations → Logs**.

= Does the plugin work with WooCommerce Subscriptions? =

The plugin hooks into `woocommerce_order_status_changed`, which fires for both standard and subscription orders. Select the relevant status (e.g. `active`) to trigger automations on subscription renewals or activations.

= Is the plugin GDPR-compliant? =

Customer e-mail addresses and names are transmitted to the remote site via the REST API. Ensure both sites are configured appropriately for your jurisdiction and that your privacy policy discloses this data transfer.

= The automation fires but the user is not created — what should I check? =

1. Open **Woo Remote Automations → Logs** and inspect the response body for the failed call.
2. Confirm the Connection is **Enabled** and the secret matches the one in **Settings → MC Remote API** on the destination site.
3. Use the **Test Connection** button on the Connection record to rule out network or credential issues.
4. Confirm that the destination site's permalink structure is set to something other than **Plain** (REST API requires pretty permalinks).

== Screenshots ==

1. **Automations list** — All configured automations with connection name, order status trigger, product count, and last-run timestamp.
2. **Automation editor** — All settings for one automation: status trigger, products, connection, user creation and role assignment options, and timeout override.
3. **Connection editor** — Remote site URL, endpoint paths, API secrets, and the Test Connection button.
4. **Logs page** — Full execution history with HTTP status codes and request/response payloads.
5. **Settings page** — Global default timeout configuration.

== Changelog ==

= 1.1.3 =
* Reorganized plugin files to follow WordPress.org marketplace standards.
* Extracted classes into `includes/` and `admin/` directories for better maintainability.
* Added proper plugin header fields: License URI, Author URI, WC version tags, Domain Path.
* Added i18n support via `load_plugin_textdomain` for full translation-readiness.
* Moved helper functions and logging logic to dedicated class files.
* No changes to automation behaviour or stored data.

= 1.1.0 =
* Added per-automation timeout override field.
* Added **Test Connection** button to Connection records with inline pass/fail notice.
* Improved execution logging: request payload and raw response body are now stored alongside the status code and message.

= 1.0.0 =
* Initial release.
* Custom post types `mcwra_connection` and `mcwra_automation` for visual configuration.
* Order status trigger with per-automation product filtering.
* Remote user creation via MC Remote API `create-user` endpoint.
* Remote role assignment via MC Remote API `assign-role` endpoint.
* Execution log table for debugging.
* Admin menu under **Woo Remote Automations** with Automations, Connections, Logs, and Settings sub-pages.

== Upgrade Notice ==

= 1.1.3 =
Structural refactor only — no changes to automation behaviour, trigger logic, API calls, or any stored data. Safe to update without re-configuring connections or automations.
