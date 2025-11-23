# Hardcoded Branding Text Report

## 📋 Summary

Found **multiple instances** of hardcoded branding text (`ThanksDoc`, `ThanksDoc EHR`, `NewWaves`) throughout the codebase that should be configurable via settings or environment variables.

---

## 🔍 Hardcoded Branding Instances

### 1. **Application/Company Names**

#### Views (resources/views/)

| File | Line | Hardcoded Text | Should Use |
|------|------|---------------|------------|
| `layouts/staff.blade.php` | 7 | `ThanksDoc EPR` | `{{ config('app.name') }}` or settings |
| `admin/dashboard.blade.php` | 14 | `ThanksDoc Dashboard` | Settings variable |
| `components/application-logo.blade.php` | 5 | `'ThanksDoc EHR'` | `app('settings')->get('site_name')` |
| `partials/navbar.blade.php` | 15, 17, 21 | `'ThanksDoc EHR'` | `$site_settings['hospital_name']` (already has fallback) |
| `partials/footer.blade.php` | 9, 11, 13 | `'ThanksDoc EHR'` | `$site_settings['hospital_name']` (already has fallback) |
| `homepage.blade.php` | 9, 10 | `'ThanksDoc EHR'` | `$site_settings['hospital_name']` (already has fallback) |

#### Admin Views Footer Text

Multiple admin views have hardcoded footer text:
- `admin/settings/backup.blade.php` (line 475, 478)
- `admin/settings/index.blade.php` (line 207, 210)
- `admin/dashboard.blade.php` (line 384, 387)
- `admin/departments/index.blade.php` (line 283)
- `admin/doctors/index.blade.php` (line 307)
- `admin/patients/index.blade.php` (line 280)
- `admin/appointments/index.blade.php` (line 284)

**Hardcoded Text:**
```html
<span>Powered by <strong>ThanksDoc EHR v1.0</strong> - Advanced Administration Dashboard</span>
© {{ date('Y') }} ThanksDoc EHR. All rights reserved.
```

**Should Use:**
```html
<span>Powered by <strong>{{ config('app.name', 'Hospital System') }} v{{ config('app.version', '1.0') }}</strong> - Advanced Administration Dashboard</span>
© {{ date('Y') }} {{ $site_settings['hospital_name'] ?? config('app.name') }}. All rights reserved.
```

#### Email Template Defaults

| File | Line | Hardcoded Text |
|------|------|---------------|
| `admin/email-management/logs.blade.php` | 353, 500 | `'Welcome to ThanksDoc EHR'`, `'ThanksDoc EHR'` |
| `admin/communication/email-templates/edit.blade.php` | 110 | `placeholder="ThanksDoc EHR"` |
| `admin/communication/email-templates/show.blade.php` | 53 | `'ThanksDoc EHR'` |

### 2. **Controllers (app/Http/Controllers/)**

| File | Line | Hardcoded Text | Context |
|------|------|---------------|---------|
| `Admin/SettingsController.php` | 1166, 1168 | `'ThanksDoc EHR v1.0'`, `'ThanksDoc EHR'` | System info, email templates |
| `Admin/SettingsController.php` | 1250, 1252 | `'ThanksDoc EHR v1.0'`, `'ThanksDoc EHR'` | Email templates |
| `Admin/SettingsController.php` | 1804 | `'ThanksDoc EHR'` | SMS test message |
| `Admin/SettingsController.php` | 1915 | `"-- ThanksDoc EHR Database Backup\n"` | Backup file header |
| `InstallController.php` | 25, 36 | `'Welcome to ThanksDoc EHR'`, `'ThanksDoc EHR'` | Install wizard |
| `InstallController.php` | 38 | `'NewWaves Projects'` | Author name |
| `InstallController.php` | 50, 74 | `'ThanksDoc EHR'` | Error messages |
| `InstallController.php` | 474 | `'ThanksDoc EHR'` | Config default |
| `Admin/AboutUsController.php` | 110 | `'About ThanksDoc EHR'` | Alt text |

### 3. **Services (app/Services/)**

| File | Line | Hardcoded Text | Context |
|------|------|---------------|---------|
| `ElectronicDispenserService.php` | 259 | `'ThanksDoc-EHR/1.0'` | User-Agent header |

### 4. **Config Files (config/)**

