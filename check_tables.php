<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = [
    'booking_services',
    'doctor_service_prices',
];

$columns = [
    'patients' => 'is_guest',
    'appointments' => 'service_id',
    'appointments' => 'created_from',
];

echo "Checking tables and columns...\n\n";

foreach ($tables as $table) {
    $exists = Schema::hasTable($table);
    echo $table . ": " . ($exists ? "EXISTS" : "DOES NOT EXIST") . "\n";
}

echo "\nChecking columns...\n";
echo "patients.is_guest: " . (Schema::hasColumn('patients', 'is_guest') ? "EXISTS" : "DOES NOT EXIST") . "\n";
echo "appointments.service_id: " . (Schema::hasColumn('appointments', 'service_id') ? "EXISTS" : "DOES NOT EXIST") . "\n";
echo "appointments.created_from: " . (Schema::hasColumn('appointments', 'created_from') ? "EXISTS" : "DOES NOT EXIST") . "\n";

if (!Schema::hasTable('booking_services')) {
    echo "\n=== Creating booking_services table ===\n";
    try {
        DB::statement("
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
                KEY `booking_services_created_by_foreign` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Add foreign key separately to avoid errors if users table structure is different
        try {
            DB::statement("ALTER TABLE `booking_services` ADD CONSTRAINT `booking_services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL");
        } catch (Exception $e) {
            // Foreign key might already exist or users table might not exist - that's okay
        }
        
        echo "✓ booking_services table created!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

if (!Schema::hasTable('doctor_service_prices') && Schema::hasTable('booking_services')) {
    echo "\n=== Creating doctor_service_prices table ===\n";
    try {
        DB::statement("
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
                UNIQUE KEY `doctor_service_prices_doctor_id_service_id_unique` (`doctor_id`,`service_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Add foreign keys separately
        try {
            DB::statement("ALTER TABLE `doctor_service_prices` ADD CONSTRAINT `doctor_service_prices_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE");
        } catch (Exception $e) {}
        
        try {
            DB::statement("ALTER TABLE `doctor_service_prices` ADD CONSTRAINT `doctor_service_prices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE CASCADE");
        } catch (Exception $e) {}
        
        echo "✓ doctor_service_prices table created!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

if (!Schema::hasColumn('patients', 'is_guest')) {
    echo "\n=== Adding is_guest column to patients ===\n";
    try {
        DB::statement("ALTER TABLE `patients` ADD COLUMN `is_guest` tinyint(1) NOT NULL DEFAULT 0");
        echo "✓ is_guest column added!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

if (!Schema::hasColumn('appointments', 'service_id') && Schema::hasTable('booking_services')) {
    echo "\n=== Adding service_id column to appointments ===\n";
    try {
        DB::statement("ALTER TABLE `appointments` ADD COLUMN `service_id` bigint(20) UNSIGNED DEFAULT NULL");
        try {
            DB::statement("ALTER TABLE `appointments` ADD CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `booking_services` (`id`) ON DELETE SET NULL");
        } catch (Exception $e) {}
        echo "✓ service_id column added!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

if (!Schema::hasColumn('appointments', 'created_from')) {
    echo "\n=== Adding created_from column to appointments ===\n";
    try {
        DB::statement("ALTER TABLE `appointments` ADD COLUMN `created_from` varchar(255) NOT NULL DEFAULT 'Internal'");
        echo "✓ created_from column added!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Final Check ===\n";
echo "booking_services: " . (Schema::hasTable('booking_services') ? "✓ EXISTS" : "✗ MISSING") . "\n";
echo "doctor_service_prices: " . (Schema::hasTable('doctor_service_prices') ? "✓ EXISTS" : "✗ MISSING") . "\n";
echo "patients.is_guest: " . (Schema::hasColumn('patients', 'is_guest') ? "✓ EXISTS" : "✗ MISSING") . "\n";
echo "appointments.service_id: " . (Schema::hasColumn('appointments', 'service_id') ? "✓ EXISTS" : "✗ MISSING") . "\n";
echo "appointments.created_from: " . (Schema::hasColumn('appointments', 'created_from') ? "✓ EXISTS" : "✗ MISSING") . "\n";

echo "\nDone!\n";

