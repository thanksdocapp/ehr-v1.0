# Running Database Migrations

## Quick Start

### Option 1: Using Batch File (Windows)
1. Double-click `run-migrations.bat` in the project root
2. If PHP is not found, edit the batch file and set the correct PHP path

### Option 2: Using Command Line
Open a terminal/command prompt in the project directory and run:

```bash
php artisan migrate
```

If you get "php is not recognized", you need to:
1. Add PHP to your system PATH, OR
2. Use the full path to PHP, for example:
   ```bash
   C:\xampp\php\php.exe artisan migrate
   ```

### Option 3: Using Composer (if available)
```bash
composer run-script migrate
```

## What Migrations Will Run

The following migration files will be executed:

1. **2025_01_15_000001_create_patient_email_consent_table.php**
   - Creates `patient_email_consent` table for tracking patient email consent

2. **2025_01_15_000002_add_medical_record_email_fields_to_email_logs_table.php**
   - Adds new fields to `email_logs` table for medical record email tracking

3. **2025_01_15_000003_create_email_bounces_table.php**
   - Creates `email_bounces` table for tracking email bounces and complaints

## Finding PHP on Windows

Common PHP installation locations:
- `C:\php\php.exe`
- `C:\xampp\php\php.exe`
- `C:\wamp\bin\php\php8.1\php.exe`
- `C:\laragon\bin\php\php-8.1\php.exe`
- `C:\Program Files\PHP\php.exe`

## Troubleshooting

### "php is not recognized"
- Add PHP to your system PATH environment variable
- Or use the full path to php.exe in your commands

### "Access denied" or permission errors
- Run your terminal/command prompt as Administrator
- Ensure your database user has CREATE TABLE permissions

### Migration already exists
If you see "Migration table not found", run:
```bash
php artisan migrate:install
php artisan migrate
```

## After Migrations

Once migrations are complete:
1. The email feature will be fully functional
2. You can start tracking patient email consent
3. Email logs will include medical record information
4. Bounce tracking will be available

## Option 4: Manual SQL Execution

If you have direct database access (phpMyAdmin, MySQL Workbench, etc.), you can run the SQL files directly:

1. Navigate to `database/migrations/SQL/` folder
2. Run the SQL files in this order:
   - `manual_migration_2025_01_15_000001.sql` (creates patient_email_consent table)
   - `manual_migration_2025_01_15_000002.sql` (adds fields to email_logs table)
   - `manual_migration_2025_01_15_000003.sql` (creates email_bounces table)

**Note:** The second migration uses MySQL 8.0+ syntax (`ADD COLUMN IF NOT EXISTS`). If you're using an older MySQL version, you may need to modify the SQL or check for column existence manually.

## Verifying Migrations

To check if migrations ran successfully, you can:
1. Check your database for the new tables:
   - `patient_email_consent`
   - `email_bounces`
   - Verify `email_logs` table has the new columns
2. Visit the email compose page - the migration warning should disappear
3. Run `php artisan migrate:status` to see migration status (if PHP is available)