| File | Line | Hardcoded Text | Notes |
|------|------|---------------|-------|
| `hospital.php` | 14 | `'ThanksDoc EHR'` | ✅ Already uses `env('HOSPITAL_NAME')` - Good! |

### 5. **Views with Fallback Values**

Some views already have fallbacks but use `'ThanksDoc EHR'` as default:

| File | Pattern |
|------|---------|
| `staff/medical-records/create.blade.php` | `config('hospital.name', 'ThanksDoc EHR')` |
| `admin/medical-records/create.blade.php` | `config('hospital.name', 'ThanksDoc EHR')` |

---

## ✅ Already Configurable (Good Examples)

1. **`config/hospital.php`** - Uses `env('HOSPITAL_NAME', 'ThanksDoc EHR')` ✅
2. **Many views** - Already use `$site_settings['hospital_name'] ?? 'ThanksDoc EHR'` ✅
3. **`config/app.php`** - Uses `env('APP_NAME', 'Laravel')` ✅

---

## 🔧 Recommendations

### Priority 1: Critical Hardcoded Text (High Impact)

1. **Admin Footer Text** - Appears on many admin pages
   - Location: Multiple admin blade files
   - Fix: Create a config variable or use site settings

2. **Installation Wizard** - First impression
   - Location: `app/Http/Controllers/InstallController.php`
   - Fix: Use config/env variables

3. **Staff Portal Title** - User-facing
   - Location: `resources/views/layouts/staff.blade.php`
   - Fix: Use `config('app.name')` or settings

4. **Email Templates Default** - Professional communication
   - Location: Email template views and controllers
   - Fix: Use site settings or config

### Priority 2: System Messages (Medium Impact)

1. **Backup File Header** - `app/Http/Controllers/Admin/SettingsController.php`
2. **SMS Test Messages** - `app/Http/Controllers/Admin/SettingsController.php`
3. **User-Agent Headers** - `app/Services/ElectronicDispenserService.php`

### Priority 3: Fallback Values (Low Impact)

- Views that already have fallbacks but use `'ThanksDoc EHR'` as default
- These work but should use `config('app.name')` or `config('hospital.name')` instead

---

## 📝 Suggested Configuration

### Add to `config/app.php`:

```php
'version' => env('APP_VERSION', '1.0'),
'author' => env('APP_AUTHOR', 'Hospital System'),
'powered_by' => env('APP_POWERED_BY', true), // Show/hide "Powered by" footer
```

### Add to `.env`:

```env
APP_NAME="Your Hospital Name"
APP_VERSION=1.0
APP_AUTHOR="Your Company Name"
HOSPITAL_NAME="Your Hospital Name"
APP_POWERED_BY=true
```

### Update `config/hospital.php`:

```php
'name' => env('HOSPITAL_NAME', config('app.name', 'Hospital System')),
'version' => env('APP_VERSION', '1.0'),
```

---

## 🎯 Action Items

1. [ ] Replace hardcoded `'ThanksDoc EHR'` in admin footers with config variables
2. [ ] Update `InstallController.php` to use config/env variables
3. [ ] Fix staff portal title in `layouts/staff.blade.php`
4. [ ] Update email template defaults to use site settings
5. [ ] Replace hardcoded branding in `SettingsController.php`
6. [ ] Update `ElectronicDispenserService.php` User-Agent header
7. [ ] Replace fallback values in views with `config('app.name')` or `config('hospital.name')`
8. [ ] Add version and author config to `config/app.php`
9. [ ] Update documentation to show how to customize branding

---

## 📊 Statistics

- **Total Files with Hardcoded Branding**: ~40+ files
- **Most Common**: `ThanksDoc EHR` (~60+ instances)
- **Second Most Common**: `ThanksDoc` (~12,000+ in logs, mostly compiled views)
- **Company Name**: `NewWaves Projects` (1 instance in InstallController)

---

## 🔍 How to Find More

```bash
# Search for ThanksDoc (case-insensitive)
grep -ri "thanksdoc" resources/views/ --exclude-dir=storage

# Search for hardcoded company names
grep -ri "NewWaves\|ThanksDoc" app/ --exclude-dir=vendor

# Search for "Powered by" text
grep -ri "Powered by" resources/views/
```

---

## 📌 Notes

- Most compiled views in `storage/framework/views/` will be regenerated automatically
- Some instances may be intentional (like in install wizard before settings are configured)
- Consider keeping some fallbacks but using generic terms like "Hospital System" instead of brand-specific names

