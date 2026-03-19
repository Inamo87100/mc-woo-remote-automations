# After-Submission Guide

What to do after your plugin is approved and live on WordPress.org.

---

## Step 1 — Deploy Your First Release

Follow [DEPLOYMENT.md](DEPLOYMENT.md) to push your plugin to SVN and make it available for download.

---

## Step 2 — Monitor Initial Feedback (First 2 Weeks)

- Watch the WordPress.org support forum daily.
- Monitor GitHub issues for bug reports.
- Check the WordPress.org plugin stats page for download numbers.
- Watch for any low ratings and respond immediately with assistance.

---

## Step 3 — Responding to Reviews and Ratings

**For positive reviews:**
- Thank the reviewer in a brief, genuine reply.
- Do not ask for additional promotion or referrals.

**For negative reviews:**
- Respond professionally within 48 hours.
- Acknowledge the issue and offer a solution.
- If the issue is fixed in a new version, mention the version number.
- Never argue or be defensive.

Example response:

```
Thank you for your feedback! We've identified the issue you described and fixed it in version [X.Y.Z].
Please update the plugin and let us know if the problem is resolved. We're happy to help in the
support forum if you need further assistance.
— Mamba Coding
```

---

## Step 4 — Managing Support Requests

- Check the support forum at least every 7 days.
- Categorize issues: bug / configuration / feature request / won't fix.
- Use [SUPPORT-RESPONSE-TEMPLATES.md](SUPPORT-RESPONSE-TEMPLATES.md) for common scenarios.
- Close resolved threads with a brief summary.

---

## Step 5 — Tracking Downloads and Analytics

WordPress.org provides plugin statistics at:
```
https://wordpress.org/plugins/mc-woo-remote-automations/advanced/
```

Key metrics to track:
- **Active installs**: updated weekly; the most-watched metric.
- **Downloads**: total since launch.
- **WordPress version distribution**: ensure your plugin works on the most common versions.
- **PHP version distribution**: identify which PHP versions your users run.

---

## Step 6 — Community Engagement

- Respond to blog posts or tutorials about your plugin.
- Engage in the WooCommerce and WordPress communities.
- Share major milestones on social media (see [SOCIAL-MEDIA-TEMPLATES.md](SOCIAL-MEDIA-TEMPLATES.md)).
- Acknowledge contributors in CHANGELOG.md and release announcements.

---

## Step 7 — Update Frequency Recommendations

| Update Type | Recommended Timeline |
|-------------|---------------------|
| Security fix | Within 24–48 hours of discovery |
| Critical bug | Within 1 week |
| WordPress compatibility | Within 2 weeks of WP release |
| Regular bug fixes | Monthly (batch minor fixes) |
| New features | Quarterly or as ready |

---

## Step 8 — Long-Term Maintenance Strategy

- Keep `Tested up to` updated with every WordPress release.
- Review PHP version compatibility annually.
- Audit dependencies and third-party libraries quarterly.
- Deprecate old features gracefully (at least one version notice).
- Archive the plugin (close it on WordPress.org) only as a last resort — notify users first.

---

## Key WordPress.org Links

| Resource | URL |
|----------|-----|
| Plugin page | https://wordpress.org/plugins/mc-woo-remote-automations/ |
| Support forum | https://wordpress.org/support/plugin/mc-woo-remote-automations/ |
| Reviews | https://wordpress.org/support/plugin/mc-woo-remote-automations/reviews/ |
| Stats | https://wordpress.org/plugins/mc-woo-remote-automations/advanced/ |
| SVN | https://plugins.svn.wordpress.org/mc-woo-remote-automations/ |
