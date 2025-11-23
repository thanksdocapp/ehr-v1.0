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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // stripe, paypal, paystack, etc.
            $table->string('display_name'); // Display name for frontend
            $table->text('description')->nullable();
            $table->string('provider'); // stripe, paypal, paystack, razorpay
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('credentials'); // Encrypted API keys and secrets
            $table->json('settings')->nullable(); // Additional gateway-specific settings
            $table->json('supported_currencies')->nullable(); // ["USD", "EUR", "NGN"]
            $table->json('supported_countries')->nullable(); // ["US", "NG", "GB"]
            $table->json('supported_methods')->nullable(); // ["card", "bank_transfer", "wallet"]
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('test_mode')->default(true);
            $table->decimal('transaction_fee_percentage', 5, 2)->default(0.00);
            $table->decimal('transaction_fee_fixed', 10, 2)->default(0.00);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['name']);
            $table->index(['is_active', 'is_default']);
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
