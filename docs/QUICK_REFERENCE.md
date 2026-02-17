# Quick Reference - cPanel Git Deployment

## 🔑 SSH Key Management

### View Your Public Key
```bash
cat ~/.ssh/id_ed25519_github.pub
```

### Test GitHub Connection
```bash
ssh -T git@github.com
```
Expected: `Hi chillocreative! You've successfully authenticated...`

### Check SSH Keys on GitHub
https://github.com/settings/keys

---

## 📦 Repository URLs

### Your Repository
- **HTTPS:** `https://github.com/chillocreative/laporan.git` ❌ (Won't work in cPanel)
- **SSH:** `git@github.com:chillocreative/laporan.git` ✅ (Use this)

### Check Current Remote URL
```bash
cd /path/to/laporan
git remote -v
```

### Change HTTPS to SSH
```bash
git remote set-url origin git@github.com:chillocreative/laporan.git
```

---

## 🚀 Quick Deployment Commands

### Initial Clone (in cPanel Terminal)
```bash
cd /home/chilloc1/public_html
git clone git@github.com:chillocreative/laporan.git
cd laporan
```

### First-Time Setup
```bash
cp .env.example .env
nano .env  # Edit database credentials
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 755 storage bootstrap/cache
```

### Pull Latest Changes
```bash
cd /home/chilloc1/public_html/laporan
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 Automated Scripts

We've created helper scripts in the `scripts/` folder:

### 1. Setup SSH Authentication
```bash
bash scripts/setup-ssh.sh
```
This will:
- Generate SSH key
- Configure SSH for GitHub
- Guide you through adding key to GitHub
- Test the connection

### 2. Deploy Laravel Application
```bash
cd /home/chilloc1/public_html/laporan
bash scripts/deploy-laravel.sh
```
This will:
- Copy .env file
- Install dependencies
- Generate app key
- Set permissions
- Run migrations (with confirmation)
- Optimize application

### 3. Update from GitHub
```bash
cd /home/chilloc1/public_html/laporan
bash scripts/update-from-github.sh
```
This will:
- Pull latest code
- Update dependencies
- Run migrations (with confirmation)
- Clear and rebuild cache

---

## 📁 Important File Paths

### cPanel Paths
```
Home Directory:     /home/chilloc1/
Public HTML:        /home/chilloc1/public_html/
SSH Keys:           /home/chilloc1/.ssh/
Repository:         /home/chilloc1/public_html/laporan/
Document Root:      /home/chilloc1/public_html/laporan/public
```

### SSH Files
```
Private Key:        ~/.ssh/id_ed25519_github
Public Key:         ~/.ssh/id_ed25519_github.pub
SSH Config:         ~/.ssh/config
Known Hosts:        ~/.ssh/known_hosts
```

### Laravel Files
```
Environment:        .env
Logs:              storage/logs/laravel.log
Cache:             bootstrap/cache/
Public Assets:     public/
```

---

## 🛠️ Common Git Commands

### Check Status
```bash
git status
```

### View Recent Commits
```bash
git log --oneline -10
```

### View Differences
```bash
git diff
```

### Reset to Latest (CAREFUL - Loses local changes)
```bash
git fetch origin
git reset --hard origin/main
```

### Create New Branch
```bash
git checkout -b feature-name
```

### Switch Branch
```bash
git checkout main
```

---

## 🐛 Troubleshooting

### Problem: Permission Denied (publickey)
```bash
# Check if key exists
ls -la ~/.ssh/

# Test connection with verbose output
ssh -vT git@github.com

# Check SSH config
cat ~/.ssh/config

# Verify key permissions
chmod 600 ~/.ssh/id_ed25519_github
chmod 644 ~/.ssh/id_ed25519_github.pub
```

### Problem: Repository not found
```bash
# Check remote URL
git remote -v

# Should show: git@github.com:chillocreative/laporan.git
# If not, update it:
git remote set-url origin git@github.com:chillocreative/laporan.git
```

### Problem: Host key verification failed
```bash
# Add GitHub to known hosts
ssh-keyscan github.com >> ~/.ssh/known_hosts

# Or remove old entry
ssh-keygen -R github.com
ssh -T git@github.com  # Answer 'yes' when prompted
```

### Problem: Laravel 500 Error
```bash
# Check permissions
chmod -R 755 storage bootstrap/cache

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Check .env file
cat .env | grep APP_KEY
# Should have a value, if not:
php artisan key:generate

# Check logs
tail -50 storage/logs/laravel.log
```

### Problem: Storage link broken
```bash
# Remove old link and recreate
rm public/storage
php artisan storage:link
```

---

## 📋 Pre-Deployment Checklist

Before deploying to production:

- [ ] `.env` file configured with production settings
- [ ] `APP_ENV=production` in `.env`
- [ ] `APP_DEBUG=false` in `.env`
- [ ] Database credentials correct
- [ ] `APP_KEY` generated
- [ ] Storage permissions set (755)
- [ ] Storage link created
- [ ] Migrations run
- [ ] Cache optimized
- [ ] Document root points to `/public`
- [ ] Domain/subdomain configured
- [ ] SSL certificate installed (recommended)

---

## 🔐 Security Reminders

✅ **DO:**
- Keep private SSH key secret
- Use `APP_DEBUG=false` in production
- Use strong database passwords
- Regularly update dependencies
- Monitor `storage/logs/laravel.log`

❌ **DON'T:**
- Commit `.env` file to Git
- Share private SSH keys
- Use default passwords
- Enable debug mode in production
- Expose storage directory

---

## 📞 Quick Links

- **GitHub Repository:** https://github.com/chillocreative/laporan
- **GitHub SSH Keys:** https://github.com/settings/keys
- **Add Deploy Key:** https://github.com/chillocreative/laporan/settings/keys
- **Laravel Docs:** https://laravel.com/docs

---

## 🎯 Common Workflows

### Workflow 1: Make changes locally, deploy to cPanel
```bash
# On local machine
git add .
git commit -m "Description of changes"
git push origin main

# On cPanel
cd /home/chilloc1/public_html/laporan
bash scripts/update-from-github.sh
```

### Workflow 2: Hotfix directly on cPanel
```bash
# Make changes via File Manager or nano
cd /home/chilloc1/public_html/laporan

# Check what changed
git status
git diff

# Commit changes
git add .
git commit -m "Hotfix: description"

# Push to GitHub (if you want to save changes)
git push origin main

# Or discard changes
git checkout .
```

### Workflow 3: Reset to clean state
```bash
cd /home/chilloc1/public_html/laporan
git fetch origin
git reset --hard origin/main
bash scripts/deploy-laravel.sh
```

---

**Last Updated:** 2026-02-16
**Repository:** chillocreative/laporan
