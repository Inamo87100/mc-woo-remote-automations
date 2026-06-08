=== MC-Woo Remote Automations ===
Contributors: mambacoding
Tags: woocommerce, automation, remote, api, user-management, roles, integration
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.7
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 4.0
WC tested up to: 9.0

Automatically create users and assign roles on a remote WordPress site whenever a WooCommerce order status changes.

== Description ==

**MC-Woo Remote Automations** bridges your WooCommerce store and any number of remote WordPress installations. When an order reaches a configured status (e.g. "completed"), the plugin calls secure REST API endpoints provided by the same plugin installed on the destination site to provision a user account and assign the correct role — all without any manual intervention.

No custom code. No cron jobs. No third-party SaaS. Just the same WordPress plugin installed on both sites and communicating directly over HTTPS.

= Why use MC-Woo Remote Automations? =

* **Sell courses, memberships, or access passes on WooCommerce** and automatically enrol the buyer on a separate platform.
* **Manage roles across sites** — upgrade a subscriber to a member, activate a student account, or revoke access, all triggered by order events.
* **Multiple remote sites** — one plugin handles unlimited Connections simultaneously, each with its own credentials and endpoints.
* **Product-level granularity** — each Automation rule targets one or more specific products, so different products can trigger different actions on different remote sites.
* **Full audit trail** — every API call is logged with status, response code, and redacted payload/response previews for troubleshooting.

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
* The **Woo Remote Automations → Logs** screen provides filterable execution history with status, response code, and payload details.
* Request/response previews in the UI redact sensitive values to reduce accidental exposure in the admin area.
* Built-in cleanup tools let you purge old logs based on retention days or clear all logs explicitly.

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
* MC-Woo Remote Automations installed and activated on each **destination** site in Remote API mode.
* HTTPS is required for remote URLs (HTTP is accepted only for `localhost` or `127.0.0.1` development targets).

== Installation ==

= Step 1 — Install this plugin on the destination site =

1. Log in to the WordPress dashboard of the **destination** site where users will be created or updated.
2. Install and activate **MC-Woo Remote Automations**.
3. Go to **Woo Remote Automations → Settings**.
4. Set **Operating mode** to **Remote API / Destination site**.
5. Copy the **Remote API Secret** shown on the settings page.

= Step 2 — Install this plugin on the source site =

1. Log in to the WordPress dashboard of your **WooCommerce store** (the source site).
2. Go to **Plugins → Add New**, search for **MC-Woo Remote Automations**, and install and activate it.
3. The **Woo Remote Automations** menu will appear in the admin sidebar. Set **Operating mode** to **Controller / Source site** under Settings.

= Step 3 — Add a Connection =

1. Go to **Woo Remote Automations → Connections → Add Connection**.
2. Enter a descriptive name (e.g. "LMS Site").
3. Fill in:
   * **Remote Site URL** — the full URL of the destination site, e.g. `https://lms.example.com`.
   * **Create User Endpoint** — leave as default `/wp-json/mc/v1/create-user` unless customised.
   * **Assign Role Endpoint** — leave as default `/wp-json/mc/v1/assign-role` unless customised.
   * **Ping Endpoint** — leave as default `/wp-json/mc/v1/ping` unless customised.
   * **Remote API Secret** — paste the API secret you copied from the destination site.
4. Check the **Enabled** box.
5. Click **Save & Test Connection** to save the Connection and immediately verify the credentials.

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

Navigate to **Woo Remote Automations → Logs** to review execution history.

The screen includes filters (status/action/search), pagination, and expandable request/response payload views for each entry.

Execution data is recorded for each API call in `{prefix}mc_wra_logs`, including:

* Date/time
* Automation and connection IDs
* Order ID and customer e-mail
* Action key (`create_user` or `assign_role`)
* Status, HTTP response code, message
* Redacted and minimized request/response previews

= Global Settings =

Navigate to **Woo Remote Automations → Settings** to configure:

* **Operating mode** (`controller`, `remote`, `both`) to control whether the site runs controller automation hooks, remote API routes, or both.
* **Default Timeout** (seconds) used by automations without an override.
* **Log Retention (days)** used by the Logs cleanup action (set `0` to disable retention-based cleanup).
* **Delete data on uninstall** to opt in to full data removal when uninstalling the plugin.

== Frequently Asked Questions ==

= Do I need to install a separate companion plugin? =

No. Install **MC-Woo Remote Automations** on both sites. Use **Controller** mode on the WooCommerce source site and **Remote API** mode on the destination site (or **Both** when one site needs both roles).

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

Yes — use the **Save & Test Connection** button on a Connection record to verify that the secret and URL are correct. To test a full end-to-end flow, place a free or coupon-discounted test order and manually update its status in the WooCommerce order editor.

