# Deployment Guide — WordPress.org SVN

This guide covers how to set up the WordPress.org SVN repositories for both plugins and perform initial and ongoing deployments.

---

## Prerequisites

- A WordPress.org account with plugin commit access
- `subversion` installed (`sudo apt-get install subversion` or `brew install subversion`)
- Credentials stored in `WP_ORG_SVN_USERNAME` and `WP_ORG_SVN_PASSWORD` environment variables

---

## SVN Directory Structure

```
https://plugins.svn.wordpress.org/<plugin-slug>/
├── trunk/       ← active development (mirrors GitHub main branch)
├── branches/    ← long-lived maintenance branches (e.g. 1.x-maintenance)
├── tags/        ← immutable version snapshots (e.g. 1.1.3)
└── assets/      ← marketplace images (icon, banner, screenshots)
```

---

## Initial SVN Setup

### 1. Check out the SVN repository

```bash
# mc-woo-remote-automations
svn checkout https://plugins.svn.wordpress.org/mc-woo-remote-automations ~/svn/mc-woo-remote-automations

# mc-remote-api
svn checkout https://plugins.svn.wordpress.org/mc-remote-api ~/svn/mc-remote-api
```

### 2. Populate trunk

```bash
cd ~/svn/mc-woo-remote-automations

rsync -av --delete \
  --exclude='.git' --exclude='.github' --exclude='tests' \
  --exclude='node_modules' --exclude='.gitignore' --exclude='.svn-deploy' \
  /path/to/repo/mc-woo-remote-automations/ trunk/

svn add --force trunk/
svn status
svn commit -m "Initial trunk commit" --username "$WP_ORG_SVN_USERNAME" --password "$WP_ORG_SVN_PASSWORD"
```

### 3. Upload marketplace assets

```bash
mkdir -p assets
cp /path/to/repo/mc-woo-remote-automations/assets/icon.svg    assets/icon-128x128.svg
cp /path/to/repo/mc-woo-remote-automations/assets/banner.svg  assets/banner-772x250.svg
# Copy screenshots — must be PNG
cp /path/to/repo/mc-woo-remote-automations/assets/screenshots/*.png assets/

svn add --force assets/
svn commit -m "Add marketplace assets" --username "$WP_ORG_SVN_USERNAME" --password "$WP_ORG_SVN_PASSWORD"
```

---

## Creating a Release Tag

Every time you release a new version you must create a matching SVN tag. The automated workflow (`deploy-svn.yml`) handles this, but you can do it manually:

```bash
VERSION="1.2.0"
svn copy \
  https://plugins.svn.wordpress.org/mc-woo-remote-automations/trunk \
  https://plugins.svn.wordpress.org/mc-woo-remote-automations/tags/${VERSION} \
  -m "Tagging version ${VERSION}" \
  --username "$WP_ORG_SVN_USERNAME" --password "$WP_ORG_SVN_PASSWORD"
```

Then update `Stable tag` in `readme.txt` and commit to trunk.

---

## Branch Management

Long-lived maintenance branches follow the pattern `branches/<major>.<minor>-maintenance`:

```bash
svn copy \
  https://plugins.svn.wordpress.org/mc-woo-remote-automations/trunk \
  https://plugins.svn.wordpress.org/mc-woo-remote-automations/branches/1.1-maintenance \
  -m "Create 1.1 maintenance branch" \
  --username "$WP_ORG_SVN_USERNAME" --password "$WP_ORG_SVN_PASSWORD"
```

---

## Automated Deployment via GitHub Actions

The workflow `.github/workflows/deploy-svn.yml` automatically deploys on every push to a `v*.*.*` tag.

**Required GitHub Secrets:**

| Secret | Description |
|--------|-------------|
| `WP_ORG_SVN_USERNAME` | WordPress.org username |
| `WP_ORG_SVN_PASSWORD` | WordPress.org password or application password |

Set these in: **GitHub → Repository → Settings → Secrets and variables → Actions**

---

## .gitignore Patterns for SVN Checkouts

Add to your local `.gitignore` to avoid committing SVN metadata:

```
.svn/
svn/
~/svn/
```

---

## Rollback a Deployment

To revert trunk to a previous tag:

```bash
VERSION="1.1.3"
svn merge \
  https://plugins.svn.wordpress.org/mc-woo-remote-automations/trunk \
  https://plugins.svn.wordpress.org/mc-woo-remote-automations/tags/${VERSION} \
  trunk/
svn commit trunk/ -m "Rollback to ${VERSION}" \
  --username "$WP_ORG_SVN_USERNAME" --password "$WP_ORG_SVN_PASSWORD"
```

Then update the `Stable tag` in `readme.txt` to the rollback version.
