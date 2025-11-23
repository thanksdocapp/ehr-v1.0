# Deployment Checklist - Safe GitHub Push & Deploy

## ✅ Pre-Deployment Checks

### 1. Files Changed (Safe to Push)
- ✅ `routes/web.php` - Fixed duplicate route (safe)
- ✅ `resources/views/partials/admin-menu-item.blade.php` - Fixed dropdowns (safe)
- ✅ `resources/views/admin/layouts/app.blade.php` - Fixed dropdown (safe)
- ✅ `app/Console/Commands/ImportDoctorUsers.php` - Migration command (safe)

### 2. Files NOT to Push (Already in .gitignore)
- ❌ `.env` - Environment variables (NEVER push)
- ❌ `vendor/` - Composer dependencies (already ignored)
- ❌ `node_modules/` - NPM dependencies (already ignored)
- ❌ `storage/logs/*` - Log files (already ignored)
- ❌ `storage/framework/cache/*` - Cache files (already ignored)
- ❌ `storage/framework/sessions/*` - Session files (already ignored)
- ❌ `storage/framework/views/*` - Compiled views (already ignored)

## 🚀 Safe Deployment Steps

### Step 1: Verify Changes Locally
```bash
# Check what will be committed
git status

# Review the changes
git diff
```

### Step 2: Commit Changes
```bash
git add routes/web.php
git add resources/views/partials/admin-menu-item.blade.php
git add resources/views/admin/layouts/app.blade.php
git add app/Console/Commands/ImportDoctorUsers.php

git commit -m "Fix: Admin sidebar dropdowns and duplicate route

- Added data-bs-toggle='dropdown' to all admin menu dropdowns
- Fixed duplicate custom-menu-items route
- Fixed System Settings sidebar link"
```

### Step 3: Push to GitHub
```bash
git push origin main
# or
git push origin master
```

### Step 4: Deploy to Production

**On your production server:**

```bash
cd /var/www/vhosts/thanksdoc.co.uk/notes.thanksdoc.co.uk

# 1. Pull latest changes
git pull origin main
# or
git pull origin master

# 2. Install/update dependencies (if composer.json changed)
/opt/plesk/php/8.3/bin/php /opt/psa/var/modules/composer/composer.phar install --no-dev --optimize-autoloader

# 3. Clear all caches
/opt/plesk/php/8.3/bin/php artisan config:clear
/opt/plesk/php/8.3/bin/php artisan route:clear
/opt/plesk/php/8.3/bin/php artisan view:clear
/opt/plesk/php/8.3/bin/php artisan cache:clear

# 4. Rebuild optimized caches
/opt/plesk/php/8.3/bin/php artisan config:cache
/opt/plesk/php/8.3/bin/php artisan route:cache
/opt/plesk/php/8.3/bin/php artisan view:cache

# 5. Verify routes (should work now)
/opt/plesk/php/8.3/bin/php artisan route:list | grep system-info
```

## ⚠️ Important Notes

### What WON'T Break:
- ✅ View changes (dropdown fixes) - Safe, just clears view cache
- ✅ Route fixes - Safe, just rebuilds route cache
- ✅ No database migrations - No schema changes
- ✅ No .env changes - Environment stays the same

### What to Watch:
- ⚠️ Route cache must be rebuilt (duplicate route fix)
- ⚠️ View cache must be cleared (dropdown fixes)
- ⚠️ Make sure `.env` file is NOT in the commit

## 🔒 Security Check

Before pushing, verify `.env` is not tracked:
```bash
git check-ignore .env
# Should output: .env

# If it doesn't, make sure .env is in .gitignore
```

## ✅ Post-Deployment Verification

After deployment, test:
1. ✅ Admin sidebar dropdowns work (Patient Management, Medical Records, etc.)
2. ✅ System Settings link works
3. ✅ All routes are accessible
4. ✅ No 500 errors in logs

## 🆘 Rollback Plan (If Needed)

If something breaks:
```bash
# On production server
cd /var/www/vhosts/thanksdoc.co.uk/notes.thanksdoc.co.uk

# Revert to previous commit
git log --oneline -5  # Find previous commit hash
git reset --hard <previous-commit-hash>

# Clear caches
/opt/plesk/php/8.3/bin/php artisan optimize:clear
```

---

**These changes are SAFE to deploy!** They only fix UI issues and route definitions, no breaking changes.

