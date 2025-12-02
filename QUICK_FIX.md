# Quick Fix: Create Booking Tables

The booking services table is missing. Here's how to fix it:

## Option 1: Run the Check Script (Recommended)

Open your terminal/command prompt in the project root and run:

```bash
php check_tables.php
```

This script will:
- Check if tables exist
- Create missing tables automatically
- Show you the status

## Option 2: Run Migrations

```bash
php artisan migrate
```

Or run specific migrations:

```bash
php artisan migrate --path=database/migrations/2025_11_28_100001_create_booking_services_table.php
php artisan migrate --path=database/migrations/2025_11_28_100002_create_doctor_service_prices_table.php
php artisan migrate --path=database/migrations/2025_11_28_100000_add_is_guest_to_patients_table.php
php artisan migrate --path=database/migrations/2025_11_28_100003_add_service_fields_to_appointments_table.php
php artisan migrate --path=database/migrations/2025_11_28_100004_add_public_booking_setting_to_settings_table.php
```

## Option 3: Run SQL Directly

If migrations don't work, you can run the SQL directly in your database:

1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Select your database: `thanksdocnotes_db`
3. Run the SQL from `create_booking_tables.sql`

## Verify It Worked

After running any of the above, refresh the page. The error should be gone and you should be able to access:
- Admin → Booking Services
- Staff → Doctor Services

If you still see errors, check the Laravel log:
```bash
tail -f storage/logs/laravel.log
```

