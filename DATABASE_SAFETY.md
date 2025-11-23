# Database Safety Features

This document describes the database safety features implemented to prevent accidental data loss.

## 🛡️ Safety Features

### 1. Database Backup Command

A comprehensive database backup command that supports MySQL, PostgreSQL, and SQLite.

#### Usage

```bash
# Basic backup
php artisan db:backup

# Compress backup
php artisan db:backup --compress

# Keep only 7 backups (default)
php artisan db:backup --keep=7

# Custom path
php artisan db:backup --path=/path/to/backup.sql
```

#### Backup Location

- **Default**: `storage/app/backups/`
- **Format**: `{database_name}_{timestamp}.sql` or `.sql.gz` (if compressed)
- **Log**: `storage/app/backups/backup_log.json`

#### Automatic Backups

Backups are automatically scheduled:
- **Daily**: 2:00 AM (keeps last 7 backups)
- **Weekly**: Sundays at 3:00 AM (keeps last 4 weeks)

### 2. Safe Migration Command

A wrapper around `migrate:fresh` that adds safety checks and backup options.

#### Usage

```bash
# Safe migrate:fresh with confirmation prompts
php artisan migrate:safe-fresh

# Create backup before fresh migration
php artisan migrate:safe-fresh --backup

# Include seeding
php artisan migrate:safe-fresh --seed

# Force without confirmation (not recommended)
php artisan migrate:safe-fresh --force
```

**⚠️ WARNING**: The `--force` flag bypasses all safety checks. Use with extreme caution!

### 3. Seeder Safeguards

All seeders now include safety checks:

#### TestPatientSeeder
- Checks if patients already exist
- **Skips seeding** if data found (prevents duplicates)
- Shows warning with patient count

#### TestUsersSeeder
- Uses `firstOrCreate()` to avoid duplicates
- Only creates if email doesn't exist
- Warns if users already exist (except in local/test)

#### DatabaseSeeder
- Shows database statistics before seeding
- Explains what seeders will do
- **Never deletes existing data**

### 4. Database Safety Service

A service class providing safety utilities:

```php
use App\Services\DatabaseSafetyService;

// Check if database has data
$hasData = DatabaseSafetyService::hasData();

// Get database statistics
$stats = DatabaseSafetyService::getDatabaseStats();
// Returns: ['Users' => 10, 'Patients' => 50, ...]

// Create backup before destructive operation
DatabaseSafetyService::createBackupBeforeDestructiveOperation('migration');

// Warn about destructive operation
DatabaseSafetyService::warnDestructiveOperation('migrate:fresh');
```

## 📋 Best Practices

### Before Running Migrations

1. **Always create a backup first**:
   ```bash
   php artisan db:backup
   ```

2. **Use safe commands**:
   ```bash
   # ✅ Good
   php artisan migrate:safe-fresh --backup
   
   # ❌ Bad (no safety checks)
   php artisan migrate:fresh
   ```

3. **Check database status**:
   ```bash
   php artisan tinker
   >>> \App\Models\Patient::count()
   >>> \App\Models\User::count()
   ```

### Before Running Seeders

1. **Check existing data**:
   ```bash
   php artisan db:seed
   # Seeders will automatically check and warn you
   ```

2. **Seed in local environment only**:
   - Test seeders only run in `local` environment
   - Production environment automatically skips test seeders

### Regular Maintenance

1. **Review backups**:
   ```bash
   # List backups
   ls -lh storage/app/backups/
   
   # Check backup log
   cat storage/app/backups/backup_log.json
   ```

2. **Clean old backups** (if needed):
   - Backups are automatically cleaned (keeps last 7/28 based on schedule)
   - Manually delete if storage space is limited

## 🔒 Safety Checklist

Before any destructive operation:

- [ ] ✅ Backup created (`php artisan db:backup`)
- [ ] ✅ Database statistics reviewed
- [ ] ✅ Confirmation prompts answered
- [ ] ✅ Appropriate environment (local/staging, not production)
- [ ] ✅ Team notified (if in shared environment)

## 🚨 Recovery

If data is accidentally deleted:

1. **Stop the operation immediately** (Ctrl+C)

2. **Locate the backup**:
   ```bash
   ls -lt storage/app/backups/ | head -5
   ```

3. **Restore from backup**:
   ```bash
   # For MySQL
   mysql -u username -p database_name < storage/app/backups/database_name_2025-01-15_020000.sql
   
   # For PostgreSQL
   psql -U username -d database_name < storage/app/backups/database_name_2025-01-15_020000.sql
   
   # For SQLite
   cp storage/app/backups/database_name_2025-01-15_020000.sql database.sqlite
   ```

4. **Verify restoration**:
   ```bash
   php artisan tinker
   >>> \App\Models\Patient::count()
   >>> \App\Models\User::count()
   ```

## 📝 Notes

- **Backups are compressed by default** (saves disk space)
- **Backup logs are kept** in `storage/app/backups/backup_log.json`
- **Automatic backups run in background** (won't block other operations)
- **Seeders are safe by design** (never delete, only create)
- **All warnings are logged** to Laravel's log files

## ⚙️ Configuration

To customize backup schedule, edit `app/Console/Kernel.php`:

```php
// Change backup time
$schedule->command('db:backup')->dailyAt('03:00');

// Change backup retention
$schedule->command('db:backup --keep=14')->dailyAt('02:00');
```

---

**Remember**: When in doubt, create a backup first! 🛡️

