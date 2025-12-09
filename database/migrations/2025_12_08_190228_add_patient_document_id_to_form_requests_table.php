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
        Schema::table('form_requests', function (Blueprint $table) {
            // Make generated_document_id nullable (for forms from GeneratedDocument)
            $table->foreignId('generated_document_id')->nullable()->change();

            // Add patient_document_id for forms from PatientDocument
            $table->foreignId('patient_document_id')->nullable()->after('generated_document_id')
                ->constrained('patient_documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropForeign(['patient_document_id']);
            $table->dropColumn('patient_document_id');
        });
    }
};
