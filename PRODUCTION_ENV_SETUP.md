# Production .env Configuration for notes.thanksdoc.co.uk

## ⚠️ Important: Update These Settings for Production

Your current `.env` file is configured for **local development**. You **MUST** update these settings before deploying to `notes.thanksdoc.co.uk`.

---

## Required Changes for Production

### 1. **Application URL** ⚠️ CRITICAL
```env
# Current (Local):
APP_URL=http://localhost:8000

# Production (Update to):
APP_URL=https://notes.thanksdoc.co.uk
```

**Why:** This is used for generating URLs in emails, notifications, and API responses.

---

### 2. **Application Environment** ⚠️ CRITICAL
```env
# Current (Local):
APP_ENV=local

# Production (Update to):
APP_ENV=production
```

**Why:** 
- Enables production optimizations
- Disables debug mode
- Affects error handling
- CORS configuration depends on this

---

### 3. **Debug Mode** ⚠️ CRITICAL
```env
# Current (Local):
APP_DEBUG=true

# Production (Update to):
APP_DEBUG=false
```

**Why:** Prevents sensitive error information from being exposed to users.

---

### 4. **CORS Configuration** ✅ Already Configured
```env
# Production:
CORS_ALLOWED_ORIGINS=https://thanksdoc.co.uk,https://www.thanksdoc.co.uk,https://notes.thanksdoc.co.uk
```

**Status:** Already set up correctly in your local `.env`.

---

### 5. **Database Configuration** ⚠️ CRITICAL
```env
# Update with your production database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_database_name
DB_USERNAME=your_production_db_user
DB_PASSWORD=your_production_db_password
```

**Why:** Production database will be different from local.

---

### 6. **Application Key** ⚠️ CRITICAL
```env
# Make sure this is set (should already be set):
APP_KEY=base64:your-application-key-here
```

**Action:** If not set, run `php artisan key:generate` on production server.

---

### 7. **Mail Configuration** ⚠️ IMPORTANT
```env
# Update with your production mail settings:
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@thanksdoc.co.uk
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@thanksdoc.co.uk
MAIL_FROM_NAME="${APP_NAME}"
```

**Why:** Emails need to be sent from your production domain.

---

### 8. **Session & Cache** ⚠️ IMPORTANT
```env
# For production, use database or redis:
SESSION_DRIVER=database
CACHE_DRIVER=file
# Or for better performance:
# CACHE_DRIVER=redis
# SESSION_DRIVER=redis
```

---

### 9. **Queue Configuration** (If using queues)
```env
QUEUE_CONNECTION=database
# Or for better performance:
# QUEUE_CONNECTION=redis
```

---

## Complete Production .env Template

Here's a complete template for your production `.env` file:

```env
# ============================================
# PRODUCTION CONFIGURATION
# Domain: notes.thanksdoc.co.uk
# ============================================

APP_NAME="ThankDoc EHR"
APP_ENV=production
APP_KEY=base64:YOUR-APP-KEY-HERE
APP_DEBUG=false
APP_URL=https://notes.thanksdoc.co.uk

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_database
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Broadcasting (if using)
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@thanksdoc.co.uk
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@thanksdoc.co.uk
MAIL_FROM_NAME="${APP_NAME}"

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://thanksdoc.co.uk,https://www.thanksdoc.co.uk,https://notes.thanksdoc.co.uk

# AWS / S3 (if using cloud storage)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Redis (if using)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Other services
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
```

---

## Deployment Checklist

Before going live, ensure:

- [ ] `APP_URL` is set to `https://notes.thanksdoc.co.uk`
- [ ] `APP_ENV` is set to `production`
- [ ] `APP_DEBUG` is set to `false`
- [ ] `APP_KEY` is generated (run `php artisan key:generate`)
- [ ] Database credentials are correct
- [ ] `CORS_ALLOWED_ORIGINS` includes your domains
- [ ] Mail configuration is set up
- [ ] File permissions are correct (755 for folders, 644 for files)
- [ ] Storage link is created (`php artisan storage:link`)
- [ ] Config cache is cleared (`php artisan config:clear`)
- [ ] Route cache is created (`php artisan route:cache`)
- [ ] View cache is created (`php artisan view:cache`)

---

## After Deployment

1. **Clear and cache config:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

2. **Test the application:**
   - Visit `https://notes.thanksdoc.co.uk`
   - Test login functionality
   - Test API endpoints
   - Check email sending

3. **Monitor logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## Security Reminders

- ✅ **Never commit `.env` to Git** (already in `.gitignore`)
- ✅ **Use strong database passwords**
- ✅ **Enable HTTPS/SSL** on your server
- ✅ **Set proper file permissions** (`.env` should be 600 or 640)
- ✅ **Keep `APP_DEBUG=false` in production**
- ✅ **Use environment-specific database credentials**

---

## Quick Setup Script

After uploading your files to production, run:

```bash
# 1. Copy .env.example to .env (if not already done)
cp .env.example .env

# 2. Edit .env with production values (see template above)

# 3. Generate application key
php artisan key:generate

# 4. Run migrations
php artisan migrate --force

# 5. Create storage link
php artisan storage:link

# 6. Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set permissions (Linux/Unix)
chmod -R 755 storage bootstrap/cache
chmod 600 .env
```

---

**Your `.env` file will work on the live domain once you update these settings!** ✅

