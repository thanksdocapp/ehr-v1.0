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
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('report_type')->nullable(); // custom, financial, patient, doctor
            $table->json('query_config')->nullable(); // stores the report configuration
            $table->json('filters')->nullable(); // stores applied filters
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_public')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['report_type', 'created_at']);
            $table->index(['created_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
