<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the unique (doctor_id, exception_date) constraint so a single date can
     * hold multiple blocks: one all-day block, or several non-overlapping time
     * intervals (partial-day blocking). The non-unique lookup index
     * (dr_avail_exc_lookup) is kept for fast reads.
     */
    public function up(): void
    {
        if (!Schema::hasTable('doctor_availability_exceptions')) {
            return;
        }

        if ($this->hasIndex('doctor_availability_exceptions', 'dr_avail_exc_unique')) {
            Schema::table('doctor_availability_exceptions', function (Blueprint $table) {
                $table->dropUnique('dr_avail_exc_unique');
            });
        }
    }

    /**
     * Reverse: re-add the unique constraint.
     * Note: this will fail if any date already holds more than one block. Such
     * rows must be consolidated before rolling back.
     */
    public function down(): void
    {
        if (!Schema::hasTable('doctor_availability_exceptions')) {
            return;
        }

        if (!$this->hasIndex('doctor_availability_exceptions', 'dr_avail_exc_unique')) {
            Schema::table('doctor_availability_exceptions', function (Blueprint $table) {
                $table->unique(['doctor_id', 'exception_date'], 'dr_avail_exc_unique');
            });
        }
    }

    /**
     * Database-agnostic index existence check (MySQL / SQLite).
     */
    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        try {
            if ($driver === 'mysql') {
                $result = $connection->select(
                    "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                    [$index]
                );
                return count($result) > 0;
            }

            if ($driver === 'sqlite') {
                $result = $connection->select(
                    "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                    [$table, $index]
                );
                return count($result) > 0;
            }
        } catch (\Throwable $e) {
            // If introspection fails, assume present so up() attempts the drop.
            return true;
        }

        return true;
    }
};
