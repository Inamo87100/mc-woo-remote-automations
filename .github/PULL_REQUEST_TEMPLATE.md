## Description

<!-- Describe the changes introduced by this PR -->

## Type of Change

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Security fix

## Related Issue

<!-- Link to the issue this PR resolves, e.g. Closes #123 -->

## Testing Checklist

- [ ] Tested on WordPress 5.0+
- [ ] Tested on PHP 7.4+
- [ ] Tested with WooCommerce 3.0+
- [ ] No PHP errors or warnings introduced
- [ ] All existing tests pass

## Security Review

- [ ] No hardcoded credentials or API keys
- [ ] All user inputs are sanitized (`sanitize_text_field`, `intval`, etc.)
- [ ] All outputs are escaped (`esc_html`, `esc_attr`, `wp_kses`, etc.)
- [ ] Nonces used for form submissions
- [ ] Capability checks in place for admin actions
- [ ] No SQL injection vulnerabilities (use `$wpdb->prepare()`)

## WordPress Coding Standards

- [ ] Code follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [ ] PHPCS passes with no errors (`phpcs`)
- [ ] All translatable strings use `__()`, `_e()`, or similar gettext functions with correct text domain

## Documentation

- [ ] README updated (if applicable)
- [ ] Changelog entry added to `CHANGELOG.md`
- [ ] Inline docblocks updated for any changed functions/classes

## Screenshots

<!-- Add screenshots for UI changes -->
