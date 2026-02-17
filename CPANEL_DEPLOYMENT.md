# cPanel GitHub Deployment Guide

**Complete guide for deploying the Laravel Laporan application from GitHub to cPanel using SSH authentication.**

---

## 📚 Documentation Overview

This repository includes comprehensive deployment documentation:

### 📖 Main Guides

1. **[SSH Setup Guide](docs/SSH_SETUP_GUIDE.md)** - Complete step-by-step guide
   - Generate SSH keys
   - Configure GitHub
   - Test connection
   - Deploy Laravel app
   - Troubleshooting

2. **[Quick Reference](docs/QUICK_REFERENCE.md)** - Command cheat sheet
   - Common commands
   - File paths
   - Git operations
   - Troubleshooting snippets

3. **[Deployment Checklist](docs/DEPLOYMENT_CHECKLIST.md)** - Printable checklist
   - Step-by-step checkboxes
   - Nothing to memorize
   - Track progress
   - Verification steps

### 🔧 Automated Scripts

Located in `scripts/` folder:

1. **`setup-ssh.sh`** - Automate SSH key generation and setup
2. **`deploy-laravel.sh`** - Initial Laravel application deployment
3. **`update-from-github.sh`** - Pull updates and redeploy

See [scripts/README.md](scripts/README.md) for detailed usage.

---

## 🚀 Quick Start

### For First-Time Setup:

```bash
# 1. Run SSH setup script
bash scripts/setup-ssh.sh

# 2. Add the public key to GitHub (script will guide you)
# Visit: https://github.com/settings/ssh/new

# 3. Clone your repository
cd /home/chilloc1/public_html
git clone git@github.com:chillocreative/laporan.git
cd laporan

# 4. Deploy Laravel application
bash scripts/deploy-laravel.sh

# 5. Configure domain to point to /public folder
```

### For Regular Updates:

```bash
cd /home/chilloc1/public_html/laporan
bash scripts/update-from-github.sh
```

---

## 📋 What You'll Need

- ✅ cPanel access with Terminal
- ✅ GitHub account access
- ✅ Repository: https://github.com/chillocreative/laporan
- ✅ 10-15 minutes for initial setup

---

## 🎯 Problem Being Solved

**Issue:** cPanel Git Version Control shows error:
```
fatal: could not read Username for 'https://github.com'
```

**Cause:** cPanel cannot use HTTPS URLs because it can't prompt for credentials in non-interactive mode.

**Solution:** Use SSH key authentication instead of HTTPS, which allows passwordless authentication.

---

## 📁 Project Structure

```
LAPORAN/
├── docs/
│   ├── SSH_SETUP_GUIDE.md          # Complete setup guide
│   ├── QUICK_REFERENCE.md          # Command cheat sheet
│   └── DEPLOYMENT_CHECKLIST.md     # Printable checklist
├── scripts/
│   ├── README.md                   # Script documentation
│   ├── setup-ssh.sh               # SSH automation
│   ├── deploy-laravel.sh          # Laravel deployment
│   └── update-from-github.sh      # Update automation
└── CPANEL_DEPLOYMENT.md           # This file
```

---

## 🔑 SSH vs HTTPS

### HTTPS (Won't Work in cPanel)
```
https://github.com/chillocreative/laporan.git
❌ Requires username/password
❌ Can't prompt in non-interactive mode
❌ Fails in cPanel Git Version Control
```

### SSH (Recommended for cPanel)
```
git@github.com:chillocreative/laporan.git
✅ Uses SSH key authentication
✅ No password prompts needed
✅ Works perfectly in cPanel
✅ More secure
```

---

## 🛠️ Implementation Steps Summary

### Phase 1: SSH Key Setup (5 minutes)
1. Generate SSH key in cPanel
2. Add public key to GitHub
3. Test connection

### Phase 2: Repository Setup (5 minutes)
1. Clone repository using SSH URL
2. Configure Laravel
3. Set permissions

### Phase 3: Domain Configuration (5 minutes)
1. Point domain to `/public` folder
2. Configure SSL (optional but recommended)
3. Test application

**Total Time:** 15-20 minutes

---

## 📖 Choosing the Right Guide

### Use This Guide If You Want To:

| Goal | Use This Document |
|------|------------------|
| Complete walkthrough with explanations | `docs/SSH_SETUP_GUIDE.md` |
| Quick command reference | `docs/QUICK_REFERENCE.md` |
| Step-by-step checklist to follow | `docs/DEPLOYMENT_CHECKLIST.md` |
| Automate SSH setup | `scripts/setup-ssh.sh` |
| Automate Laravel deployment | `scripts/deploy-laravel.sh` |
| Update deployed application | `scripts/update-from-github.sh` |
| Understand all scripts | `scripts/README.md` |

---

## 🎓 Recommended Approach

