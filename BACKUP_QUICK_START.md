# Database Backup Quick Start Guide

## 🚀 Quick Commands

### Create a Backup
```bash
# Basic backup
php artisan db:backup

# Compressed backup (recommended)
php artisan db:backup --compress

# Backup with custom retention (keep last 10)
php artisan db:backup --compress --keep=10
```

### Safe Migration
```bash
# Safe migrate:fresh with confirmation
php artisan migrate:safe-fresh

# With automatic backup
php artisan migrate:safe-fresh --backup

# With seeding
php artisan migrate:safe-fresh --backup --seed
```

## 📁 Backup Location

- **Directory**: `storage/app/backups/`
- **Format**: `{database_name}_{date}_{time}.sql` or `.sql.gz`
- **Log**: `storage/app/backups/backup_log.json`

## ⏰ Automatic Backups

- **Daily**: 2:00 AM (keeps last 7 backups)
- **Weekly**: Sundays at 3:00 AM (keeps last 4 weeks)

## 🛡️ Safety Features

1. ✅ **Seeder Safeguards** - Seeders check for existing data and warn you
2. ✅ **Safe Migration Command** - `migrate:safe-fresh` requires confirmation
3. ✅ **Automatic Backups** - Scheduled backups prevent data loss
4. ✅ **Backup Logs** - Track all backup operations

## 📖 Full Documentation

See [DATABASE_SAFETY.md](DATABASE_SAFETY.md) for complete documentation.

