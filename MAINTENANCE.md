# Maintenance Guide

Long-term maintenance strategy for MC-Woo Remote Automations and MC Remote API.

---

## Regular Update Schedule

| Type | Frequency | Description |
|------|-----------|-------------|
| Security updates | As needed (ASAP) | Fixes for vulnerabilities |
| WordPress compatibility | Within 2 weeks of WP release | Test and update `Tested up to` |
| WooCommerce compatibility | Within 2 weeks of WC release | Test with new WC versions |
| Bug fixes | Monthly | Batch non-critical fixes |
| Features | Quarterly | Planned feature releases |
| Dependencies | Quarterly | Review any bundled libraries |

---

## WordPress Version Compatibility

1. When a new WordPress version is released (major or minor):
   - Deploy to a test environment with the new WP version.
   - Run the full test suite.
   - Fix any deprecation warnings.
   - Update `Tested up to` in `readme.txt`.
   - Release a patch version if code changes were needed.

2. Drop support for old WordPress versions only when:
   - Usage share falls below 5% (check WordPress.org version stats).
   - Announce the drop at least one release in advance.

---

## PHP Version Compatibility

1. Test against PHP 7.4, 8.0, 8.1, 8.2, 8.3 using the CI matrix.
2. When dropping a PHP version:
   - Check WordPress.org PHP stats to confirm low usage.
   - Update `Requires PHP` in both the plugin header and `readme.txt`.
   - Add an `== Upgrade Notice ==` entry.
   - Announce in the changelog.

---

## Security Maintenance

- Perform a security audit before every `MAJOR` and `MINOR` release.
- Subscribe to [WordPress Security Newsletter](https://wordpress.org/news/) for core vulnerability notices.
- Monitor CVE databases for vulnerabilities in any bundled libraries.
- Keep `nonce` and capability checks reviewed with every new admin page or REST endpoint.

---

## Performance Monitoring

- Review execution logs for patterns of slow automations.
- Monitor for any PHP notices or warnings in WP_DEBUG mode.
- Profile database queries if automation volume grows significantly.

---

## User Support Response Times

| Priority | Description | Response Time |
|----------|-------------|---------------|
| Critical (security) | Security vulnerability | 24–48 hours |
| High (data loss, broken functionality) | Plugin stops working | 3 days |
| Medium (partial functionality issue) | Feature not working | 7 days |
| Low (question, enhancement) | General inquiries | 14 days |

---

## Community Engagement Schedule

| Activity | Frequency |
|----------|-----------|
| Check WordPress.org support forum | Twice per week |
| Check GitHub issues | Weekly |
| Respond to open threads | Weekly |
| Post a community update or tip | Monthly |
| Share a major milestone | As it happens |

---

## Deprecation Policy

1. When deprecating a function, filter, or action hook:
   - Add a call to `_deprecated_function()` or `_deprecated_hook()` with a message.
   - Document in CHANGELOG.md under `### Deprecated`.
   - Provide a migration path in the changelog or documentation.
2. Deprecated items remain for **at least one MINOR version** before removal.
3. Removal is always announced in `== Upgrade Notice ==` in `readme.txt`.

---

## End-of-Life Policy

If a plugin must be abandoned:
1. Notify users via a `readme.txt` update and support forum post at least 6 months in advance.
2. Create a final release that adds a prominent admin notice pointing users to alternatives.
3. Request the plugin be closed on WordPress.org only after the 6-month window.
