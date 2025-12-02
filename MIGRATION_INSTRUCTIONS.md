# Migration Instructions for Booking System

The booking system requires several database tables to be created. If you're getting the error:
```
Table 'thanksdocnotes_db.booking_services' doesn't exist
```

## Solution 1: Run Migrations (Recommended)

Run these commands in order:

```bash
php artisan migrate --path=database/migrations/2025_11_28_100001_create_booking_services_table.php
php artisan migrate --path=database/migrations/2025_11_28_100002_create_doctor_service_prices_table.php
php artisan migrate --path=database/migrations/2025_11_28_100000_add_is_guest_to_patients_table.php
php artisan migrate --path=database/migrations/2025_11_28_100003_add_service_fields_to_appointments_table.php
php artisan migrate --path=database/migrations/2025_11_28_100004_add_public_booking_setting_to_settings_table.php
```

Or run all at once:
```bash
php artisan migrate
```

## Solution 2: Manual SQL (If migrations fail)

If migrations don't work, you can run this SQL directly in your database:

```sql
-- Create booking_services table
CREATE TABLE IF NOT EXISTS `booking_services` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `default_duration_minutes` int(11) NOT NULL DEFAULT 30,
    `default_price` decimal(10,2) DEFAULT NULL,
    `tags` json DEFAULT NULL,
    `created_by` bigint(20) UNSIGNED DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `booking_services_created_by_foreign` (`created_by`),
    CONSTRAINT `booking_services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create doctor_service_prices table
CREATE TABLE IF NOT EXISTS `doctor_service_prices` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `doctor_id` bigint(20) UNSIGNED NOT NULL,
    `service_id` bigint(20) UNSIGNED NOT NULL,
    `custom_price` decimal(10,2) DEFAULT NULL,
    `custom_duration_minutes` int(11) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `doctor_service_prices_doctor_id_service_id_unique` (`doctor_id`,`service_id`),
    KEY `doctor_service_prices_doctor_id_foreign` (`doctor_id`),
    KEY `doctor_service_prices_service_id_foreign` (`service_id`),
    CONSTRAINT `doctor_service_prices_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `doctor_service_prices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add is_guest column to patients table
ALTER TABLE `patients` ADD COLUMN IF NOT EXISTS `is_guest` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`;

-- Add service_id and created_from to appointments table
ALTER TABLE `appointments` ADD COLUMN IF NOT EXISTS `service_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `department_id`;
ALTER TABLE `appointments` ADD COLUMN IF NOT EXISTS `created_from` varchar(255) NOT NULL DEFAULT 'Internal' AFTER `status`;

-- Add foreign key for service_id (if column was just added)
-- Note: Run this only if service_id column was just created above
ALTER TABLE `appointments` 
    ADD CONSTRAINT `appointments_service_id_foreign` 
    FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE SET NULL;
```

## Verification

After running migrations or SQL, verify the tables exist:

```bash
php artisan tinker
```

Then in tinker:
```php
Schema::hasTable('booking_services'); // Should return true
Schema::hasTable('doctor_service_prices'); // Should return true
Schema::hasColumn('patients', 'is_guest'); // Should return true
Schema::hasColumn('appointments', 'service_id'); // Should return true
```

## Next Steps

Once tables are created:
1. Go to Admin → Booking Services → Create your first service
2. Enable public booking in Admin → Settings → General
3. Doctors can now customize their service pricing in their dashboard
