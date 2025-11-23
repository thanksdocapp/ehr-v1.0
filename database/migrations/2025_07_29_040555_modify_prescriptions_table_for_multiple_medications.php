<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop individual medication fields one by one for SQLite compatibility
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('medication_name');
        });
        
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('dosage');
        });
        
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });
        
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
        
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('instructions');
        });
        
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        // Add new fields
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->json('medications')->after('prescription_number'); // Store medications as JSON
            $table->enum('status', ['pending', 'approved', 'dispensed', 'cancelled', 'expired'])
                  ->default('pending')->after('medications');
            $table->string('prescription_type')->after('status'); // new, refill, modification, emergency
            $table->date('prescription_date')->after('prescription_type');
            $table->text('diagnosis')->nullable()->after('prescription_date');
            $table->date('follow_up_date')->nullable()->after('notes');
            $table->integer('refills_allowed')->default(0)->after('follow_up_date');
            $table->text('pharmacist_notes')->nullable()->after('refills_allowed');
            $table->unsignedBigInteger('pharmacist_id')->nullable()->after('pharmacist_notes');
            $table->unsignedBigInteger('created_by')->nullable()->after('pharmacist_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->timestamp('dispensed_at')->nullable()->after('updated_by');
        });
        
        // Add foreign key constraints for new fields
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->foreign('pharmacist_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['pharmacist_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Drop new columns
            $table->dropColumn([
                'medications',
                'prescription_type', 
                'prescription_date',
                'diagnosis',
                'follow_up_date',
                'refills_allowed',
                'pharmacist_notes',
                'pharmacist_id',
                'created_by',
                'updated_by',
                'dispensed_at',
                'status'
            ]);
            
            // Restore original columns
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->string('duration');
            $table->text('instructions')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired', 'pending'])->default('active');
        });
    }
};
