# GitHub SSH Authentication Setup for cPanel

## Quick Start Guide

This guide will help you set up SSH key authentication between your cPanel server and GitHub, solving the "fatal: could not read Username" error.

**Time Required:** 10-15 minutes
**Difficulty:** Beginner-Friendly

---

## Prerequisites

- ✅ Access to your cPanel account
- ✅ Access to your GitHub account (chillocreative)
- ✅ Repository: https://github.com/chillocreative/laporan

---

## Step 1: Generate SSH Key in cPanel

### Option A: Using cPanel Terminal (Recommended)

1. **Login to cPanel**
   - Go to your cPanel URL
   - Login with your credentials

2. **Open Terminal**
   - Search for "Terminal" in cPanel search bar
   - Click to open the Terminal

3. **Generate SSH Key**

   Copy and paste this command:
   ```bash
   ssh-keygen -t ed25519 -C "cpanel-laporan-deploy" -f ~/.ssh/id_ed25519_github
   ```

   When prompted:
   - **Enter file location:** Press `Enter` (uses default)
   - **Enter passphrase:** Press `Enter` (no passphrase)
   - **Enter passphrase again:** Press `Enter`

   You should see output like:
   ```
   Your identification has been saved in /home/chilloc1/.ssh/id_ed25519_github
   Your public key has been saved in /home/chilloc1/.ssh/id_ed25519_github.pub
   ```

4. **Display Your Public Key**
   ```bash
   cat ~/.ssh/id_ed25519_github.pub
   ```

   **Copy the entire output** - it will look like:
   ```
   ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIJqfg... cpanel-laporan-deploy
   ```

### Option B: Using SSH Access Manager

1. Login to cPanel
2. Go to **Security** → **SSH Access**
3. Click **Manage SSH Keys**
4. Click **Generate a New Key**
5. Fill in:
   - **Key Name:** `github_laporan`
   - **Key Password:** Leave empty
   - **Key Type:** DSA (or RSA if available)
6. Click **Generate Key**
7. Click **Go Back**
8. Find your key, click **Manage** → **Authorize**
9. Click **View/Download** to see the public key

---

## Step 2: Add SSH Key to GitHub

1. **Copy your public key** from Step 1

2. **Open GitHub**
   - Go to https://github.com
   - Login to your account

3. **Navigate to SSH Settings**
   - Click your profile picture (top-right corner)
   - Click **Settings**
   - In the left sidebar, click **SSH and GPG keys**

4. **Add New SSH Key**
   - Click the green **"New SSH key"** button
   - Fill in the form:
     - **Title:** `cPanel Laporan Server`
     - **Key type:** `Authentication Key`
     - **Key:** Paste your public key from Step 1
   - Click **"Add SSH key"**
   - Enter your GitHub password if prompted

✅ **Success!** Your SSH key is now added to GitHub.

---

## Step 3: Test SSH Connection

Back in your cPanel Terminal:

```bash
ssh -T git@github.com
```

**First time?** You'll see:
```
The authenticity of host 'github.com (140.82.121.4)' can't be established.
Are you sure you want to continue connecting (yes/no)?
```
Type `yes` and press Enter.

**Expected Success Message:**
```
Hi chillocreative! You've successfully authenticated, but GitHub does not provide shell access.
```

✅ If you see your GitHub username (chillocreative), SSH is working!

❌ If you see "Permission denied", double-check:
- Public key is correctly added to GitHub
- No typos in the key

---

## Step 4: Configure Git Repository in cPanel

### Method 1: Using cPanel Git Version Control (Easiest)

1. **Open Git Version Control**
   - In cPanel, search for "Git Version Control"
   - Click to open

2. **Create New Repository**
   - Click **"Create"** button

3. **Fill in Repository Details**
   - **Clone URL:** `git@github.com:chillocreative/laporan.git`
   - **Repository Path:** `/home/chilloc1/public_html/laporan`
     - Or wherever you want to deploy (e.g., `/home/chilloc1/laporan.cc`)
   - **Repository Name:** `laporan` (optional)

4. **Click "Create"**

   cPanel will now clone your repository using SSH authentication.

### Method 2: Using Terminal (Alternative)

If Git Version Control doesn't work, use Terminal:

```bash
# Navigate to where you want to deploy
cd /home/chilloc1/public_html

# Clone the repository
git clone git@github.com:chillocreative/laporan.git

# Enter the directory
cd laporan
```

---

## Step 5: Configure SSH for Git (If Key Has Custom Name)

