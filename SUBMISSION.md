# WordPress.org Plugin Directory Submission Guide

Step-by-step instructions for submitting **MC-Woo Remote Automations** and **MC Remote API** to the WordPress.org Plugin Directory.

---

## Step 1 — Create a WordPress.org Account

1. Go to [https://login.wordpress.org/register](https://login.wordpress.org/register).
2. Choose a memorable username (this will appear as the plugin author).
3. Verify your email address.

---

## Step 2 — Prepare Your Plugin

Before submitting, complete the [Pre-Submission Checklist](PRE-SUBMISSION-CHECKLIST.md) and ensure:

- `readme.txt` exists and follows the [WordPress.org readme format](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/).
- Plugin header in the main PHP file contains all required fields.
- License is GPL v2 or later.
- No commercial language in the plugin code.

---

## Step 3 — Submit the Plugin

1. Navigate to [https://wordpress.org/plugins/developers/add/](https://wordpress.org/plugins/developers/add/).
2. Log in with your WordPress.org account.
3. Fill in the submission form (see [SUBMISSION-FORM-TEMPLATE.md](SUBMISSION-FORM-TEMPLATE.md) for pre-filled content).
4. Upload a `.zip` file of the plugin directory (without development files):
   ```bash
   zip -r mc-woo-remote-automations.zip mc-woo-remote-automations/ \
     -x "*.git*" -x "*tests/*" -x "*node_modules/*"
   ```
5. Submit the form.

---

## Step 4 — Wait for Review (24–72 hours)

The WordPress.org review team will check your plugin for:

- Security issues
- Code quality (WordPress Coding Standards)
- Trademark violations
- Proper licensing
- No external dependencies / auto-updates outside WordPress.org

You will receive an email with either an approval or revision requests.

---

## Step 5 — Respond to Revision Requests

If the reviewer requests changes:

1. Read the email carefully — it links to specific issues.
2. Fix all mentioned issues.
3. Reply to the same email thread with a summary of changes.
4. Attach a new `.zip` if requested.

---

## Step 6 — First Release After Approval

Once approved:

1. You will receive SVN commit access.
2. Follow [DEPLOYMENT.md](DEPLOYMENT.md) to push your plugin to SVN.
3. Set `Stable tag` in `readme.txt` to your version number.
4. Commit to SVN trunk and create a tag.
5. The plugin will appear on WordPress.org within minutes.

---

## Step 7 — Post-Submission

See [AFTER-SUBMISSION-GUIDE.md](AFTER-SUBMISSION-GUIDE.md) for:
- Monitoring first reviews
- Handling support requests
- Planning future updates
