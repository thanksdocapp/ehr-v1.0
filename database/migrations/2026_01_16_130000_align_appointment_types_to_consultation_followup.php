<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        $allowedTypes = ['consultation', 'followup'];

        DB::statement("UPDATE appointments SET type = 'consultation' WHERE type IN ('emergency', 'checkup', 'surgery')");

        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE appointments RENAME TO appointments_old');

            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->string('appointment_number')->unique();
                $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->string('type')->default('consultation');
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled'])->default('pending');
                $table->text('reason')->nullable();
                $table->text('symptoms')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('fee', 8, 2)->nullable();
                $table->boolean('is_online')->default(false);
                $table->string('meeting_link')->nullable();
                $table->datetime('check_in_time')->nullable();
                $table->datetime('check_out_time')->nullable();
                $table->text('prescription')->nullable();
                $table->text('diagnosis')->nullable();
                $table->json('vital_signs')->nullable();
                $table->text('follow_up_instructions')->nullable();
                $table->date('next_appointment_date')->nullable();
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO appointments (
                    id, appointment_number, patient_id, doctor_id, department_id, appointment_date,
                    appointment_time, type, status, reason, symptoms, notes, fee, is_online,
                    meeting_link, check_in_time, check_out_time, prescription, diagnosis, vital_signs,
                    follow_up_instructions, next_appointment_date, created_at, updated_at
                )
                SELECT
                    id, appointment_number, patient_id, doctor_id, department_id, appointment_date,
                    appointment_time, type, status, reason, symptoms, notes, fee, is_online,
                    meeting_link, check_in_time, check_out_time, prescription, diagnosis, vital_signs,
                    follow_up_instructions, next_appointment_date, created_at, updated_at
                FROM appointments_old
            ");

            Schema::drop('appointments_old');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE appointments MODIFY COLUMN type ENUM('" . implode("','", $allowedTypes) . "') NOT NULL DEFAULT 'consultation'"
            );
            return;
        }

        try {
            DB::statement("ALTER TABLE appointments ALTER COLUMN type TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_type_check");
            DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_type_check CHECK (type IN ('" . implode("','", $allowedTypes) . "'))");
        } catch (\Exception $e) {
            \Log::warning('Could not align appointments.type constraint: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        $allowedTypes = ['consultation', 'followup', 'emergency', 'checkup', 'surgery'];

        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE appointments RENAME TO appointments_new');

            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->string('appointment_number')->unique();
                $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->string('type')->default('consultation');
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled'])->default('pending');
                $table->text('reason')->nullable();
                $table->text('symptoms')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('fee', 8, 2)->nullable();
                $table->boolean('is_online')->default(false);
                $table->string('meeting_link')->nullable();
                $table->datetime('check_in_time')->nullable();
                $table->datetime('check_out_time')->nullable();
                $table->text('prescription')->nullable();
                $table->text('diagnosis')->nullable();
                $table->json('vital_signs')->nullable();
                $table->text('follow_up_instructions')->nullable();
                $table->date('next_appointment_date')->nullable();
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO appointments (
                    id, appointment_number, patient_id, doctor_id, department_id, appointment_date,
                    appointment_time, type, status, reason, symptoms, notes, fee, is_online,
                    meeting_link, check_in_time, check_out_time, prescription, diagnosis, vital_signs,
                    follow_up_instructions, next_appointment_date, created_at, updated_at
                )
                SELECT
                    id, appointment_number, patient_id, doctor_id, department_id, appointment_date,
                    appointment_time, type, status, reason, symptoms, notes, fee, is_online,
                    meeting_link, check_in_time, check_out_time, prescription, diagnosis, vital_signs,
                    follow_up_instructions, next_appointment_date, created_at, updated_at
                FROM appointments_new
            ");

            Schema::drop('appointments_new');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE appointments MODIFY COLUMN type ENUM('" . implode("','", $allowedTypes) . "') NOT NULL DEFAULT 'consultation'"
            );
            return;
        }

        try {
            DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_type_check");
            DB::statement("ALTER TABLE appointments ALTER COLUMN type TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_type_check CHECK (type IN ('" . implode("','", $allowedTypes) . "'))");
        } catch (\Exception $e) {
            \Log::warning('Could not revert appointments.type constraint: ' . $e->getMessage());
        }
    }
};
