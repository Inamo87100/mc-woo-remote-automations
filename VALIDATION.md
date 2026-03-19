# Pre-Submission Validation Checklist

Use this checklist before submitting either plugin to WordPress.org or deploying to production.

---

## WordPress.org Requirements

### Plugin Files
- [ ] Main plugin file with correct plugin header (Plugin Name, Version, Author, License, Text Domain)
- [ ] `readme.txt` in WordPress.org format with all required sections
- [ ] `LICENSE` file (GPL v2 or later)
- [ ] No executable files (`.sh`, `.bat`) in plugin root
- [ ] No ZIP archives included in the plugin

### readme.txt Validation
- [ ] `Requires at least:` field set (minimum: 5.0)
- [ ] `Tested up to:` field matches current WordPress version
- [ ] `Requires PHP:` field set (minimum: 7.4)
- [ ] `Stable tag:` matches the version in the plugin header
- [ ] Short description is under 150 characters
- [ ] At least one screenshot documented
- [ ] Changelog section present

---

## Security Validation

- [ ] All user inputs sanitised (`sanitize_email()`, `sanitize_text_field()`, etc.)
- [ ] All database inputs use `$wpdb->prepare()` or `$wpdb->insert()`
- [ ] All HTML output escaped (`esc_html()`, `esc_attr()`, `wp_kses_post()`)
- [ ] Admin pages check `current_user_can()` capability
- [ ] Nonces used on all admin form submissions
- [ ] No hardcoded credentials or secrets
- [ ] No use of `eval()`, `exec()`, `shell_exec()`, or `system()`
- [ ] Direct file access blocked: `if ( ! defined( 'ABSPATH' ) ) { exit; }`

---

## Compatibility Validation

- [ ] Tested on WordPress 6.4+ ✅
- [ ] Tested on WordPress 5.0 ✅
- [ ] Tested on PHP 8.2 ✅
- [ ] Tested on PHP 7.4 ✅
- [ ] No PHP deprecation warnings on PHP 8.x
- [ ] WooCommerce 9.x compatibility (mc-woo-remote-automations only)

---

## Documentation Validation

- [ ] `docs/` directory present with at minimum SETUP.md and SECURITY.md
- [ ] API endpoints documented (mc-remote-api only)
- [ ] Screenshots present in `assets/screenshots/`
- [ ] `README.md` present at repository root
- [ ] `CONTRIBUTING.md` present

---

## Asset Validation

- [ ] `assets/icon.svg` (128×128px)
- [ ] `assets/banner.svg` (772×250px)
- [ ] At least one screenshot in `assets/screenshots/`
- [ ] SVG files are valid and render correctly in browsers
- [ ] No copyrighted images used

---

## License Validation

- [ ] `LICENSE` file present and is GPL v2 or later
- [ ] Plugin header `License: GPL v2 or later` matches
- [ ] Plugin header `License URI:` points to https://www.gnu.org/licenses/gpl-2.0.html
- [ ] No GPL-incompatible dependencies included

---

## Code Quality Validation

- [ ] WordPress Coding Standards pass: `phpcs --standard=.phpcs.xml.dist`
- [ ] No debug code present (`var_dump`, `print_r`, `die`, `error_log` in production paths)
- [ ] Plugin prefix used consistently (`mc_`, `MC_`, `mc-`) to avoid conflicts
- [ ] Text domain matches slug in all `__()`, `_e()`, `esc_html__()` calls
- [ ] `load_plugin_textdomain()` called on `plugins_loaded` hook

---

## Final Pre-Deployment Checks

- [ ] Version numbers match in plugin header and `readme.txt`
- [ ] All new features documented in changelog
- [ ] No TODO or FIXME comments left in production code
- [ ] Plugin activates and deactivates without errors
- [ ] No PHP fatal errors in `wp-content/debug.log`
