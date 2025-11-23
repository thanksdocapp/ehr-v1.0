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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->timestamp('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['credit_card', 'debit_card', 'bank_transfer', 'cash', 'insurance', 'online']);
            $table->string('transaction_id')->unique()->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->json('gateway_response')->nullable(); // Payment gateway response
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['invoice_id', 'status']);
            $table->index(['payment_date', 'status']);
            $table->index('transaction_id');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