= Where are the logs stored? =

Logs are stored in a dedicated custom database table (`{prefix}mc_wra_logs`) created when the plugin is activated. They are viewable from **Woo Remote Automations → Logs**.

= How can I clean up old logs? =

Use **Woo Remote Automations → Logs**:
1. Configure retention days in **Settings**.
2. Click **Delete logs older than retention** to purge old records.
3. Use **Clear all logs** (with explicit confirmation) for a full reset.

= What happens on uninstall? =

By default, uninstall keeps plugin data. If you enable **Delete data on uninstall** in Settings, uninstall removes:
* Connections
* Automations
* Log table (`{prefix}mc_wra_logs`)
* Plugin settings

= Does the plugin work with WooCommerce Subscriptions? =

The plugin hooks into `woocommerce_order_status_changed`, which fires for both standard and subscription orders. Select the relevant status (e.g. `active`) to trigger automations on subscription renewals or activations.

= Is the plugin GDPR-compliant? =

Customer e-mail addresses and names are transmitted to the remote site via the REST API. The plugin also stores execution logs for troubleshooting, and the Logs UI redacts sensitive values in payload previews. Configure a retention policy and document this transfer/processing in your privacy policy as required by your jurisdiction.

= The automation fires but the user is not created — what should I check? =

1. Open **Woo Remote Automations → Logs** and inspect the response body for the failed call.
2. Confirm the Connection is **Enabled** and the secret matches the one in **Woo Remote Automations → Settings** on the destination site.
3. Use the **Save & Test Connection** button on the Connection record to rule out network or credential issues.
4. Confirm that the destination site's permalink structure is set to something other than **Plain** (REST API requires pretty permalinks).

== Screenshots ==

1. **Automations list** — All configured automations with connection name, order status trigger, product count, and last-run timestamp.
2. **Automation editor** — All settings for one automation: status trigger, products, connection, user creation and role assignment options, and timeout override.
3. **Connection editor** — Remote site URL, endpoint paths, API secrets, and the Save & Test Connection button.
4. **Logs page** — Filterable execution history with payload detail view and cleanup actions.

== External Services ==

This plugin communicates with remote WordPress sites where **MC-Woo Remote Automations** is installed and set to Remote API mode. No data is sent to any third-party or Mamba Coding servers.

**When does it connect?**

* When an order changes status and an Automation is triggered, the plugin sends a request to the remote site URL you configured in the Connection record (HTTPS required, except `localhost`/`127.0.0.1` development targets).
* When you click the **Save & Test Connection** button, the plugin sends a ping request to the remote site.

**What data is sent?**

* Customer billing e-mail address, first name, and last name (for the create-user action).
* Customer billing e-mail address and a role slug you chose (for the assign-role action).
* A shared API secret (in the request header) that you configured.

**Where does it go?**

Data is sent exclusively to the remote site URL you enter in each Connection record — a WordPress site you own and control. No data is ever sent to Mamba Coding or any other third party.

The remote site must run this plugin in Remote API mode. By using this plugin you take responsibility for the data transfer between your sites. Document this processing in your privacy policy as required by applicable law (e.g. GDPR).

== Changelog ==

= 1.2.7 =
* Restricted Connection and Automation post type capabilities to administrators.
* Enforced secure remote URL validation (HTTPS required, localhost/127.0.0.1 HTTP allowed for development).
* Applied operating mode gating so controller and remote behaviors are loaded only when selected.
* Executed only published automations.
* Reduced persisted log sensitivity by storing redacted/minimized payload and response previews.
* Internationalized remaining user-facing REST API error messages.
* Removed promotional admin banner runtime behavior.
* Updated documentation for current single-plugin architecture and metadata consistency.

= 1.2.0 =
* Integrated the remote API functionality into the main plugin so the same plugin can be installed on source and destination sites.
* Added operating mode and Remote API Secret settings.
* Removed the requirement for a separate companion plugin.


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
* Improved execution logging with request and response details in the Logs screen.

= 1.0.0 =
* Initial release.
* Custom post types `mcwra_connection` and `mcwra_automation` for visual configuration.
* Order status trigger with per-automation product filtering.
* Remote user creation via the built-in `create-user` endpoint.
* Remote role assignment via the built-in `assign-role` endpoint.
* Execution log table for debugging.
* Admin menu under **Woo Remote Automations** with Automations, Connections, Logs, and Settings sub-pages.

== Upgrade Notice ==

= 1.2.7 =
Includes WordPress.org submission-readiness updates for access control, secure connection validation, operating mode enforcement, safer logging defaults, and documentation consistency.
