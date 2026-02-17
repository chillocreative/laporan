# Deployment Scripts

This folder contains helper scripts to simplify the deployment process from GitHub to cPanel.

## Scripts Overview

### 1. `setup-ssh.sh`
**Purpose:** Set up SSH key authentication between cPanel and GitHub

**Usage:**
```bash
bash setup-ssh.sh
```

**What it does:**
- ✅ Creates SSH directory if needed
- ✅ Generates ED25519 SSH key pair
- ✅ Sets correct file permissions
- ✅ Configures SSH config for GitHub
- ✅ Adds GitHub to known hosts
- ✅ Displays public key for copying
- ✅ Tests GitHub connection

**When to use:**
- First-time setup
- Setting up SSH on a new server
- Regenerating SSH keys

**Requirements:**
- Access to cPanel Terminal
- Access to GitHub account

---

### 2. `deploy-laravel.sh`
**Purpose:** Initial deployment and setup of Laravel application

**Usage:**
```bash
cd /path/to/your/repository
bash deploy-laravel.sh
```

**What it does:**
- ✅ Verifies Laravel project structure
- ✅ Creates .env file from .env.example
- ✅ Installs Composer dependencies
- ✅ Generates application key
- ✅ Sets correct file permissions
- ✅ Creates storage symlink
- ✅ Runs database migrations (optional)
- ✅ Runs seeders (optional)
- ✅ Optimizes application cache
- ✅ Provides document root information

**When to use:**
- After first clone from GitHub
- Fresh installation
- Resetting application to clean state

**Requirements:**
- Repository already cloned
- Run from Laravel project root
- Composer installed on server

---

### 3. `update-from-github.sh`
**Purpose:** Pull latest changes and update application

**Usage:**
```bash
cd /path/to/your/repository
bash update-from-github.sh
```

**What it does:**
- ✅ Checks Git status
- ✅ Handles uncommitted changes (with stashing)
- ✅ Pulls latest code from GitHub
- ✅ Updates Composer dependencies
- ✅ Runs new migrations (optional)
- ✅ Clears all cache
- ✅ Rebuilds optimized cache
- ✅ Restores stashed changes if any

**When to use:**
- After pushing new code to GitHub
- Regular updates
- Deploying new features
- Applying bug fixes

**Requirements:**
- Repository already set up
- SSH authentication working
- Run from Laravel project root

---

## Installation

These scripts are already included in your repository. To use them on your cPanel server:

### Method 1: They're already there!
If you've cloned the repository, the scripts are in the `scripts/` folder.

### Method 2: Make them executable (if needed)
```bash
cd /path/to/repository/scripts
chmod +x setup-ssh.sh
chmod +x deploy-laravel.sh
chmod +x update-from-github.sh
```

---

## Quick Start Workflow

### First Time Setup

1. **Upload scripts to cPanel** (if not already cloned)
2. **Set up SSH:**
   ```bash
   bash scripts/setup-ssh.sh
   ```
   Follow the prompts and add the key to GitHub

3. **Clone your repository:**
   ```bash
   cd /home/chilloc1/public_html
   git clone git@github.com:chillocreative/laporan.git
   cd laporan
   ```

4. **Deploy Laravel:**
   ```bash
   bash scripts/deploy-laravel.sh
   ```
   Edit .env when prompted, run migrations

5. **Configure domain** to point to `/public` folder

6. **Done!** Your application is live

### Regular Updates

Whenever you push new code to GitHub:

```bash
cd /home/chilloc1/public_html/laporan
bash scripts/update-from-github.sh
```

That's it!

---

## Troubleshooting

### "Permission denied" when running scripts

**Solution:**
```bash
chmod +x scripts/*.sh
```

### "No such file or directory"

**Solution:**
Make sure you're in the correct directory:
```bash
pwd  # Should show your repository path
ls   # Should show artisan file (Laravel project)
```

### Scripts not found

**Solution:**
Ensure scripts are in the `scripts/` folder:
```bash
ls -la scripts/
```

### Composer not found

**Solution:**
- Install Composer in cPanel
- Or run composer commands manually
- Or use PHP with composer.phar:
  ```bash
  php composer.phar install --no-dev
  ```

---

## Script Customization

### Changing Default Paths

Edit the scripts to match your setup:

**In `deploy-laravel.sh`:**
```bash
# Change repository path if needed
REPO_DIR=$(pwd)  # Uses current directory
```

**In `setup-ssh.sh`:**
```bash
# Change key filename if needed
KEY_FILE="$HOME/.ssh/id_ed25519_github"
```

### Changing Git Branch

If you use a branch other than `main`:

**In `update-from-github.sh`:**
```bash
# Change 'main' to your branch name
git pull origin main  # Change to: git pull origin your-branch
```

---

## Advanced Usage

### Running Scripts from Anywhere

Add scripts to your PATH:

```bash
# In your ~/.bashrc or ~/.bash_profile
export PATH="$PATH:/home/chilloc1/public_html/laporan/scripts"
```

Then you can run:
```bash
update-from-github.sh  # From anywhere
```

### Automated Deployments

Set up a cron job to auto-update:

```bash
# Edit crontab
crontab -e

# Add this line to update daily at 2 AM
0 2 * * * cd /home/chilloc1/public_html/laporan && bash scripts/update-from-github.sh >> /tmp/deploy.log 2>&1
```

### Webhook Deployment

For automatic deployment on Git push, you'd need:
1. A webhook endpoint in your Laravel app
2. GitHub webhook configured
3. Script to pull and deploy when webhook is triggered

(This requires additional setup beyond these scripts)

---

## Safety Features

All scripts include:
- ✅ Error checking (`set -e`)
- ✅ Status confirmations
- ✅ Color-coded output
- ✅ Interactive prompts for destructive actions
- ✅ Stashing of uncommitted changes
- ✅ Clear success/failure messages

---

## Script Maintenance

### Keep Scripts Updated

When updating the repository:
```bash
git pull origin main
```

New script versions will be pulled automatically.

### Backup Scripts

Before modifying:
```bash
cp scripts/deploy-laravel.sh scripts/deploy-laravel.sh.backup
```

---

## Getting Help

### View Script Options

Most scripts show help when run without proper context:
```bash
bash scripts/deploy-laravel.sh
# Will show error if not in Laravel directory
```

### Check Script Output

Scripts use color coding:
- 🟢 **Green:** Success
- 🟡 **Yellow:** Warning or info
- 🔴 **Red:** Error
- 🔵 **Blue:** Information

### Documentation

- **Detailed Guide:** `docs/SSH_SETUP_GUIDE.md`
- **Quick Reference:** `docs/QUICK_REFERENCE.md`
- **Checklist:** `docs/DEPLOYMENT_CHECKLIST.md`

---

## Security Notes

⚠️ **Important:**
- Scripts never store or transmit passwords
- SSH private keys stay on your server
- No sensitive data is logged
- Always review scripts before running
- Don't commit modified scripts with credentials

---

## Contributing

If you improve these scripts:
1. Test thoroughly on your server
2. Document changes
3. Update this README
4. Commit and push to your repository

---

## Support

If scripts don't work:
1. Check the troubleshooting section
2. Review `docs/SSH_SETUP_GUIDE.md`
3. Check script permissions (`chmod +x`)
4. Verify you're in the correct directory
5. Check cPanel error logs

---

**Scripts Version:** 1.0
**Last Updated:** 2026-02-16
**Repository:** chillocreative/laporan
