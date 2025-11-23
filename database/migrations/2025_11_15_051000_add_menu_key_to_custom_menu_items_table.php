<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('custom_menu_items')) {
            if (!Schema::hasColumn('custom_menu_items', 'menu_key')) {
                // Add menu_key column
                Schema::table('custom_menu_items', function (Blueprint $table) {
                    $table->string('menu_key')->nullable()->after('id');
                });
                
                // Generate menu_key for existing records
                $items = DB::table('custom_menu_items')->whereNull('menu_key')->get();
                foreach ($items as $item) {
                    $baseKey = 'custom-' . strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($item->label)));
                    $counter = 1;
                    $menuKey = $baseKey;
                    
                    while (DB::table('custom_menu_items')->where('menu_key', $menuKey)->exists()) {
                        $menuKey = $baseKey . '-' . $counter;
                        $counter++;
                    }
                    
                    DB::table('custom_menu_items')
                        ->where('id', $item->id)
                        ->update(['menu_key' => $menuKey]);
                }
                
                // Make menu_key required and unique after populating
                Schema::table('custom_menu_items', function (Blueprint $table) {
                    $table->string('menu_key')->nullable(false)->unique()->change();
                });
            } else {
                // If menu_key exists but some records don't have it, populate them
                $items = DB::table('custom_menu_items')->whereNull('menu_key')->get();
                foreach ($items as $item) {
                    $baseKey = 'custom-' . strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($item->label)));
                    $counter = 1;
                    $menuKey = $baseKey;
                    
                    while (DB::table('custom_menu_items')->where('menu_key', $menuKey)->exists()) {
                        $menuKey = $baseKey . '-' . $counter;
                        $counter++;
                    }
                    
                    DB::table('custom_menu_items')
                        ->where('id', $item->id)
                        ->update(['menu_key' => $menuKey]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('custom_menu_items') && Schema::hasColumn('custom_menu_items', 'menu_key')) {
            Schema::table('custom_menu_items', function (Blueprint $table) {
                $table->dropUnique(['menu_key']);
                $table->dropColumn('menu_key');
            });
        }
    }
};

