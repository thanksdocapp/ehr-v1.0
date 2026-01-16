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
        $allowedTypes = ['consultation', 'followup', 'emergency', 'checkup', 'surgery'];

        // Normalize existing values to the new appointment-aligned set
        DB::statement("UPDATE medical_records SET record_type = 'followup' WHERE record_type = 'follow_up'");
        DB::statement("UPDATE medical_records SET record_type = 'checkup' WHERE record_type = 'routine_checkup'");
        DB::statement("UPDATE medical_records SET record_type = 'consultation' WHERE record_type IN ('diagnosis', 'prescription', 'lab_result', 'discharge', 'procedure', 'administration_update')");

        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE medical_records RENAME TO medical_records_old');

            Schema::create('medical_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
                $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
                $table->string('record_type');
                $table->string('diagnosis')->nullable();
                $table->text('symptoms')->nullable();
                $table->text('treatment')->nullable();
                $table->text('notes')->nullable();
                $table->text('vital_signs')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->boolean('is_private')->default(false);
                $table->date('record_date')->nullable();
                $table->text('presenting_complaint')->nullable();
                $table->text('history_of_presenting_complaint')->nullable();
                $table->text('past_medical_history')->nullable();
                $table->text('drug_history')->nullable();
                $table->text('allergies')->nullable();
                $table->text('social_history')->nullable();
                $table->text('family_history')->nullable();
                $table->text('ideas_concerns_expectations')->nullable();
                $table->text('plan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['patient_id', 'created_at']);
                $table->index(['doctor_id', 'created_at']);
                $table->index('record_type');
            });

            DB::statement("
                INSERT INTO medical_records (
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms,
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    presenting_complaint, history_of_presenting_complaint, past_medical_history,
                    drug_history, allergies, social_history, family_history, ideas_concerns_expectations,
                    plan, created_by, updated_by, created_at, updated_at
                )
                SELECT
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms,
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    presenting_complaint, history_of_presenting_complaint, past_medical_history,
                    drug_history, allergies, social_history, family_history, ideas_concerns_expectations,
                    plan, created_by, updated_by, created_at, updated_at
                FROM medical_records_old
            ");

            Schema::drop('medical_records_old');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE medical_records MODIFY COLUMN record_type ENUM('" . implode("','", $allowedTypes) . "') NOT NULL"
            );
            return;
        }

        try {
            DB::statement("ALTER TABLE medical_records ALTER COLUMN record_type TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS medical_records_record_type_check");
            DB::statement("ALTER TABLE medical_records ADD CONSTRAINT medical_records_record_type_check CHECK (record_type IN ('" . implode("','", $allowedTypes) . "'))");
        } catch (\Exception $e) {
            \Log::warning('Could not align medical_records.record_type constraint: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        $allowedTypes = ['consultation', 'diagnosis', 'prescription', 'lab_result', 'follow_up', 'discharge'];

        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE medical_records RENAME TO medical_records_new');

            Schema::create('medical_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
                $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
                $table->string('record_type');
                $table->string('diagnosis')->nullable();
                $table->text('symptoms')->nullable();
                $table->text('treatment')->nullable();
                $table->text('notes')->nullable();
                $table->text('vital_signs')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->boolean('is_private')->default(false);
                $table->date('record_date')->nullable();
                $table->text('presenting_complaint')->nullable();
                $table->text('history_of_presenting_complaint')->nullable();
                $table->text('past_medical_history')->nullable();
                $table->text('drug_history')->nullable();
                $table->text('allergies')->nullable();
                $table->text('social_history')->nullable();
                $table->text('family_history')->nullable();
                $table->text('ideas_concerns_expectations')->nullable();
                $table->text('plan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['patient_id', 'created_at']);
                $table->index(['doctor_id', 'created_at']);
                $table->index('record_type');
            });

            DB::statement("
                INSERT INTO medical_records (
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms,
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    presenting_complaint, history_of_presenting_complaint, past_medical_history,
                    drug_history, allergies, social_history, family_history, ideas_concerns_expectations,
                    plan, created_by, updated_by, created_at, updated_at
                )
                SELECT
                    id, patient_id, doctor_id, appointment_id,
                    CASE
                        WHEN record_type = 'followup' THEN 'follow_up'
                        WHEN record_type = 'checkup' THEN 'consultation'
                        WHEN record_type = 'surgery' THEN 'consultation'
                        ELSE record_type
                    END as record_type,
                    diagnosis, symptoms, treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    presenting_complaint, history_of_presenting_complaint, past_medical_history,
                    drug_history, allergies, social_history, family_history, ideas_concerns_expectations,
                    plan, created_by, updated_by, created_at, updated_at
                FROM medical_records_new
            ");

            Schema::drop('medical_records_new');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE medical_records MODIFY COLUMN record_type ENUM('" . implode("','", $allowedTypes) . "') NOT NULL"
            );
            return;
        }

        try {
            DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS medical_records_record_type_check");
            DB::statement("ALTER TABLE medical_records ALTER COLUMN record_type TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE medical_records ADD CONSTRAINT medical_records_record_type_check CHECK (record_type IN ('" . implode("','", $allowedTypes) . "'))");
        } catch (\Exception $e) {
            \Log::warning('Could not revert medical_records.record_type constraint: ' . $e->getMessage());
        }
    }
};
