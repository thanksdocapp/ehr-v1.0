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
        Schema::table('medical_records', function (Blueprint $table) {
            // Add missing fields that the form uses
            $table->date('record_date')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->text('present_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'record_date',
                'chief_complaint', 
                'present_illness',
                'past_medical_history',
                'physical_examination',
                'assessment',
                'plan',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
