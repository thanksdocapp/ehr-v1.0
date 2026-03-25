<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_booking_discount_codes')) {
            Schema::create('clinic_booking_discount_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
                $table->string('code', 64);
                $table->enum('discount_type', ['percent', 'fixed']);
                $table->decimal('discount_value', 10, 2);
                $table->foreignId('booking_service_id')->nullable()->constrained('booking_services')->nullOnDelete();
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('uses_count')->default(0);
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['department_id', 'code']);
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'clinic_booking_discount_code_id')) {
                $table->foreignId('clinic_booking_discount_code_id')
                    ->nullable()
                    ->constrained('clinic_booking_discount_codes')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'clinic_booking_discount_code_id')) {
                $table->dropForeign(['clinic_booking_discount_code_id']);
                $table->dropColumn('clinic_booking_discount_code_id');
            }
        });

        Schema::dropIfExists('clinic_booking_discount_codes');
    }
};
