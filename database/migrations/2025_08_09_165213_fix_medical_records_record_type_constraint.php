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
        // For SQLite, we need to recreate the table with the correct enum constraint
        // since SQLite doesn't support ALTER TABLE to modify CHECK constraints
        
        if (DB::getDriverName() === 'sqlite') {
            // First, rename the current table
            DB::statement('ALTER TABLE medical_records RENAME TO medical_records_old');
            
            // Create the new table with correct enum values
            DB::statement('
                CREATE TABLE medical_records (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    patient_id INTEGER NOT NULL,
                    doctor_id INTEGER NOT NULL,
                    appointment_id INTEGER DEFAULT NULL,
                    record_type varchar check (record_type in (\'consultation\', \'diagnosis\', \'prescription\', \'lab_result\', \'follow_up\', \'discharge\')) not null,
                    diagnosis VARCHAR(255) DEFAULT NULL,
                    symptoms TEXT DEFAULT NULL,
                    treatment TEXT DEFAULT NULL,
                    notes TEXT DEFAULT NULL,
                    vital_signs TEXT DEFAULT NULL,
                    follow_up_date DATE DEFAULT NULL,
                    is_private BOOLEAN DEFAULT 0 NOT NULL,
                    record_date date DEFAULT NULL,
                    chief_complaint text DEFAULT NULL,
                    present_illness text DEFAULT NULL,
                    past_medical_history text DEFAULT NULL,
                    physical_examination text DEFAULT NULL,
                    assessment text DEFAULT NULL,
                    plan text DEFAULT NULL,
                    created_by INTEGER DEFAULT NULL,
                    updated_by INTEGER DEFAULT NULL,
                    created_at DATETIME DEFAULT NULL,
                    updated_at DATETIME DEFAULT NULL,
                    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
                    FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE,
                    FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE SET NULL
                )
            ');
            
            // Create indexes manually to avoid conflicts
            try {
                DB::statement('CREATE INDEX medical_records_patient_id_created_at_index ON medical_records (patient_id, created_at)');
            } catch (Exception $e) {
                // Index might already exist, ignore
            }
            
            try {
                DB::statement('CREATE INDEX medical_records_doctor_id_created_at_index ON medical_records (doctor_id, created_at)');
            } catch (Exception $e) {
                // Index might already exist, ignore
            }
            
            try {
                DB::statement('CREATE INDEX medical_records_record_type_index ON medical_records (record_type)');
            } catch (Exception $e) {
                // Index might already exist, ignore
            }
            
            // Copy data from old table to new table, mapping old record_type values to new ones
            DB::statement("
                INSERT INTO medical_records (
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms, 
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    chief_complaint, present_illness, past_medical_history, physical_examination,
                    assessment, plan, created_by, updated_by, created_at, updated_at
                )
                SELECT 
                    id, patient_id, doctor_id, appointment_id,
                    CASE 
                        WHEN record_type = 'emergency' THEN 'consultation'
                        WHEN record_type = 'routine_checkup' THEN 'consultation'
                        WHEN record_type = 'procedure' THEN 'diagnosis'
                        ELSE record_type
                    END as record_type,
                    diagnosis, symptoms, treatment, notes, vital_signs, follow_up_date, is_private,
                    record_date, chief_complaint, present_illness, past_medical_history,
                    physical_examination, assessment, plan, created_by, updated_by, created_at, updated_at
                FROM medical_records_old
            ");
            
            // Drop the old table
            Schema::drop('medical_records_old');
        } else {
            // For MySQL and other databases, first update existing data to match new enum values
            if (DB::getDriverName() === 'mysql') {
                // First, update any old record_type values to new values
                DB::statement("UPDATE medical_records SET record_type = 'consultation' WHERE record_type IN ('emergency', 'routine_checkup')");
                DB::statement("UPDATE medical_records SET record_type = 'diagnosis' WHERE record_type = 'procedure'");
                
                // Now modify the enum column
                DB::statement("ALTER TABLE medical_records MODIFY COLUMN record_type ENUM('consultation', 'diagnosis', 'prescription', 'lab_result', 'follow_up', 'discharge') NOT NULL");
            } else {
                // For PostgreSQL and other databases, use raw SQL
                // First update data
                DB::statement("UPDATE medical_records SET record_type = 'consultation' WHERE record_type IN ('emergency', 'routine_checkup')");
                DB::statement("UPDATE medical_records SET record_type = 'diagnosis' WHERE record_type = 'procedure'");
                
                // Then modify column
                try {
                    DB::statement("ALTER TABLE medical_records ALTER COLUMN record_type TYPE VARCHAR(50)");
                    DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS medical_records_record_type_check");
                    DB::statement("ALTER TABLE medical_records ADD CONSTRAINT medical_records_record_type_check CHECK (record_type IN ('consultation', 'diagnosis', 'prescription', 'lab_result', 'follow_up', 'discharge'))");
                } catch (\Exception $e) {
                    // If constraint already exists or column type is different, log and continue
                    \Log::warning('Could not modify record_type column: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Rename current table
            DB::statement('ALTER TABLE medical_records RENAME TO medical_records_new');
            
            // Recreate the original table structure
            Schema::create('medical_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
                $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('record_type', ['consultation', 'follow_up', 'emergency', 'routine_checkup', 'procedure']);
                $table->string('diagnosis')->nullable();
                $table->text('symptoms')->nullable();
                $table->text('treatment')->nullable();
                $table->text('notes')->nullable();
                $table->json('vital_signs')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->boolean('is_private')->default(false);
                $table->date('record_date')->nullable();
                $table->text('chief_complaint')->nullable();
                $table->text('present_illness')->nullable();
                $table->text('past_medical_history')->nullable();
                $table->text('physical_examination')->nullable();
                $table->text('assessment')->nullable();
                $table->text('plan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index(['patient_id', 'created_at']);
                $table->index(['doctor_id', 'created_at']);
                $table->index('record_type');
            });
            
            // Copy data back with reverse mapping
            DB::statement("
                INSERT INTO medical_records (
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms, 
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    chief_complaint, present_illness, past_medical_history, physical_examination,
                    assessment, plan, created_by, updated_by, created_at, updated_at
                )
                SELECT 
                    id, patient_id, doctor_id, appointment_id,
                    CASE 
                        WHEN record_type IN ('diagnosis', 'prescription', 'lab_result', 'discharge') THEN 'consultation'
                        ELSE record_type
                    END as record_type,
                    diagnosis, symptoms, treatment, notes, vital_signs, follow_up_date, is_private,
                    record_date, chief_complaint, present_illness, past_medical_history,
                    physical_examination, assessment, plan, created_by, updated_by, created_at, updated_at
                FROM medical_records_new
            ");
            
            // Drop the new table
            Schema::drop('medical_records_new');
        } else {
            // For MySQL, use raw SQL to revert the enum column
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE medical_records MODIFY COLUMN record_type ENUM('consultation', 'follow_up', 'emergency', 'routine_checkup', 'procedure') NOT NULL");
            } else {
                // For PostgreSQL and other databases
                try {
                    DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS medical_records_record_type_check");
                    DB::statement("ALTER TABLE medical_records ALTER COLUMN record_type TYPE VARCHAR(50)");
                    DB::statement("ALTER TABLE medical_records ADD CONSTRAINT medical_records_record_type_check CHECK (record_type IN ('consultation', 'follow_up', 'emergency', 'routine_checkup', 'procedure'))");
                } catch (\Exception $e) {
                    \Log::warning('Could not revert record_type column: ' . $e->getMessage());
                }
            }
        }
    }
};
