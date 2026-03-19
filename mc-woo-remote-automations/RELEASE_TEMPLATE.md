# Release Template — MC-Woo Remote Automations

Copy and fill in this template when preparing a new release.

---

## v[X.Y.Z] — [YYYY-MM-DD]

### Summary

[1–3 sentence summary of what this release includes.]

### What's New

- [New feature 1]
- [New feature 2]

### Changed

- [Changed behavior 1]

### Bug Fixes

- [Bug fix 1] (Issue #[number])
- [Bug fix 2] (Issue #[number])

### Security

- [Security fix] (CVE-XXXX-XXXX if applicable)

### Upgrade Notice

[Is this a breaking change? Are there migration steps?]
[If no breaking changes, write: "No breaking changes. Update from your WordPress dashboard."]

### Tested With

- WordPress [X.X.X]
- WooCommerce [X.X.X]
- PHP [X.X]

---

## Pre-Release Checklist

- [ ] Version bumped in `mc-woo-remote-automations.php`
- [ ] Version constant updated (`MC_WOO_REMOTE_VERSION`)
- [ ] `Stable tag` updated in `readme.txt`
- [ ] `== Changelog ==` section updated in `readme.txt`
- [ ] `== Upgrade Notice ==` section updated in `readme.txt` (if breaking)
- [ ] `CHANGELOG.md` updated
- [ ] PHPCS passes
- [ ] Tested on latest WordPress + WooCommerce
- [ ] Git tag created: `git tag -a vX.Y.Z -m "Release X.Y.Z"`
- [ ] Tag pushed: `git push origin main --tags`
- [ ] SVN deployment confirmed
