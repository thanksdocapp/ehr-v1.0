<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Running Booking System Migrations ===\n\n";

try {
    // Create booking_services table
    if (!Schema::hasTable('booking_services')) {
        echo "Creating booking_services table...\n";
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
                KEY `booking_services_created_by_foreign` (`created_by`),
                CONSTRAINT `booking_services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "✓ booking_services table created\n";
    } else {
        echo "✓ booking_services table already exists\n";
    }

    // Create doctor_service_prices table
    if (!Schema::hasTable('doctor_service_prices')) {
        echo "Creating doctor_service_prices table...\n";
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
                UNIQUE KEY `doctor_service_prices_doctor_id_service_id_unique` (`doctor_id`,`service_id`),
                KEY `doctor_service_prices_doctor_id_foreign` (`doctor_id`),
                KEY `doctor_service_prices_service_id_foreign` (`service_id`),
                CONSTRAINT `doctor_service_prices_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
                CONSTRAINT `doctor_service_prices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "✓ doctor_service_prices table created\n";
    } else {
        echo "✓ doctor_service_prices table already exists\n";
    }

    // Add is_guest column to patients
    if (!Schema::hasColumn('patients', 'is_guest')) {
        echo "Adding is_guest column to patients table...\n";
        DB::statement("ALTER TABLE `patients` ADD COLUMN `is_guest` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`");
        echo "✓ is_guest column added\n";
    } else {
        echo "✓ patients.is_guest column already exists\n";
    }

    // Add service_id to appointments
    if (!Schema::hasColumn('appointments', 'service_id')) {
        echo "Adding service_id column to appointments table...\n";
        DB::statement("ALTER TABLE `appointments` ADD COLUMN `service_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `department_id`");
        DB::statement("ALTER TABLE `appointments` ADD CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE SET NULL");
        echo "✓ service_id column added\n";
    } else {
        echo "✓ appointments.service_id column already exists\n";
    }

    // Add created_from to appointments
    if (!Schema::hasColumn('appointments', 'created_from')) {
        echo "Adding created_from column to appointments table...\n";
        DB::statement("ALTER TABLE `appointments` ADD COLUMN `created_from` varchar(255) NOT NULL DEFAULT 'Internal' AFTER `status`");
        echo "✓ created_from column added\n";
    } else {
        echo "✓ appointments.created_from column already exists\n";
    }

    // Add public_booking_enabled setting
    $setting = DB::table('settings')->where('key', 'public_booking_enabled')->first();
    if (!$setting) {
        echo "Adding public_booking_enabled setting...\n";
        DB::table('settings')->insert([
            'key' => 'public_booking_enabled',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'general',
            'description' => 'Enable or disable the public online booking system.',
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ public_booking_enabled setting added\n";
    } else {
        echo "✓ public_booking_enabled setting already exists\n";
    }

    echo "\n=== Migration Complete! ===\n";
    echo "All tables and columns have been created successfully.\n";

} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

