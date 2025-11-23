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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('billing_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('payment_gateway_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            
            // Gateway specific
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_payment_url')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'expired', 'cancelled', 'refunded'])->default('pending');
            $table->string('gateway_status')->nullable();
            
            // Cryptocurrency specific
            $table->string('crypto_currency')->nullable();
            $table->decimal('crypto_amount', 18, 8)->nullable();
            $table->string('crypto_address')->nullable();
            $table->string('transaction_hash')->nullable();
            $table->integer('confirmations')->default(0);
            $table->integer('required_confirmations')->default(1);
            
            // Metadata
            $table->json('gateway_response')->nullable();
            $table->json('webhook_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['gateway_transaction_id']);
            $table->index(['transaction_hash']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
