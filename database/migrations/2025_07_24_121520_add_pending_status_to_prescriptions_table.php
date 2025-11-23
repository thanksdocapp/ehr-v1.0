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
        // For SQLite, we need to recreate the table since it doesn't support MODIFY COLUMN
        if (DB::getDriverName() === 'sqlite') {
            // Create a temporary table with the new enum values
            Schema::create('prescriptions_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
                $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
                $table->string('prescription_number')->unique();
                $table->string('medication_name');
                $table->string('dosage');
                $table->string('frequency');
                $table->string('duration');
                $table->text('instructions')->nullable();
                $table->string('status')->default('active'); // Use string for SQLite
                $table->date('prescribed_date');
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['patient_id', 'status']);
                $table->index(['doctor_id', 'prescribed_date']);
                $table->index('prescription_number');
            });
            
            // Copy data from old table to new table
            DB::statement('INSERT INTO prescriptions_temp SELECT * FROM prescriptions');
            
            // Drop the old table
            Schema::drop('prescriptions');
            
            // Rename the temporary table
            Schema::rename('prescriptions_temp', 'prescriptions');
        } else {
            // For MySQL, use ALTER TABLE
            DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active', 'completed', 'cancelled', 'expired', 'pending') DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is complex to reverse, so we'll just leave it as is
        // The 'pending' status will remain available
    }
};
