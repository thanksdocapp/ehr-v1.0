<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::created(function () {
            if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
                DB::statement('PRAGMA foreign_keys = ON;');
            }
        });
    }
}
