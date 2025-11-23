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
        Schema::table('patients', function (Blueprint $table) {
            // ID Documents
            $table->string('patient_id_document_path')->nullable()->after('photo');
            $table->string('guardian_id_document_path')->nullable()->after('patient_id_document_path');
            
            // GP Consent and Details
            $table->boolean('consent_share_with_gp')->default(false)->after('guardian_id_document_path');
            $table->string('gp_name')->nullable()->after('consent_share_with_gp');
            $table->string('gp_email')->nullable()->after('gp_name');
            $table->string('gp_phone')->nullable()->after('gp_email');
            $table->text('gp_address')->nullable()->after('gp_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'patient_id_document_path',
                'guardian_id_document_path',
                'consent_share_with_gp',
                'gp_name',
                'gp_email',
                'gp_phone',
                'gp_address'
            ]);
        });
    }
};
