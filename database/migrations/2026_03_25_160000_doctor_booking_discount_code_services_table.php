<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doctor_booking_discount_codes')) {
            return;
        }

        if (!Schema::hasTable('doctor_booking_discount_code_services')) {
            Schema::create('doctor_booking_discount_code_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_booking_discount_code_id')
                    ->constrained('doctor_booking_discount_codes')
                    ->cascadeOnDelete();
                $table->foreignId('booking_service_id')
                    ->constrained('booking_services')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(
                    ['doctor_booking_discount_code_id', 'booking_service_id'],
                    'doc_disc_code_svc_unique'
                );
            });
        }

        if (Schema::hasColumn('doctor_booking_discount_codes', 'booking_service_id')) {
            $rows = DB::table('doctor_booking_discount_codes')
                ->whereNotNull('booking_service_id')
                ->select('id', 'booking_service_id')
                ->get();

            foreach ($rows as $row) {
                DB::table('doctor_booking_discount_code_services')->updateOrInsert(
                    [
                        'doctor_booking_discount_code_id' => $row->id,
                        'booking_service_id' => $row->booking_service_id,
                    ],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            DB::table('doctor_booking_discount_codes')->update(['booking_service_id' => null]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_booking_discount_code_services');
    }
};
