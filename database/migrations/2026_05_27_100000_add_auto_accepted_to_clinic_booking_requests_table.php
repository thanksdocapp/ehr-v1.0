<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clinic_booking_requests')) {
            return;
        }

        if (! Schema::hasColumn('clinic_booking_requests', 'auto_accepted')) {
            if (Schema::hasColumn('clinic_booking_requests', 'accepted_at')) {
                Schema::table('clinic_booking_requests', function (Blueprint $table) {
                    $table->boolean('auto_accepted')->default(false)->after('accepted_at');
                });
            } else {
                Schema::table('clinic_booking_requests', function (Blueprint $table) {
                    $table->boolean('auto_accepted')->default(false);
                });
            }
        }

        if (! Schema::hasTable('appointments')) {
            return;
        }

        DB::table('clinic_booking_requests')
            ->where('status', 'accepted')
            ->whereNull('doctor_id')
            ->whereNotNull('appointment_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $appointment = DB::table('appointments')->where('id', $row->appointment_id)->first();
                    if (! $appointment?->doctor_id) {
                        continue;
                    }
                    DB::table('clinic_booking_requests')
                        ->where('id', $row->id)
                        ->update(['doctor_id' => $appointment->doctor_id]);
                }
            });

        DB::table('clinic_booking_requests')
            ->where('status', 'accepted')
            ->whereNull('department_id')
            ->whereNotNull('appointment_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $appointment = DB::table('appointments')->where('id', $row->appointment_id)->first();
                    if (! $appointment?->department_id) {
                        continue;
                    }
                    DB::table('clinic_booking_requests')
                        ->where('id', $row->id)
                        ->update(['department_id' => $appointment->department_id]);
                }
            });

    }

    public function down(): void
    {
        if (! Schema::hasTable('clinic_booking_requests')) {
            return;
        }

        if (Schema::hasColumn('clinic_booking_requests', 'auto_accepted')) {
            Schema::table('clinic_booking_requests', function (Blueprint $table) {
                $table->dropColumn('auto_accepted');
            });
        }
    }
};
