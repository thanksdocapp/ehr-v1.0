# Database Backup Cron Jobs

## ✅ Currently Configured Backup Schedules

Your application has **2 automated backup schedules** configured in `app/Console/Kernel.php`:

### 1. Daily Backup
- **Schedule**: Every day at 2:00 AM
- **Command**: `php artisan db:backup --compress --keep=7`
- **Compression**: ✅ Enabled
- **Retention**: Keeps last 7 backups
- **Log File**: `storage/logs/backup.log`

### 2. Weekly Backup
- **Schedule**: Every Sunday at 3:00 AM
- **Command**: `php artisan db:backup --compress --keep=28`
- **Compression**: ✅ Enabled
- **Retention**: Keeps last 28 backups (4 weeks)
- **Log File**: `storage/logs/backup.log`

## 📋 Current Schedule List

Run `php artisan schedule:list` to see all scheduled tasks:

```
Daily Backup:  0  2  * * *  php artisan db:backup --compress --keep=7
Weekly Backup: 0  3  * * 0  php artisan db:backup --compress --keep=28
```

## ⚙️ Setup Required for Cron Jobs

For these scheduled backups to run automatically, you need to add Laravel's scheduler to your server's cron:

### For Linux/Unix Servers:

Add this line to your crontab (`crontab -e`):

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Important**: Replace `/path-to-your-project` with your actual project path.

### For Windows Servers:

Use Task Scheduler to run this command every minute:

```batch
php artisan schedule:run
```

Or use Laravel's Windows Task Scheduler package.

### For Local Development:

If you want to test the scheduler locally, you can run:

```bash
php artisan schedule:work
```

This will run the scheduler every minute in the foreground (useful for testing).

## 🔧 Modifying Backup Schedules

To change backup times or frequency, edit `app/Console/Kernel.php`:

### Example: Change Daily Backup to 1 AM

```php
$schedule->command('db:backup --compress --keep=7')
         ->dailyAt('01:00')  // Changed from '02:00'
         ->timezone(config('app.timezone', 'UTC'))
         ->withoutOverlapping()
         ->runInBackground()
         ->appendOutputTo(storage_path('logs/backup.log'));
```

### Example: Add Hourly Backups

```php
$schedule->command('db:backup --compress --keep=24')
         ->hourly()
         ->timezone(config('app.timezone', 'UTC'))
         ->withoutOverlapping()
         ->runInBackground()
         ->appendOutputTo(storage_path('logs/backup.log'));
```

### Example: Add Backup at Specific Time (e.g., 11 PM)

```php
$schedule->command('db:backup --compress --keep=7')
         ->dailyAt('23:00')
         ->timezone(config('app.timezone', 'UTC'))
         ->withoutOverlapping()
         ->runInBackground()
         ->appendOutputTo(storage_path('logs/backup.log'));
```

## 📊 Backup Schedule Options

### Frequency Options:
- `->hourly()` - Every hour
- `->daily()` - Once per day at midnight
- `->dailyAt('14:00')` - Once per day at specific time
- `->twiceDaily(1, 13)` - Twice per day (1 AM and 1 PM)
- `->weekly()` - Once per week
- `->weeklyOn(0, '03:00')` - Every Sunday at 3 AM (0 = Sunday)
- `->monthly()` - Once per month
- `->cron('0 2 * * *')` - Custom cron expression

### Other Useful Options:
- `->withoutOverlapping()` - Prevents multiple instances from running
- `->runInBackground()` - Runs in background (non-blocking)
- `->appendOutputTo('path/to/log')` - Logs output to file
- `->timezone('America/New_York')` - Set specific timezone

## 🕐 Timezone

Backups use your application's timezone from `config/app.php`:
```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

To change timezone, update `APP_TIMEZONE` in your `.env` file.

## ✅ Verify Cron Jobs Are Running

1. Check if backups are being created:
   ```bash
   ls -lh storage/app/backups/
   ```

2. Check backup logs:
   ```bash
   tail -f storage/logs/backup.log
   ```

3. Test the backup command manually:
   ```bash
   php artisan db:backup --compress --keep=7
   ```

4. Test the scheduler:
   ```bash
   php artisan schedule:run
   ```

## 📝 Notes

- The `--keep=7` option means only the last 7 daily backups are kept
- The `--keep=28` option means only the last 28 weekly backups are kept
- Old backups are automatically deleted when the limit is exceeded
- All backups are compressed (`.sql.gz` files) to save space
- Backups run in the background to avoid blocking other tasks

