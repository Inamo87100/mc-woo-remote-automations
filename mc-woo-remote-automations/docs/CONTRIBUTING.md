# Contributing to MC-Woo Remote Automations

Thank you for your interest in contributing! Please read these guidelines before submitting a pull request.

---

## Development Setup

1. **Clone the repository:**

```bash
git clone https://github.com/Inamo87100/mc-woo-remote-automations.git
cd mc-woo-remote-automations/mc-woo-remote-automations
```

2. **Install PHP code-quality tools:**

```bash
composer global require squizlabs/php_codesniffer
composer global require wp-coding-standards/wpcs
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```

3. **Install WooCommerce for local development:**
   - Use [LocalWP](https://localwp.com/) or Docker with WooCommerce.
   - Activate WooCommerce and MC Remote API on a second local site.

---

## Code Standards

This plugin follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

Key conventions:
- Tabs for indentation.
- Yoda conditions: `'value' === $variable`.
- All public methods documented with PHPDoc.
- All output escaped: `esc_html()`, `esc_attr()`, `wp_kses_post()`.
- All database inputs use `$wpdb->prepare()` or appropriate sanitisation.
- No debug code (`var_dump`, `print_r`, `error_log`) in pull requests.

---

## Testing Procedures

1. Set up two local WordPress sites:
   - **Site A (WooCommerce)**: MC-Woo Remote Automations installed.
   - **Site B (API)**: MC Remote API installed.
2. Create a Connection from Site A pointing to Site B.
3. Create an Automation mapped to a test product.
4. Place a test WooCommerce order and verify execution logs.
5. Run WordPress Coding Standards: `phpcs --standard=../.phpcs.xml.dist`.

---

## Pull Request Process

1. Fork the repository and create a branch: `feature/your-feature` or `fix/your-fix`.
2. Follow the code standards above.
3. Ensure existing automations, connections, and logs still work correctly.
4. Update documentation if behaviour changes.
5. Submit a pull request against `main` with a clear description of your changes.

---

## Reporting Issues

Open an issue at [https://github.com/Inamo87100/mc-woo-remote-automations/issues](https://github.com/Inamo87100/mc-woo-remote-automations/issues) with:
- WordPress and WooCommerce versions
- PHP version
- Description of the issue and steps to reproduce
- Any relevant log entries
