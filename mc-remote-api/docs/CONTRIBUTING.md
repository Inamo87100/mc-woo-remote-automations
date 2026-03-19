# Contributing to MC Remote API

Thank you for your interest in contributing! Please read these guidelines before submitting a pull request.

---

## Development Setup

1. **Clone the repository:**

```bash
git clone https://github.com/Inamo87100/mc-woo-remote-automations.git
cd mc-woo-remote-automations/mc-remote-api
```

2. **Install PHP code-quality tools:**

```bash
composer global require squizlabs/php_codesniffer
composer global require wp-coding-standards/wpcs
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```

3. **Run the code sniffer:**

```bash
phpcs --standard=../.phpcs.xml.dist mc-remote-api.php includes/
```

---

## Code Standards

This plugin follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

Key conventions:
- Tabs for indentation (not spaces).
- Opening braces on the same line as control structures.
- Yoda conditions: `'value' === $variable`.
- All public functions must have PHPDoc blocks.
- All output must be escaped (`esc_html()`, `esc_attr()`, `wp_kses_post()`).
- All database inputs must be prepared or sanitised.

---

## Testing Procedures

1. Set up a local WordPress environment (e.g., LocalWP, WP-CLI, or Docker).
2. Activate the plugin and verify no PHP errors appear.
3. Test all endpoints using the examples in [`docs/EXAMPLES.md`](EXAMPLES.md).
4. Run manual test cases from [`docs/TESTING.md`](TESTING.md).
5. Verify WordPress Coding Standards pass: `phpcs --standard=../.phpcs.xml.dist`.

---

## Pull Request Process

1. Fork the repository and create a branch named `feature/your-feature` or `fix/your-fix`.
2. Make your changes following the code standards above.
3. Ensure all existing functionality still works.
4. Add or update documentation as needed.
5. Submit a pull request against the `main` branch with a clear description.

---

## Reporting Issues

Please open an issue at [https://github.com/Inamo87100/mc-woo-remote-automations/issues](https://github.com/Inamo87100/mc-woo-remote-automations/issues) with:
- WordPress version and PHP version
- Steps to reproduce
- Expected vs. actual behaviour
- Any relevant log output
