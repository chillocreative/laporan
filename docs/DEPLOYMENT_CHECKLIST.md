# cPanel Git Deployment Checklist

**Repository:** chillocreative/laporan
**Date:** _______________
**Deployed By:** _______________

---

## Phase 1: SSH Key Setup

### In cPanel Terminal

- [ ] **1.1** Login to cPanel
- [ ] **1.2** Open Terminal (or SSH Access)
- [ ] **1.3** Generate SSH key:
  ```bash
  ssh-keygen -t ed25519 -C "cpanel-laporan-deploy" -f ~/.ssh/id_ed25519_github
  ```
- [ ] **1.4** Press Enter for all prompts (no passphrase)
- [ ] **1.5** View public key:
  ```bash
  cat ~/.ssh/id_ed25519_github.pub
  ```
- [ ] **1.6** Copy the entire public key (starts with `ssh-ed25519`)

### On GitHub

- [ ] **1.7** Go to https://github.com/settings/ssh/new
- [ ] **1.8** Title: `cPanel Laporan Server`
- [ ] **1.9** Paste the public key
- [ ] **1.10** Click "Add SSH key"
- [ ] **1.11** Confirm with password if prompted

### Back in cPanel Terminal

- [ ] **1.12** Test connection:
  ```bash
  ssh -T git@github.com
  ```
- [ ] **1.13** Type `yes` if asked about authenticity
- [ ] **1.14** Verify success message shows `chillocreative`

**✅ Phase 1 Complete** - SSH authentication is working!

---

## Phase 2: Clone Repository

### Choose Your Method

#### Method A: Using cPanel Git Version Control (Recommended)

- [ ] **2.1** In cPanel, search for "Git Version Control"
- [ ] **2.2** Click "Create"
- [ ] **2.3** Fill in details:
  - Clone URL: `git@github.com:chillocreative/laporan.git`
  - Repository Path: `/home/chilloc1/public_html/laporan`
  - Repository Name: `laporan`
- [ ] **2.4** Click "Create" button
- [ ] **2.5** Wait for cloning to complete
- [ ] **2.6** Verify "Successfully cloned" message

#### Method B: Using Terminal (Alternative)

- [ ] **2.1** Open cPanel Terminal
- [ ] **2.2** Navigate to deployment location:
  ```bash
  cd /home/chilloc1/public_html
  ```
- [ ] **2.3** Clone repository:
  ```bash
  git clone git@github.com:chillocreative/laporan.git
  ```
- [ ] **2.4** Enter directory:
  ```bash
  cd laporan
  ```

**✅ Phase 2 Complete** - Repository cloned successfully!

---

## Phase 3: Laravel Application Setup

### In cPanel Terminal

- [ ] **3.1** Navigate to repository:
  ```bash
  cd /home/chilloc1/public_html/laporan
  ```

### Environment Configuration

- [ ] **3.2** Copy environment file:
  ```bash
  cp .env.example .env
  ```
- [ ] **3.3** Edit .env file:
  ```bash
  nano .env
  ```
- [ ] **3.4** Update these settings:
  ```
  APP_NAME=Laporan
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://yourdomain.com

  DB_CONNECTION=mysql
  DB_HOST=localhost
  DB_PORT=3306
  DB_DATABASE=your_database_name
  DB_USERNAME=your_database_user
  DB_PASSWORD=your_database_password
  ```
- [ ] **3.5** Save file (Ctrl+O, Enter, Ctrl+X)

### Install Dependencies

- [ ] **3.6** Install Composer packages:
  ```bash
  composer install --optimize-autoloader --no-dev
  ```
- [ ] **3.7** Wait for installation to complete

### Application Setup

- [ ] **3.8** Generate application key:
  ```bash
  php artisan key:generate
  ```
- [ ] **3.9** Create storage symlink:
  ```bash
  php artisan storage:link
  ```
- [ ] **3.10** Set permissions:
  ```bash
  chmod -R 755 storage bootstrap/cache
  ```

### Database Setup

- [ ] **3.11** Run migrations:
  ```bash
  php artisan migrate --force
  ```
- [ ] **3.12** Review migration output for errors
- [ ] **3.13** (Optional) Run seeders:
  ```bash
  php artisan db:seed --force
  ```

### Cache Optimization

- [ ] **3.14** Optimize application:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

**✅ Phase 3 Complete** - Laravel application configured!

---

## Phase 4: Domain Configuration

### Choose Your Setup

#### Option A: Subdomain (Recommended)

- [ ] **4.1** In cPanel, go to "Subdomains"
- [ ] **4.2** Create subdomain:
  - Subdomain: `laporan`
  - Domain: `yourdomain.com`
  - Document Root: `/home/chilloc1/public_html/laporan/public`
- [ ] **4.3** Click "Create"
- [ ] **4.4** Wait for DNS propagation (5-30 minutes)

