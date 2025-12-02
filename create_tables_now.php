<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$output = [];
$output[] = "=== Creating Booking System Tables ===\n";

try {
    // Create booking_services table
    if (!Schema::hasTable('booking_services')) {
        $output[] = "Creating booking_services table...\n";
        DB::statement("
            CREATE TABLE `booking_services` (
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
                KEY `booking_services_created_by_foreign` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        try {
            DB::statement("ALTER TABLE `booking_services` ADD CONSTRAINT `booking_services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL");
        } catch (Exception $e) {
            // Foreign key might already exist
        }
        
        $output[] = "✓ booking_services table created!\n";
    } else {
        $output[] = "✓ booking_services table already exists\n";
    }

    // Create doctor_service_prices table
    if (!Schema::hasTable('doctor_service_prices')) {
        $output[] = "Creating doctor_service_prices table...\n";
        DB::statement("
            CREATE TABLE `doctor_service_prices` (
                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `doctor_id` bigint(20) UNSIGNED NOT NULL,
                `service_id` bigint(20) UNSIGNED NOT NULL,
                `custom_price` decimal(10,2) DEFAULT NULL,
                `custom_duration_minutes` int(11) DEFAULT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `doctor_service_prices_doctor_id_service_id_unique` (`doctor_id`,`service_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        try {
            DB::statement("ALTER TABLE `doctor_service_prices` ADD CONSTRAINT `doctor_service_prices_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE");
            DB::statement("ALTER TABLE `doctor_service_prices` ADD CONSTRAINT `doctor_service_prices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE CASCADE");
        } catch (Exception $e) {
            // Foreign keys might already exist
        }
        
        $output[] = "✓ doctor_service_prices table created!\n";
    } else {
        $output[] = "✓ doctor_service_prices table already exists\n";
    }

    // Add is_guest column
    if (!Schema::hasColumn('patients', 'is_guest')) {
        $output[] = "Adding is_guest column to patients...\n";
        DB::statement("ALTER TABLE `patients` ADD COLUMN `is_guest` tinyint(1) NOT NULL DEFAULT 0");
        $output[] = "✓ is_guest column added!\n";
    } else {
        $output[] = "✓ patients.is_guest column already exists\n";
    }

    // Add service_id column
    if (!Schema::hasColumn('appointments', 'service_id')) {
        $output[] = "Adding service_id column to appointments...\n";
        DB::statement("ALTER TABLE `appointments` ADD COLUMN `service_id` bigint(20) UNSIGNED DEFAULT NULL");
        try {
            DB::statement("ALTER TABLE `appointments` ADD CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE SET NULL");
        } catch (Exception $e) {}
        $output[] = "✓ service_id column added!\n";
    } else {
        $output[] = "✓ appointments.service_id column already exists\n";
    }

    // Add created_from column
    if (!Schema::hasColumn('appointments', 'created_from')) {
        $output[] = "Adding created_from column to appointments...\n";
        DB::statement("ALTER TABLE `appointments` ADD COLUMN `created_from` varchar(255) NOT NULL DEFAULT 'Internal'");
        $output[] = "✓ created_from column added!\n";
    } else {
        $output[] = "✓ appointments.created_from column already exists\n";
    }

    $output[] = "\n=== SUCCESS! All tables created ===\n";
    $output[] = "You can now access the booking services pages.\n";

} catch (Exception $e) {
    $output[] = "\n✗ ERROR: " . $e->getMessage() . "\n";
    $output[] = $e->getTraceAsString() . "\n";
}

// Write to file and echo
$result = implode('', $output);
file_put_contents('migration_result.txt', $result);
echo $result;

