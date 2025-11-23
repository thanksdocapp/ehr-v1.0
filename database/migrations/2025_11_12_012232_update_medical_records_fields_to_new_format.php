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
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support column renaming, so we need to recreate the table
            DB::statement('ALTER TABLE medical_records RENAME TO medical_records_old');
            
            // Create new table structure
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
                // New fields
                $table->text('presenting_complaint')->nullable(); // PC (renamed from chief_complaint)
                $table->text('history_of_presenting_complaint')->nullable(); // HPC (renamed from present_illness)
                $table->text('past_medical_history')->nullable(); // PMH (kept)
                $table->text('drug_history')->nullable(); // DH (new)
                $table->text('allergies')->nullable(); // Allergies (new)
                $table->text('social_history')->nullable(); // SH (new)
                $table->text('family_history')->nullable(); // FH (new)
                $table->text('ideas_concerns_expectations')->nullable(); // ICE (new)
                $table->text('plan')->nullable(); // Plan (kept, label updated in views)
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index(['patient_id', 'created_at']);
                $table->index(['doctor_id', 'created_at']);
                $table->index('record_type');
            });
            
            // Copy data from old table to new table
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
                    chief_complaint AS presenting_complaint,
                    present_illness AS history_of_presenting_complaint,
                    past_medical_history,
                    NULL AS drug_history,
                    NULL AS allergies,
                    NULL AS social_history,
                    NULL AS family_history,
                    NULL AS ideas_concerns_expectations,
                    plan, created_by, updated_by, created_at, updated_at
                FROM medical_records_old
            ");
            
            // Drop old table
            DB::statement('DROP TABLE medical_records_old');
        } else {
            // MySQL/PostgreSQL - can rename columns directly
            Schema::table('medical_records', function (Blueprint $table) {
                // Rename existing columns
                $table->renameColumn('chief_complaint', 'presenting_complaint');
                $table->renameColumn('present_illness', 'history_of_presenting_complaint');
                
                // Add new columns
                $table->text('drug_history')->nullable()->after('past_medical_history');
                $table->text('allergies')->nullable()->after('drug_history');
                $table->text('social_history')->nullable()->after('allergies');
                $table->text('family_history')->nullable()->after('social_history');
                $table->text('ideas_concerns_expectations')->nullable()->after('family_history');
                
                // Drop old columns
                $table->dropColumn(['physical_examination', 'assessment']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite rollback - recreate old structure
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
            
            DB::statement("
                INSERT INTO medical_records (
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms,
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    chief_complaint, present_illness, past_medical_history,
                    physical_examination, assessment, plan, created_by, updated_by, created_at, updated_at
                )
                SELECT 
                    id, patient_id, doctor_id, appointment_id, record_type, diagnosis, symptoms,
                    treatment, notes, vital_signs, follow_up_date, is_private, record_date,
                    presenting_complaint AS chief_complaint,
                    history_of_presenting_complaint AS present_illness,
                    past_medical_history,
                    NULL AS physical_examination,
                    NULL AS assessment,
                    plan, created_by, updated_by, created_at, updated_at
                FROM medical_records_new
            ");
            
            DB::statement('DROP TABLE medical_records_new');
        } else {
            // MySQL/PostgreSQL rollback
            Schema::table('medical_records', function (Blueprint $table) {
                // Rename back
                $table->renameColumn('presenting_complaint', 'chief_complaint');
                $table->renameColumn('history_of_presenting_complaint', 'present_illness');
                
                // Add back old columns
                $table->text('physical_examination')->nullable();
                $table->text('assessment')->nullable();
                
                // Drop new columns
                $table->dropColumn(['drug_history', 'allergies', 'social_history', 'family_history', 'ideas_concerns_expectations']);
            });
        }
    }
};
