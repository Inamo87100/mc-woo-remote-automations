# Contributing to MC Plugins

Thank you for considering contributing to the MC Plugins suite!

---

## Getting Started

1. **Fork** the repository on GitHub.
2. **Clone** your fork locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/mc-woo-remote-automations.git
   ```
3. **Install PHP tools:**
   ```bash
   composer global require squizlabs/php_codesniffer
   composer global require wp-coding-standards/wpcs
   phpcs --config-set installed_paths $(composer global config home)/vendor/wp-coding-standards/wpcs
   ```

---

## Code Standards

Both plugins follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/):
- Tabs for indentation (not spaces)
- Yoda conditions: `'value' === $variable`
- PHPDoc blocks on all public methods
- All output escaped: `esc_html()`, `esc_attr()`, `wp_kses_post()`
- All database inputs sanitised or prepared with `$wpdb->prepare()`
- No debug code in pull requests (`var_dump`, `error_log`, etc.)

Run the linter before submitting:
```bash
phpcs --standard=.phpcs.xml.dist mc-remote-api/
phpcs --standard=.phpcs.xml.dist mc-woo-remote-automations/
```

---

## Testing

See [TESTING.md](TESTING.md) for the manual testing checklist and automated test setup.

---

## Pull Request Process

1. Create a branch: `feature/your-feature` or `fix/your-fix`.
2. Make your changes following the standards above.
3. Update documentation as needed.
4. Ensure all existing functionality works.
5. Submit a PR against the `main` branch with a clear description.

---

## Reporting Issues

Open an issue at [https://github.com/Inamo87100/mc-woo-remote-automations/issues](https://github.com/Inamo87100/mc-woo-remote-automations/issues) with:
- WordPress, WooCommerce, and PHP versions
- Steps to reproduce
- Expected vs. actual behaviour
- Log output (anonymised)

---

## Security

For security vulnerabilities, please contact **security@mambacoding.com** privately. Do not open a public issue.
