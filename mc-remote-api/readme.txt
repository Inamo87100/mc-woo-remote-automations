=== MC Remote API ===
Contributors: mambacoding
Tags: api, rest-api, user-management, automation, integration, role
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Expose secure REST API endpoints for remote user creation and role assignment across WordPress installations.

== Description ==

**MC Remote API** turns any WordPress site into a headless user-management backend. Install it on the *destination* site and it exposes three secure REST endpoints that let any authorised external service — including [MC-Woo Remote Automations](https://mambacoding.com/) — create users and manage their roles programmatically, with no manual admin work required.

= Why use MC Remote API? =

* **Cross-site user provisioning** — enrol students, activate memberships, or grant access on a remote WordPress site automatically.
* **Zero-friction integration** — a single shared-secret header is all the authentication you need.
* **No external dependencies** — built entirely on the native WordPress REST API, no SDK required.
* **Works with any WooCommerce automation layer** — pairs perfectly with MC-Woo Remote Automations for end-to-end order-driven automation.

= Key Features =

**REST API Endpoints**

* `POST /wp-json/mc/v1/create-user` — Create a new WordPress user (or silently return the existing one if the e-mail is already registered). Accepts `user_email`, `first_name`, `last_name`, and `role`.
* `POST /wp-json/mc/v1/assign-role` — Assign any valid WordPress role to an existing user identified by e-mail. Accepts `email` and `role`.
* `GET /wp-json/mc/v1/ping` — Health-check endpoint to verify that the connection and secret key are valid before running real automations.

**Authentication & Security**

* All three endpoints are protected by a shared secret transmitted in the `X-MC-SECRET` HTTP request header.
* The secret is compared using `hash_equals()` to prevent timing attacks.
* A cryptographically strong 32-character secret is auto-generated on plugin activation — no manual setup needed.
* The secret can be rotated at any time from **Settings → MC Remote API**.
* Always deploy over HTTPS to keep the secret in transit encrypted.

**Admin Settings Page**

* View or update the API secret from **Settings → MC Remote API**.
* The settings page also lists all registered endpoint URLs for quick reference.

= Typical Use Cases =

* Sell an online course on your WooCommerce store → automatically create the buyer's account on your LMS site.
* Sell a community membership → automatically upgrade the buyer's role on your membership platform.
* Synchronise user accounts across a multisite network or separate WordPress installations.
* Automate role changes from any external system capable of making HTTP requests.

= API Reference =

**Authentication**

Every request must include the following HTTP header:

`X-MC-SECRET: your-secret-key`

Requests missing the header or sending the wrong value receive a `401 Unauthorized` response.

**POST /wp-json/mc/v1/create-user**

Request body (JSON):

`{ "user_email": "jane@example.com", "first_name": "Jane", "last_name": "Doe", "role": "student" }`

Possible responses:

* `{ "success": true, "code": "user_created", "user_id": 42 }` — User was created successfully.
* `{ "success": true, "code": "user_exists", "user_id": 42 }` — E-mail is already registered; the existing user ID is returned.
* `{ "success": false, "message": "Invalid email" }` — Validation failed.

**POST /wp-json/mc/v1/assign-role**

Request body (JSON):

`{ "email": "jane@example.com", "role": "member" }`

Possible responses:

* `{ "success": true, "code": "role_assigned", "role": "member" }` — Role assigned successfully.
* `{ "success": false, "message": "User not found" }` — No user with that e-mail exists.

**GET /wp-json/mc/v1/ping**

No request body required.

Response:

* `{ "success": true, "plugin": "MC Remote API" }` — Connection is valid.

= Compatible With =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Any plugin or service that can send authenticated HTTP requests
* [MC-Woo Remote Automations](https://mambacoding.com/) — the companion WooCommerce automation plugin

== Installation ==

= Automatic Installation (Recommended) =

1. Log in to your WordPress dashboard.
2. Go to **Plugins → Add New**.
3. Search for **MC Remote API**.
4. Click **Install Now**, then **Activate**.
5. Go to **Settings → MC Remote API** to find your auto-generated API secret.

= Manual Installation =

1. Download the plugin zip file.
2. Log in to your WordPress dashboard and go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**, then **Activate**.
4. Go to **Settings → MC Remote API** to find your auto-generated API secret.

= FTP Installation =

1. Unzip the plugin archive on your computer.
2. Upload the `mc-remote-api` folder to your `/wp-content/plugins/` directory via FTP.
3. Log in to your WordPress dashboard and activate **MC Remote API** from the **Plugins** menu.
4. Go to **Settings → MC Remote API** to find your auto-generated API secret.

= Post-Activation Checklist =

* [ ] Confirm the API secret is displayed under **Settings → MC Remote API**.
* [ ] Test the ping endpoint from the remote site using MC-Woo Remote Automations' **Test Connection** button.
* [ ] Ensure the destination site is accessible over HTTPS.

== Usage ==

= cURL Examples =

**Ping (connection test)**

`curl -X GET "https://your-destination-site.com/wp-json/mc/v1/ping" -H "X-MC-SECRET: your-secret-key"`

**Create a user**

`curl -X POST "https://your-destination-site.com/wp-json/mc/v1/create-user" -H "Content-Type: application/json" -H "X-MC-SECRET: your-secret-key" -d '{"user_email":"jane@example.com","first_name":"Jane","last_name":"Doe","role":"student"}'`

**Assign a role**

`curl -X POST "https://your-destination-site.com/wp-json/mc/v1/assign-role" -H "Content-Type: application/json" -H "X-MC-SECRET: your-secret-key" -d '{"email":"jane@example.com","role":"member"}'`

= Configuration =

1. **Find the API secret** — Go to **Settings → MC Remote API**. The 32-character secret was generated automatically when you activated the plugin.
2. **Copy the secret** — You will need to paste it into the Connection settings of [MC-Woo Remote Automations](https://mambacoding.com/) on the source (WooCommerce) site.
3. **Rotate the secret** — Edit the value in the text field and click **Save Changes**. Update the secret in MC-Woo Remote Automations as well.
4. **Verify the endpoints** — The settings page lists all three endpoint URLs. Use the ping URL to perform a quick sanity check.

== Frequently Asked Questions ==

= Do I install this plugin on every WordPress site? =

Install MC Remote API only on the **destination** sites — the sites where users need to be created or have their roles updated. The source site (your WooCommerce store) uses [MC-Woo Remote Automations](https://mambacoding.com/) instead.

= How do I find my API secret? =

Go to **Settings → MC Remote API**. The secret is generated automatically on first activation. You can view, copy, or change it there at any time.

= Can I regenerate or change the secret? =

Yes. Open **Settings → MC Remote API**, edit the value in the API Secret field, and click **Save Changes**. Remember to update the corresponding field in MC-Woo Remote Automations on the source site, otherwise the connection will return `401 Unauthorized`.

= Is the API secure? =

All endpoints require the `X-MC-SECRET` header to match the stored secret exactly. The comparison uses `hash_equals()` to protect against timing attacks. Always use HTTPS so the secret cannot be intercepted in transit.

= Which WordPress roles can be assigned? =

Any valid WordPress role slug can be used — for example `customer`, `subscriber`, `editor`, `student`, `member`. Custom roles registered by third-party plugins (such as LearnDash's `group_leader`) are also supported as long as the slug is correct.

= What happens if a user already exists? =

The `/create-user` endpoint checks for an existing account with the supplied e-mail address. If one is found it returns `{ "success": true, "code": "user_exists" }` without creating a duplicate or modifying the existing account.

= Can multiple source sites call the same destination site? =

Yes. Any authorised site that knows the secret can call the endpoints. All three endpoints are stateless and thread-safe.

= The ping returns a 401 — what is wrong? =

The most common cause is a mismatch between the secret stored in MC Remote API and the secret entered in MC-Woo Remote Automations. Go to **Settings → MC Remote API**, copy the secret, paste it into the Connection record on the source site, and test again.

= Does the plugin require WooCommerce? =

No. MC Remote API is a standalone plugin that works on any WordPress site. WooCommerce is only needed on the source site, not on the destination site that hosts the API.

= Does the plugin send e-mail notifications to new users? =

No. User creation via the REST API is silent by default — no welcome e-mail is dispatched.

= Is the plugin compatible with WordPress Multisite? =

The plugin can be network-activated but each site has its own independent API secret and user database.

== Screenshots ==

1. **Settings page** — View and manage the API secret. All endpoint URLs are listed for easy reference.
2. **Endpoint list** — The three available REST endpoints displayed on the settings page.
3. **Successful ping response** — JSON response confirming a valid connection.

== Changelog ==

= 1.1.0 =
* Reorganized plugin files to follow WordPress.org marketplace standards.
* Extracted classes into `includes/` directory for better maintainability.
* Added proper plugin header fields: License URI, Author URI, Domain Path.
* Added i18n support via `load_plugin_textdomain` for full translation-readiness.
* No changes to API behaviour or stored data.

= 1.0.0 =
* Initial release.
* REST endpoint `POST /wp-json/mc/v1/create-user` for remote user creation.
* REST endpoint `POST /wp-json/mc/v1/assign-role` for remote role assignment.
* REST endpoint `GET /wp-json/mc/v1/ping` for connection health checks.
* Shared-secret authentication via `X-MC-SECRET` header with `hash_equals()` protection.
* Auto-generated 32-character API secret on activation.
* Admin settings page under **Settings → MC Remote API**.

== Upgrade Notice ==

= 1.1.0 =
Structural refactor only — no changes to API behaviour, endpoint URLs, secret storage, or any stored data. Safe to update without re-configuring the plugin.
