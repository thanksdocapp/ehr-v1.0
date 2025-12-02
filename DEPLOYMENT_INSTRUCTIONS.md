# Deployment Instructions - Recent Tables Migration

This document provides instructions for adding the recent booking system tables to your live database.

## Files Included

1. **add_recent_tables_to_live.sql** - Complete SQL script with all table/column additions
2. This file (DEPLOYMENT_INSTRUCTIONS.md) - Deployment guide

## What This Migration Adds

### New Tables:
1. **booking_services** - Stores services available for public booking
2. **doctor_service_prices** - Doctor-specific pricing overrides for services
3. **invoice_items** - Individual items on invoices (if not already exists)

### New Columns:
1. **patients.is_guest** - Boolean flag to identify guest patients from public bookings
2. **appointments.service_id** - Links appointments to booking services
3. **appointments.created_from** - Tracks where appointment was created (e.g., "Public Booking Link")
4. **invoices.payment_token** - Token for public payment links
5. **invoices.payment_token_expires_at** - Expiration date for payment tokens

### Column Modifications:
1. **patients.date_of_birth** - Made nullable (for guest patients)
2. **patients.gender** - Made nullable (for guest patients)

### Settings:
1. **public_booking_enabled** - Setting to enable/disable public booking

## Deployment Steps

### Option 1: Direct SQL Execution (Recommended)

1. **Backup your database first!**
   ```bash
   mysqldump -u [username] -p [database_name] > backup_before_migration.sql
   ```

2. **Connect to your live database:**
   ```bash
   mysql -u [username] -p [database_name]
   ```

3. **Run the SQL script:**
   ```sql
   source add_recent_tables_to_live.sql;
   ```
   
   Or from command line:
   ```bash
   mysql -u [username] -p [database_name] < add_recent_tables_to_live.sql
   ```

4. **Verify the migration:**
   The script will output a summary at the end showing which tables/columns were created.

### Option 2: Using Laravel Migrations (If you have SSH access)

1. **Upload the migration files to your server:**
   - `database/migrations/2025_11_28_100000_add_is_guest_to_patients_table.php`
   - `database/migrations/2025_11_28_100001_create_booking_services_table.php`
   - `database/migrations/2025_11_28_100002_create_doctor_service_prices_table.php`
   - `database/migrations/2025_11_28_100003_add_service_fields_to_appointments_table.php`
   - `database/migrations/2025_11_28_100004_add_public_booking_setting_to_settings_table.php`
   - `database/migrations/2025_12_02_000001_make_patient_fields_nullable_for_guests.php`

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

## Safety Features

The SQL script is **idempotent** - it's safe to run multiple times:
- Checks for table/column existence before creating
- Won't duplicate data
- Won't break if tables/columns already exist

## Verification

After running the migration, verify:

1. **Check tables exist:**
   ```sql
   SHOW TABLES LIKE 'booking_services';
   SHOW TABLES LIKE 'doctor_service_prices';
   SHOW TABLES LIKE 'invoice_items';
   ```

2. **Check columns exist:**
   ```sql
   DESCRIBE patients;  -- Should show is_guest column
   DESCRIBE appointments;  -- Should show service_id and created_from columns
   DESCRIBE invoices;  -- Should show payment_token and payment_token_expires_at columns
   ```

3. **Check setting exists:**
   ```sql
   SELECT * FROM settings WHERE `key` = 'public_booking_enabled';
   ```

## Troubleshooting

### Foreign Key Errors
If you get foreign key errors when creating `doctor_service_prices`:
- Make sure `booking_services` table was created first
- Make sure `doctors` table exists

### Column Already Exists Errors
- This is normal if you've run the script before
- The script handles this gracefully

### ENUM Modification Errors
- The script uses raw SQL for ENUM modifications
- If it fails, you may need to update existing NULL values first

## Rollback (If Needed)

If you need to rollback:

1. **Drop new tables:**
   ```sql
   DROP TABLE IF EXISTS doctor_service_prices;
   DROP TABLE IF EXISTS booking_services;
   DROP TABLE IF EXISTS invoice_items;
   ```

2. **Remove new columns:**
   ```sql
   ALTER TABLE appointments DROP COLUMN IF EXISTS service_id;
   ALTER TABLE appointments DROP COLUMN IF EXISTS created_from;
   ALTER TABLE patients DROP COLUMN IF EXISTS is_guest;
   ALTER TABLE invoices DROP COLUMN IF EXISTS payment_token;
   ALTER TABLE invoices DROP COLUMN IF EXISTS payment_token_expires_at;
   ```

3. **Revert nullable columns (if needed):**
   ```sql
   -- Update NULL values first
   UPDATE patients SET date_of_birth = '1900-01-01' WHERE date_of_birth IS NULL;
   UPDATE patients SET gender = 'other' WHERE gender IS NULL;
   
   -- Then make NOT NULL
   ALTER TABLE patients MODIFY COLUMN date_of_birth DATE NOT NULL;
   ALTER TABLE patients MODIFY COLUMN gender ENUM('male', 'female', 'other') NOT NULL;
   ```

4. **Remove setting:**
   ```sql
   DELETE FROM settings WHERE `key` = 'public_booking_enabled';
   ```

## Support

If you encounter any issues, check:
1. Database user has CREATE, ALTER, and INDEX permissions
2. All required parent tables exist (users, doctors, invoices, etc.)
3. No conflicting constraints exist

