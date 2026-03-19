# Continuous Deployment

Documentation for the CI/CD pipeline used to test, lint, and deploy both plugins.

---

## Pipeline Overview

```
Push / PR
   │
   ├── phpcs.yml        → PHP CodeSniffer (WordPress Coding Standards)
   │
Tag v*.*.*
   │
   ├── release.yml      → Create GitHub Release + trigger SVN deployment
   │
   └── deploy-svn.yml   → Sync trunk to WordPress.org SVN + create SVN tag

Scheduled (weekly)
   └── wordpress-org-sync.yml  → Check version sync between GitHub and WordPress.org
```

---

## Workflows

| Workflow file | Trigger | Purpose |
|---------------|---------|---------|
| `.github/workflows/phpcs.yml` | Push/PR to `main` | Lint PHP with WordPress Coding Standards |
| `.github/workflows/release.yml` | Push of `v*.*.*` tag | Create GitHub Release, trigger deploy |
| `.github/workflows/deploy-svn.yml` | `repository_dispatch` or manual | Deploy to WordPress.org SVN |
| `.github/workflows/wordpress-org-sync.yml` | Weekly schedule or manual | Monitor sync status |

---

## Required GitHub Secrets

Set these in **GitHub → Repository → Settings → Secrets and variables → Actions**:

| Secret | Description |
|--------|-------------|
| `WP_ORG_SVN_USERNAME` | WordPress.org account username |
| `WP_ORG_SVN_PASSWORD` | WordPress.org account password or [Application Password](https://make.wordpress.org/meta/2021/10/27/wordpress-org-application-passwords/) |

---

## Automated Testing

The `phpcs.yml` workflow runs automatically on every push and pull request to `main`. It:

1. Installs PHP 8.1.
2. Installs `squizlabs/php_codesniffer` and `wp-coding-standards/wpcs`.
3. Runs `phpcs` on both plugin directories.

To run locally:

```bash
composer global require squizlabs/php_codesniffer
composer global require wp-coding-standards/wpcs
phpcs --config-set installed_paths $(composer global config home)/vendor/wp-coding-standards/wpcs

# Lint mc-woo-remote-automations
phpcs --standard=mc-woo-remote-automations/.phpcs.xml.dist \
      mc-woo-remote-automations/mc-woo-remote-automations.php \
      mc-woo-remote-automations/includes/ \
      mc-woo-remote-automations/admin/

# Lint mc-remote-api
phpcs --standard=mc-remote-api/.phpcs.xml.dist \
      mc-remote-api/mc-remote-api.php \
      mc-remote-api/includes/
```

---

## Automated Deployment

### Triggering a Release

1. Ensure `CHANGELOG.md` and `readme.txt` are updated for the new version.
2. Run `version-bump.sh` (or manually update versions) and push the tag:
   ```bash
   git tag -a v1.2.0 -m "Release 1.2.0"
   git push origin main --tags
   ```
3. `release.yml` automatically creates the GitHub Release.
4. `deploy-svn.yml` is triggered via `repository_dispatch` and deploys to WordPress.org SVN.

### Manual Deployment

Use the **workflow_dispatch** trigger on `deploy-svn.yml`:

1. Go to **GitHub → Actions → Deploy to WordPress.org SVN**.
2. Click **Run workflow**.
3. Enter the version and select the plugin.
4. Click **Run workflow**.

---

## Rollback Automation

To roll back to a previous version:

1. Create a new patch release (e.g. `1.2.1`) reverting the bad changes.
2. Push the new tag — the pipeline deploys automatically.
3. Alternatively, use the manual workflow dispatch to deploy the previous version's tag.

---

## Status Monitoring

The `wordpress-org-sync.yml` workflow runs every Monday and compares local versions with WordPress.org. Check the Actions tab for the weekly sync report.
