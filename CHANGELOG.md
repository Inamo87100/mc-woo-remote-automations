# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

---

## mc-remote-api 1.1.0 / mc-woo-remote-automations 1.1.3 — 2025-01-01

### Added
- WordPress.org marketplace-compliant structure for both plugins.
- `includes/class-admin-banner.php` — promotional banner extracted to its own file.
- `includes/class-main.php` — core plugin logic extracted to its own file.
- `includes/functions.php` — global helper functions.
- `includes/class-helpers.php` (mc-woo-remote-automations) — HTTP + logging helpers.
- `admin/class-admin.php` (mc-woo-remote-automations) — all admin UI logic.
- `readme.txt` for both plugins (WordPress.org format).
- `LICENSE` (GPL v2) for both plugins.
- `.gitignore` for both plugins and repository root.
- Plugin constants: `MC_REMOTE_API_VERSION`, `MC_REMOTE_API_PATH`, `MC_REMOTE_API_URL`, `MC_REMOTE_API_FILE`.
- Plugin constants: `MC_WOO_REMOTE_VERSION`, `MC_WOO_REMOTE_PATH`, `MC_WOO_REMOTE_URL`, `MC_WOO_REMOTE_FILE`.
- i18n bootstrap via `load_plugin_textdomain` on `plugins_loaded`.
- `languages/` directory for translation files.
- Empty `admin/css/`, `admin/js/`, `public/css/`, `public/js/` placeholder directories.

### Changed
- Main plugin files are now thin loaders that require the class files from `includes/` and `admin/`.
- Plugin headers updated with `Author URI`, `License`, `License URI`, `Domain Path`, and WooCommerce version tags.
- `MC_Remote_API` class renamed to `MC_Remote_API_Main`.
- Internal method names aligned with WordPress coding standards.
- Logo URL now uses the `MC_REMOTE_API_URL` / `MC_WOO_REMOTE_URL` constants instead of `plugin_dir_url(__FILE__)`.

---

## mc-remote-api 1.0.0 / mc-woo-remote-automations 1.0.0 — 2024-01-01

### Added
- Initial release.
- REST API endpoints: `/create-user`, `/assign-role`, `/ping`.
- WooCommerce order-status automation with Connection and Automation CPTs.
- Admin banner with premium upgrade prompt.
- Execution log table.
