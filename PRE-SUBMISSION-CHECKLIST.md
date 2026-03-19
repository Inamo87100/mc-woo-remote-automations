# Pre-Submission Checklist

Complete every item on this checklist before submitting to the WordPress.org Plugin Directory.

---

## Plugin Identity

- [ ] Plugin slug is available: `mc-woo-remote-automations` ([check](https://wordpress.org/plugins/mc-woo-remote-automations/))
- [ ] Plugin slug is available: `mc-remote-api` ([check](https://wordpress.org/plugins/mc-remote-api/))
- [ ] Plugin name does not infringe on any trademarks

## readme.txt

- [ ] `readme.txt` follows [WordPress.org format](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)
- [ ] `Stable tag` matches the current release version
- [ ] Short description is ≤ 150 characters
- [ ] `Tested up to` reflects the latest tested WordPress version
- [ ] `Requires at least` is set (minimum: 5.0)
- [ ] `Requires PHP` is set (minimum: 7.4)
- [ ] At least one `== Screenshots ==` entry (if screenshots are provided)
- [ ] FAQ section answers the most common questions
- [ ] Changelog section is populated

## Plugin Header (main PHP file)

- [ ] `Plugin Name` is set
- [ ] `Description` is clear and under 150 characters
- [ ] `Version` matches `Stable tag` in readme.txt
- [ ] `Author` and `Author URI` are set
- [ ] `License: GPL v2 or later` is set
- [ ] `Text Domain` matches the plugin slug
- [ ] `Domain Path: /languages` is set
- [ ] `Requires at least` is set
- [ ] `Requires PHP` is set

## Code Quality

- [ ] No commercial/premium language in plugin code (freemium call-to-actions are OK)
- [ ] PHPCS passes with WordPress Coding Standards: `phpcs`
- [ ] No hardcoded debug code (`var_dump`, `print_r`, `error_log` without conditions)
- [ ] No dead/commented-out code blocks
- [ ] All functions and classes have docblocks

## Security

- [ ] All user inputs sanitized (`sanitize_text_field`, `intval`, `wp_kses`, etc.)
- [ ] All outputs escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, etc.)
- [ ] Nonces used for all form submissions (`wp_nonce_field`, `check_admin_referer`)
- [ ] Capability checks on all admin actions (`current_user_can`)
- [ ] SQL queries use `$wpdb->prepare()`
- [ ] No direct file inclusion without path validation
- [ ] No `eval()` or `base64_decode()` of user input
- [ ] REST API endpoints have proper permission callbacks

## Licensing

- [ ] License is GPL v2 or later
- [ ] `LICENSE` file present in plugin root
- [ ] Any third-party code/libraries are GPL-compatible

## Compatibility

- [ ] Plugin tested on WordPress 5.0+
- [ ] Plugin tested on PHP 7.4+
- [ ] Plugin tested with WooCommerce 3.0+ (where applicable)
- [ ] No deprecated WordPress functions used

## Internationalisation

- [ ] All user-facing strings use `__()`, `_e()`, `esc_html__()`, etc.
- [ ] Correct text domain used in all gettext calls
- [ ] `.pot` file generated (or at minimum, domain is loaded with `load_plugin_textdomain`)

## Database

- [ ] No database tables without WordPress table prefix (`$wpdb->prefix`)
- [ ] Tables created/updated using `dbDelta()`
- [ ] Uninstall hook removes all plugin data from the database

## Assets

- [ ] `icon-128x128.png` or `icon.svg` present in `assets/`
- [ ] `banner-772x250.png` or `banner.svg` present in `assets/`
- [ ] Screenshots are PNG and properly numbered (`screenshot-1.png`, etc.)
- [ ] All images optimised for web

## External Services

- [ ] No external dependencies that are not WordPress.org hosted (or clearly documented)
- [ ] No auto-updates from non-WordPress.org sources
- [ ] No hidden outbound HTTP requests
- [ ] External API calls documented in readme.txt

## Other

- [ ] No URL redirects in the main plugin file on activation
- [ ] Plugin deactivation/uninstall hooks implemented
- [ ] No output buffering issues
- [ ] Plugin does not modify global WordPress settings without user consent
