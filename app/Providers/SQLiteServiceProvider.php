<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\ConnectionEvent;

class SQLiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            try {
                DB::statement('PRAGMA foreign_keys = ON;');
            } catch (\Exception $e) {
                // Log error if needed
            }
        }
    }
}
