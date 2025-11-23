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
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('file_path');
            $table->string('export_type'); // pdf, excel, csv
            $table->string('report_type'); // custom, financial, patient, doctor
            $table->unsignedBigInteger('report_id')->nullable();
            $table->unsignedBigInteger('exported_by');
            $table->integer('file_size')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('report_id')->references('id')->on('saved_reports')->onDelete('cascade');
            $table->foreign('exported_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'created_at']);
            $table->index(['exported_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
