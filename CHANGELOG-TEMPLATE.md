# Changelog Template

Use this template when adding entries to `CHANGELOG.md`.

---

## Template

```markdown
## [X.Y.Z] - YYYY-MM-DD

### Added
- New feature description (PR #123 — @author)

### Changed
- Changed behavior description (PR #124 — @author)

### Fixed
- Bug fix description (Issue #125 — @author)

### Security
- Security fix description (CVE-XXXX-XXXX — @author)

### Deprecated
- Deprecated function/hook (will be removed in X.Y+2.0)

### Removed
- Removed feature description (was deprecated since X.Y.0)
```

---

## Category Definitions

| Category | When to use |
|----------|-------------|
| **Added** | New features, new hooks/filters, new settings |
| **Changed** | Existing behavior has changed (non-breaking) |
| **Fixed** | Bug fixes |
| **Security** | Security vulnerability fixes — always include CVE if available |
| **Deprecated** | Features/APIs that will be removed in a future version |
| **Removed** | Features/APIs removed in this release (must have been deprecated first) |

---

## Rules

1. Every release must have a CHANGELOG.md entry.
2. Entries are listed in reverse chronological order (newest first).
3. Each line item links to the relevant PR or issue number.
4. Security fixes always include the reporter's credit.
5. Breaking changes are marked with ⚠️ and explained in the `== Upgrade Notice ==` section of `readme.txt`.

---

## Example Entry

```markdown
## [1.2.0] - 2024-03-15

### Added
- Remote connection health check endpoint (PR #45 — @dev1)
- Configurable retry delay for failed automations (PR #47 — @dev2)
- Filter `mc_woo_automation_user_data` to modify user data before creation (PR #48)

### Changed
- Execution logs now show remote site name instead of URL (PR #50 — @dev1)
- Minimum WooCommerce version raised from 3.0 to 5.0

### Fixed
- Fixed fatal error when remote site returns invalid JSON (Issue #43 — @dev2)
- Fixed role assignment not applying on sites with custom roles (Issue #44)

### Security
- Sanitized remote site URL input to prevent SSRF (reported by @security-researcher)
```
