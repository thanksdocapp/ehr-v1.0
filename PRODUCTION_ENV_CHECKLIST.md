# Production .env Checklist for notes.thanksdoc.co.uk

## Quick Checklist

Copy this checklist and check off each item when updating your production `.env` file:

### ⚠️ Critical Settings (Must Update)

- [ ] `APP_URL=https://notes.thanksdoc.co.uk`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` is set (run `php artisan key:generate` if not)

### 🔐 Database Settings

- [ ] `DB_DATABASE` = your production database name
- [ ] `DB_USERNAME` = your production database user
- [ ] `DB_PASSWORD` = your production database password
- [ ] `DB_HOST` = your database host (usually `127.0.0.1` or `localhost`)

### 🌐 CORS Settings

- [ ] `CORS_ALLOWED_ORIGINS=https://thanksdoc.co.uk,https://www.thanksdoc.co.uk,https://notes.thanksdoc.co.uk`

### 📧 Mail Settings

- [ ] `MAIL_HOST` = your SMTP server
- [ ] `MAIL_USERNAME` = your email address
- [ ] `MAIL_PASSWORD` = your email password
- [ ] `MAIL_FROM_ADDRESS` = noreply@thanksdoc.co.uk (or your preferred email)

### ✅ After Deployment

- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan storage:link`
- [ ] Test login functionality
- [ ] Test email sending
- [ ] Verify API endpoints work

---

## Current Local Settings (for reference)

Your current local `.env` has:
- `APP_URL=http://localhost:8000` → **Change to:** `https://notes.thanksdoc.co.uk`
- `APP_ENV=local` → **Change to:** `production`
- `APP_DEBUG=true` → **Change to:** `false`
- `CORS_ALLOWED_ORIGINS` → **Already correct!**

---

**Answer:** Your current `.env` file will **NOT** work on the live domain without these updates. Update the settings above before deploying! ⚠️

