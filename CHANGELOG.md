# Changelog

All notable changes to the MC Plugins suite are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Added
- Professional SVG assets (icon and banner) for both plugins
- Comprehensive documentation suite (`docs/` directories)
- PHP unit test structure (`tests/` directories)
- WordPress Coding Standards configuration (`.phpcs.xml.dist`)
- GitHub Actions CI workflow for automated PHPCS checks
- Root-level documentation: `README.md`, `COMPATIBILITY.md`, `TESTING.md`, `VALIDATION.md`, `STANDARDS.md`, `CONTRIBUTING.md`

---

## MC Remote API

### [1.1.0] – 2024-01-01

#### Added
- `/ping` endpoint for connection testing
- Constant-time secret comparison using `hash_equals()`
- Auto-generation of API secret on plugin activation
- Admin settings page under Settings → MC Remote API

#### Changed
- Passwords for new users generated with `wp_generate_password()` (not email-derived)
- Role validation uses `wp_roles()->is_role()` for strict checking

#### Fixed
- Invalid role slug now falls back to `customer` in `/create-user`

---

## MC-Woo Remote Automations

### [1.1.3] – 2024-01-01

#### Added
- Per-automation timeout configuration
- Support for separate secrets for create-user and assign-role actions
- `allow_exists` logic treats "user_exists" API response as success

#### Changed
- Refactored to class-based architecture
- Log table created via `dbDelta()` on activation

#### Fixed
- Handling of disabled Connections (skipped gracefully)
- Array cast for product_ids meta ensures integer comparison

### [1.0.0] – 2023-06-01

#### Added
- Initial release
- WooCommerce order status hook integration
- mcwra_connection and mcwra_automation custom post types
- Execution log database table
