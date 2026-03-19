# Release Process

How to create, version, and deploy releases for both plugins.

---

## Versioning

This project uses [Semantic Versioning](https://semver.org/):

```
MAJOR.MINOR.PATCH
```

| Increment | When |
|-----------|------|
| `PATCH` | Bug fixes, security patches, translation updates |
| `MINOR` | New features (backward-compatible) |
| `MAJOR` | Breaking changes, major rewrites |

---

## Release Checklist

Before creating a release:

- [ ] All target issues/PRs merged into `main`
- [ ] `CHANGELOG.md` updated with all changes
- [ ] `Stable tag` in `readme.txt` updated
- [ ] Version number updated in main plugin PHP file and constants
- [ ] Tested on the latest stable WordPress + WooCommerce versions
- [ ] PHPCS passes with no errors
- [ ] Security audit completed for the release
- [ ] Documentation updated if needed

---

## How to Create a Release

### Method 1 — Automated (recommended)

Use the `version-bump.sh` script:

```bash
./version-bump.sh mc-woo-remote-automations 1.2.0
```

This will:
1. Update `Version:` in the plugin PHP file header
2. Update the version constant
3. Update `Stable tag` in `readme.txt`
4. Add a CHANGELOG.md entry template
5. Prompt you to commit and tag

Then push:

```bash
git push origin main --tags
```

The `release.yml` GitHub Actions workflow will automatically:
- Create a GitHub Release
- Trigger SVN deployment via `deploy-svn.yml`

### Method 2 — Manual

1. Update the version in the plugin PHP file header and constant.
2. Update `Stable tag` in `readme.txt`.
3. Add a CHANGELOG.md entry.
4. Commit:
   ```bash
   git add -A
   git commit -m "chore: release mc-woo-remote-automations 1.2.0"
   git tag -a v1.2.0 -m "Release 1.2.0"
   git push origin main --tags
   ```

---

## Deploy to WordPress.org

After a GitHub tag is pushed:

- **Automated**: The `deploy-svn.yml` workflow deploys automatically.
- **Manual trigger**: Go to **GitHub → Actions → Deploy to WordPress.org SVN → Run workflow**.

To deploy manually via SVN, see [DEPLOYMENT.md](DEPLOYMENT.md).

---

## Rollback Procedures

### GitHub Rollback

To revert to a previous release, create a new patch release reverting the problematic changes:

```bash
git revert <bad-commit-hash>
git commit -m "fix: revert problematic change"
./version-bump.sh mc-woo-remote-automations 1.1.4
git push origin main --tags
```

### WordPress.org SVN Rollback

Update `Stable tag` in `readme.txt` to the previous stable version and commit to SVN trunk. The WordPress.org directory will serve the previous tag to users.

---

## Emergency Hotfix Process

1. Create a hotfix branch from the current production tag:
   ```bash
   git checkout -b hotfix/1.1.4 v1.1.3
   ```
2. Apply the minimal fix.
3. Bump the patch version.
4. Merge into `main`.
5. Tag and push to trigger deployment.

---

## Version Numbering History

See [CHANGELOG.md](CHANGELOG.md) for the full version history.
