# AGENTS.md

## Cursor Cloud specific instructions

### Overview

ThankDoc EHR is a Laravel 10 hospital management system (PHP 8.x, MariaDB/MySQL). See `README.md` for feature details and standard setup steps.

### Services

| Service | How to start | Notes |
|---|---|---|
| **MariaDB** | `sudo mkdir -p /run/mysqld && sudo chown mysql:mysql /run/mysqld && sudo chmod 755 /run/mysqld && sudo mysqld_safe &` | Must be running before any artisan command |
| **Laravel dev server** | `php artisan serve --host=0.0.0.0 --port=8000` | Homepage at `/`, admin at `/admin/login` |

### Key gotchas

- **SmsNotificationService eagerly queries `site_settings` table**: Any artisan command (including `migrate`, `key:generate`, `package:discover`) will fail if the `site_settings` table does not exist in the connected database. On a fresh database, you must manually create this table before running migrations. See the migration `2024_01_01_000001_create_site_settings_table.php` for the schema, then record it in the `migrations` table to avoid conflicts.
- **PHPUnit uses MySQL, not SQLite**: The `phpunit.xml` is configured to use a MySQL database (`thankdoc_ehr_test`) because the eager `SmsNotificationService` DB query breaks SQLite in-memory tests. The `database/database.sqlite` file must also exist (can be empty) to prevent `SQLiteServiceProvider` boot errors.
- **Missing `PaymentSeeder`**: The `DatabaseSeeder` references `PaymentSeeder` which does not exist. Running `php artisan db:seed` will fail on this class. Seed individual seeders or skip `PaymentSeeder`.
- **PSR-4 autoload mismatches**: `HospitalSystemSeeder` (in `HospitalManagementSeeder.php`) and `PublicBookingService` (in `BookingService.php`) don't match their filenames. Composer will skip these classes with a warning; this is harmless.

### Test credentials

| Role | Email | Password | Login URL |
|---|---|---|---|
| Admin | `admin@hospital.com` | `admin123` | `/admin/login` |
| Super Admin | `kelvin@newwaves.com` | `NewWaves2024!` | `/admin/login` |
| Test Patient | `john.smith@example.com` | `password123` | `/login` |

### Commands reference

- **Lint**: `./vendor/bin/pint --test` (check) or `./vendor/bin/pint` (fix)
- **Tests**: `./vendor/bin/phpunit`
- **Dev server**: `php artisan serve --host=0.0.0.0 --port=8000`
- **Migrations**: `php artisan migrate --no-interaction --force`
- **Seed**: `php artisan db:seed --no-interaction --force` (will fail on PaymentSeeder; seed individually if needed)

### Database configuration

- **Dev DB**: `thankdoc_ehr` (user: `thankdoc`, pass: `thankdoc_pass`)
- **Test DB**: `thankdoc_ehr_test` (same credentials)