#### Option B: Main Domain

- [ ] **4.1** In cPanel, go to "Domains"
- [ ] **4.2** Click "Manage" next to your domain
- [ ] **4.3** Change Document Root to:
  ```
  /home/chilloc1/public_html/laporan/public
  ```
- [ ] **4.4** Click "Submit"

#### Option C: Addon Domain

- [ ] **4.1** In cPanel, go to "Addon Domains"
- [ ] **4.2** Add new domain
- [ ] **4.3** Set Document Root to:
  ```
  /home/chilloc1/public_html/laporan/public
  ```

**✅ Phase 4 Complete** - Domain configured!

---

## Phase 5: SSL Certificate (Recommended)

- [ ] **5.1** In cPanel, go to "SSL/TLS Status"
- [ ] **5.2** Select your domain
- [ ] **5.3** Click "Run AutoSSL"
- [ ] **5.4** Wait for certificate installation
- [ ] **5.5** Update .env `APP_URL` to `https://`

**✅ Phase 5 Complete** - SSL configured!

---

## Phase 6: Testing & Verification

### Application Testing

- [ ] **6.1** Open your domain in browser
- [ ] **6.2** Verify homepage loads
- [ ] **6.3** Check for any errors
- [ ] **6.4** Test main features/routes
- [ ] **6.5** Verify database connections work

### File Verification

- [ ] **6.6** In File Manager, navigate to repository
- [ ] **6.7** Verify all files are present:
  - [ ] `app/` directory
  - [ ] `resources/` directory
  - [ ] `public/` directory
  - [ ] `.env` file
  - [ ] `artisan` file

### Log Checking

- [ ] **6.8** Check for errors:
  ```bash
  tail -50 storage/logs/laravel.log
  ```
- [ ] **6.9** Review any error messages
- [ ] **6.10** Fix any issues found

### Permissions Check

- [ ] **6.11** Verify storage is writable:
  ```bash
  ls -la storage/
  ```
- [ ] **6.12** Verify cache is writable:
  ```bash
  ls -la bootstrap/cache/
  ```

**✅ Phase 6 Complete** - Application tested!

---

## Phase 7: Future Updates Setup

### Test Update Process

- [ ] **7.1** Using cPanel Git Version Control:
  - Go to "Git Version Control"
  - Click "Pull or Deploy"
  - Click "Update from Remote"

  OR using Terminal:
  ```bash
  cd /home/chilloc1/public_html/laporan
  git pull origin main
  ```
- [ ] **7.2** Verify update works
- [ ] **7.3** Run post-update commands:
  ```bash
  composer install --no-dev
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

### Bookmark Helper Scripts

- [ ] **7.4** Note location of helper scripts:
  - Setup SSH: `scripts/setup-ssh.sh`
  - Deploy Laravel: `scripts/deploy-laravel.sh`
  - Update: `scripts/update-from-github.sh`

**✅ Phase 7 Complete** - Update process verified!

---

## Final Checklist

### Security

- [ ] `.env` file has production settings
- [ ] `APP_DEBUG=false`
- [ ] Strong database password
- [ ] `.env` file NOT committed to Git
- [ ] Storage permissions correct (755)
- [ ] SSL certificate installed

### Performance

- [ ] Cache optimized (`config:cache`, `route:cache`, `view:cache`)
- [ ] Composer optimized (`--optimize-autoloader`)
- [ ] No development dependencies installed

### Functionality

- [ ] Application loads without errors
- [ ] Database connections work
- [ ] File uploads work (if applicable)
- [ ] Email sending works (if applicable)
- [ ] All main features tested

### Documentation

- [ ] Document Root noted: ________________________
- [ ] Database name noted: ________________________
- [ ] Domain URL noted: ________________________
- [ ] Deployment date noted: ________________________

---

## 🎉 Deployment Complete!

Your Laravel application is now live at:

**URL:** ___________________________________

**Next Steps:**
1. Monitor `storage/logs/laravel.log` for any errors
2. Set up regular backups (database + files)
3. Configure cron jobs if needed
4. Set up monitoring/uptime checks
5. Document any custom configurations

---

## Troubleshooting Reference

If you encounter issues, check:

1. **SSH not working:** See `docs/SSH_SETUP_GUIDE.md`
2. **Quick commands:** See `docs/QUICK_REFERENCE.md`
3. **Laravel logs:** `storage/logs/laravel.log`
4. **cPanel Error Logs:** Metrics → Errors in cPanel
5. **PHP Error Logs:** Check cPanel error logs

---

**Checklist Version:** 1.0
**Last Updated:** 2026-02-16
**Completed By:** _______________
**Completion Date:** _______________

**Notes:**
_______________________________________________
_______________________________________________
_______________________________________________
_______________________________________________
