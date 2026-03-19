# Documentation Strategy

Plan for creating and maintaining user and developer documentation.

---

## Audience Segments

| Audience | Needs |
|----------|-------|
| **End users** (store owners) | Setup guide, how-to videos, FAQ, troubleshooting |
| **Developers** | API reference, hooks/filters, code examples, contributing guide |
| **WordPress.org reviewers** | Compliant readme.txt, clear description, transparent codebase |

---

## Documentation Structure

```
Repository root
├── README.md                ← Project overview (GitHub landing page)
├── CHANGELOG.md             ← Version history
├── CONTRIBUTING.md          ← Developer contribution guide
└── docs/ (per plugin)
    ├── SETUP.md             ← Installation & configuration
    ├── API.md               ← REST API reference (mc-remote-api)
    ├── EXAMPLES.md          ← Integration examples and use cases
    ├── TROUBLESHOOTING.md   ← Common issues and solutions
    ├── SECURITY.md          ← Security best practices
    └── TESTING.md           ← Testing guide
```

---

## User Documentation

### readme.txt (WordPress.org)

The `readme.txt` is the plugin's public-facing documentation on WordPress.org. It must always contain:

- Clear `== Description ==` covering all major use cases
- Complete `== Installation ==` instructions
- `== Frequently Asked Questions ==` covering the top 5–10 support questions
- `== Screenshots ==` with captions for every screenshot
- `== Changelog ==` updated on every release

### Setup Guide (`docs/SETUP.md`)

Step-by-step installation and configuration guide covering:
- Prerequisites
- Installation steps
- Initial configuration
- First automation setup
- Verification checklist

### Troubleshooting Guide (`docs/TROUBLESHOOTING.md`)

Covers the most common issues encountered by users with clear resolution steps.

---

## Developer Documentation

### API Reference (`mc-remote-api/docs/API.md`)

Full REST API documentation including:
- Endpoint URLs
- Authentication
- Request/response formats
- Error codes
- cURL examples

### Hooks & Filters

Document all WordPress actions and filters exposed by the plugins:
- Filter name, parameters, return value, example usage.
- Add `@since` tags to identify when hooks were introduced.

### Code Examples (`docs/EXAMPLES.md`)

Practical integration examples for common use cases.

---

## Video Tutorials (Optional)

Priority topics for video tutorials:
1. Installation and initial setup (5 min)
2. Configuring your first automation (10 min)
3. Troubleshooting common issues (8 min)

Host on YouTube and embed in the plugin website.

---

## Blog Posts (mambacoding.com)

| Topic | Priority | Frequency |
|-------|----------|-----------|
| Launch announcement | High | At release |
| Use case: sell LMS access via WooCommerce | High | Month 1 |
| Use case: multi-site membership | Medium | Month 2 |
| Developer guide: using the REST API | Medium | Month 2 |
| How we built it (technical) | Low | Month 3 |

---

## Knowledge Base Articles

Topics to cover in a knowledge base (FAQ format):
- What is MC-Woo Remote Automations?
- Do I need both plugins?
- Is my data secure?
- What happens if an automation fails?
- How do I test before going live?
- Can I use this with non-WooCommerce sites?

---

## FAQ Maintenance

- Review FAQ entries quarterly.
- Add new FAQ entries whenever the same support question appears 3+ times.
- Archive obsolete entries when features change.

---

## Documentation Review Cycle

| Document | Review Frequency |
|----------|-----------------|
| `readme.txt` | Every release |
| `docs/SETUP.md` | Every MINOR release |
| `docs/TROUBLESHOOTING.md` | Quarterly |
| `docs/API.md` | Every API change |
| Blog posts | Annual review for accuracy |