### For Beginners:
1. Read `docs/SSH_SETUP_GUIDE.md` first (understand what you're doing)
2. Use `docs/DEPLOYMENT_CHECKLIST.md` while performing setup
3. Bookmark `docs/QUICK_REFERENCE.md` for later

### For Experienced Users:
1. Run `scripts/setup-ssh.sh`
2. Run `scripts/deploy-laravel.sh`
3. Done!

### For Future Updates:
1. Just run `scripts/update-from-github.sh`
2. That's it!

---

## ✅ Verification

After deployment, verify:

```bash
# 1. Check Git status
cd /home/chilloc1/public_html/laporan
git status

# 2. Verify Laravel installation
php artisan --version

# 3. Check file permissions
ls -la storage/

# 4. Test application
curl https://yourdomain.com
```

---

## 🔧 Troubleshooting

### Common Issues:

| Problem | Solution |
|---------|----------|
| Permission denied (publickey) | Check SSH key is added to GitHub |
| Repository not found | Verify SSH URL format |
| Host key verification failed | Run `ssh-keyscan github.com >> ~/.ssh/known_hosts` |
| Laravel 500 error | Check `.env` file and permissions |
| Composer not found | Install Composer or use PHP with composer.phar |

Full troubleshooting guide: `docs/SSH_SETUP_GUIDE.md#troubleshooting`

---

## 🔐 Security Best Practices

✅ **Do:**
- Keep private SSH keys secure
- Use `APP_DEBUG=false` in production
- Set strong database passwords
- Enable SSL certificates
- Regularly update dependencies

❌ **Don't:**
- Share private SSH keys
- Commit `.env` files to Git
- Use debug mode in production
- Use weak passwords
- Expose sensitive information

---

## 🔄 Deployment Workflow

### Development Workflow:

```bash
# On local machine
git add .
git commit -m "New feature"
git push origin main

# On cPanel server
cd /home/chilloc1/public_html/laporan
bash scripts/update-from-github.sh
```

### Emergency Rollback:

```bash
# Find previous commit
git log --oneline

# Rollback to specific commit
git reset --hard COMMIT_HASH

# Or rollback one commit
git reset --hard HEAD~1

# Redeploy
bash scripts/deploy-laravel.sh
```

---

## 📞 Support Resources

### Documentation
- **This Repository:** All guides in `docs/` folder
- **Laravel Docs:** https://laravel.com/docs
- **Git Docs:** https://git-scm.com/doc

### GitHub
- **Repository:** https://github.com/chillocreative/laporan
- **Issues:** https://github.com/chillocreative/laporan/issues
- **SSH Keys:** https://github.com/settings/keys

### cPanel
- **Error Logs:** Metrics → Errors in cPanel
- **PHP Logs:** Check error_log files
- **Laravel Logs:** `storage/logs/laravel.log`

---

## 🎯 Next Steps After Deployment

1. **Configure Environment**
   - Review `.env` settings
   - Set up email configuration
   - Configure queue drivers if needed

2. **Set Up Cron Jobs** (if needed)
   ```bash
   * * * * * cd /home/chilloc1/public_html/laporan && php artisan schedule:run
   ```

3. **Enable Queue Workers** (if needed)
   ```bash
   php artisan queue:work --daemon
   ```

4. **Set Up Backups**
   - Database backups
   - File backups
   - Backup `.env` file securely

5. **Monitor Application**
   - Set up uptime monitoring
   - Configure error notifications
   - Review logs regularly

---

## 📊 Maintenance Schedule

### Daily
- Check application availability
- Review error logs

### Weekly
- Update dependencies: `composer update`
- Check for Laravel security updates
- Review disk space usage

### Monthly
- Audit SSH keys on GitHub
- Review and rotate secrets
- Clean up old logs
- Optimize database

---

## 🎉 Success Indicators

Your deployment is successful when:

✅ `ssh -T git@github.com` shows your username
✅ `git pull` works without password prompts
✅ Application loads in browser without errors
✅ Database connections work
✅ File uploads work (if applicable)
✅ All main features function correctly

---

## 📝 Notes

- This guide is specific to the **Laporan** Laravel application
- Tested with cPanel's Git Version Control feature
- Works with GitHub public and private repositories
- SSH keys can be reused for multiple repositories
- Deploy keys are an alternative for single repository access

---

## 🔄 Keeping This Guide Updated

This guide is version controlled in the repository. To get updates:

```bash
cd /home/chilloc1/public_html/laporan
git pull origin main
```

Updated documentation will be in the `docs/` folder.

---

## 📄 License & Usage

These deployment guides and scripts are part of the Laporan project.
Feel free to adapt them for your own Laravel projects.

---

## ✨ Credits

**Repository:** chillocreative/laporan
**Platform:** cPanel + GitHub
**Framework:** Laravel
**Created:** 2026-02-16

---

**Questions or Issues?**

1. Check the guides in `docs/` folder
2. Review `scripts/README.md` for script help
3. Consult `docs/QUICK_REFERENCE.md` for commands
4. Check GitHub repository issues
5. Review cPanel and Laravel logs

---

**Happy Deploying! 🚀**
