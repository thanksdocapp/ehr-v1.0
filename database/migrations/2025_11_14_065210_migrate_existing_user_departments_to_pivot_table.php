<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate existing department_id values to the user_department pivot table
     */
    public function up(): void
    {
        // Check if pivot table exists
        if (!Schema::hasTable('user_department')) {
            return; // Pivot table not created yet, skip
        }

        // Check if users table has department_id column
        if (!Schema::hasColumn('users', 'department_id')) {
            return; // No department_id column, skip
        }

        // Migrate existing user-department relationships
        $users = DB::table('users')
            ->whereNotNull('department_id')
            ->where('department_id', '!=', 0)
            ->get();

        foreach ($users as $user) {
            // Check if relationship already exists in pivot table
            $exists = DB::table('user_department')
                ->where('user_id', $user->id)
                ->where('department_id', $user->department_id)
                ->exists();

            if (!$exists) {
                DB::table('user_department')->insert([
                    'user_id' => $user->id,
                    'department_id' => $user->department_id,
                    'is_primary' => true, // Mark as primary since it was the only department
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all entries from pivot table
        DB::table('user_department')->truncate();
    }
};
