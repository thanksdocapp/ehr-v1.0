<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Testing table access...\n\n";

// Test if we can query the table
try {
    if (Schema::hasTable('booking_services')) {
        $count = DB::table('booking_services')->count();
        echo "SUCCESS: booking_services table exists with $count records\n";
    } else {
        echo "ERROR: booking_services table does NOT exist\n";
        echo "Creating it now...\n";
        
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
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "Table created!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";