If you used a custom name for your SSH key (like `id_ed25519_github`), configure SSH:

```bash
# Create or edit SSH config
nano ~/.ssh/config
```

Add these lines:
```
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_github
    IdentitiesOnly yes
```

Save: `Ctrl + O`, `Enter`, then `Ctrl + X`

Set correct permissions:
```bash
chmod 600 ~/.ssh/config
chmod 600 ~/.ssh/id_ed25519_github
chmod 644 ~/.ssh/id_ed25519_github.pub
```

---

## Step 6: Deploy Laravel Application

Once the repository is cloned, set up your Laravel app:

```bash
# Navigate to your repository
cd /home/chilloc1/public_html/laporan

# Copy environment file
cp .env.example .env

# Edit .env file (use nano or File Manager in cPanel)
nano .env

# Install dependencies (if Composer is available)
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Run migrations (if database is configured)
php artisan migrate --seed

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

---

## Step 7: Configure Web Access

### Option A: Subdomain
1. In cPanel, go to **Domains** → **Subdomains**
2. Create subdomain: `laporan.yourdomain.com`
3. Point Document Root to: `/home/chilloc1/public_html/laporan/public`

### Option B: Main Domain
1. In cPanel, go to **Domains** → **Domains**
2. Edit your domain's Document Root
3. Change to: `/home/chilloc1/public_html/laporan/public`

### Option C: Using .htaccess Redirect
If deploying to subfolder, create `.htaccess` in public_html:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ /laporan/public/$1 [L]
</IfModule>
```

---

## Step 8: Update & Pull Changes (Future Deployments)

### Using cPanel Git Version Control
1. Go to **Git Version Control**
2. Find your repository
3. Click **"Pull or Deploy"**
4. Click **"Update from Remote"**

### Using Terminal
```bash
cd /home/chilloc1/public_html/laporan
git pull origin main
```

After pulling changes:
```bash
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Troubleshooting

### Problem: Permission Denied (publickey)

**Solution:**
```bash
# Check if key exists
ls -la ~/.ssh/

# Check SSH agent
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_ed25519_github

# Test again
ssh -T git@github.com
```

### Problem: Could not resolve hostname

**Solution:**
```bash
# Add GitHub to known hosts
ssh-keyscan github.com >> ~/.ssh/known_hosts

# Test connection
ssh -T git@github.com
```

### Problem: Repository not found

**Verify:**
- You have access to https://github.com/chillocreative/laporan
- Repository name is spelled correctly
- SSH URL format: `git@github.com:chillocreative/laporan.git`

### Problem: HTTPS URL still being used

**Solution:**
```bash
cd /home/chilloc1/public_html/laporan
git remote -v
# If showing https, update to SSH:
git remote set-url origin git@github.com:chillocreative/laporan.git
```

---

## Security Best Practices

✅ **DO:**
- Keep your private key secret (never share `id_ed25519` file)
- Use different keys for different servers
- Regularly audit GitHub SSH keys (remove unused ones)
- Use deploy keys for production servers

❌ **DON'T:**
- Share your private key
- Use the same key everywhere
- Commit keys to Git repositories
- Give keys passphrases on automated servers (unless you have SSH agent)

---

## Quick Reference

### SSH URLs Format
```
HTTPS: https://github.com/chillocreative/laporan.git
SSH:   git@github.com:chillocreative/laporan.git
```

### Common Commands
```bash
# View public key
cat ~/.ssh/id_ed25519_github.pub

# Test GitHub connection
ssh -T git@github.com

# Check Git remote URL
git remote -v

# Change to SSH URL
git remote set-url origin git@github.com:chillocreative/laporan.git

# Pull latest changes
git pull origin main
```

### File Locations
```
Private Key: ~/.ssh/id_ed25519_github
Public Key:  ~/.ssh/id_ed25519_github.pub
SSH Config:  ~/.ssh/config
Known Hosts: ~/.ssh/known_hosts
```

---

## Next Steps After Setup

1. ✅ Configure `.env` file with production settings
2. ✅ Set up database and run migrations
3. ✅ Configure domain/subdomain to point to `/public`
4. ✅ Set correct file permissions
5. ✅ Test the application in browser
6. ✅ Set up automated deployments (optional)

---

## Support

If you encounter issues:
1. Check the Troubleshooting section above
2. Verify each step was completed correctly
3. Check cPanel error logs: **Metrics** → **Errors**
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Document Version:** 1.0
**Last Updated:** 2026-02-16
**Repository:** https://github.com/chillocreative/laporan
