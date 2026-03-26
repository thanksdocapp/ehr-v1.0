<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_settlement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_settlement_id')->constrained('doctor_settlements')->cascadeOnDelete();
            $table->foreignId('billing_id')->nullable()->constrained('billings')->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index('doctor_settlement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_settlement_lines');
    }
};
