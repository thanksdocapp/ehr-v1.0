<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$results = [];

$results[] = "=== Table Verification ===\n\n";

$tables = [
    'booking_services' => 'Booking Services',
    'doctor_service_prices' => 'Doctor Service Prices',
];

foreach ($tables as $table => $name) {
    $exists = Schema::hasTable($table);
    $results[] = $name . " (" . $table . "): " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

$results[] = "\n=== Column Verification ===\n\n";

$columns = [
    'patients' => ['is_guest' => 'Is Guest'],
    'appointments' => ['service_id' => 'Service ID', 'created_from' => 'Created From'],
];

foreach ($columns as $table => $cols) {
    foreach ($cols as $col => $label) {
        $exists = Schema::hasColumn($table, $col);
        $results[] = $table . "." . $col . " (" . $label . "): " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
    }
}

$output = implode('', $results);
file_put_contents('table_verification.txt', $output);
echo $output;

