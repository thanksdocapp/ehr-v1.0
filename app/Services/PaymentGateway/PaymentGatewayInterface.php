<?php

namespace App\Services\PaymentGateway;

interface PaymentGatewayInterface
{
    /**
     * Initialize the payment gateway with credentials
     */
    public function initialize(array $credentials): void;

    /**
     * Create a payment request
     */
    public function createPayment(array $paymentData): array;

    /**
     * Check payment status
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Process webhook callback
     */
    public function processWebhook(array $webhookData): array;

    /**
     * Refund a payment
     */
    public function refundPayment(string $paymentId, float $amount = null): array;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;

    /**
     * Test gateway connection
     */
    public function testConnection(): bool;

    /**
     * Get gateway name
     */
    public function getGatewayName(): string;

    /**
     * Get gateway display name
     */
    public function getDisplayName(): string;
}
