<?php

namespace App\Services\PaymentGateway;

use Exception;
use Illuminate\Support\Facades\Log;

class CoinGateGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'coingate';
    protected string $displayName = 'CoinGate';

    protected function getBaseUrl(): string
    {
        return $this->testMode 
            ? 'https://api-sandbox.coingate.com/v2/' 
            : 'https://api.coingate.com/v2/';
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Authorization' => 'Token ' . $this->credentials['api_key'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $formattedData = $this->formatPaymentData($paymentData);
            
            $response = $this->makeRequest('post', 'orders', $formattedData);

            return [
                'success' => true,
                'payment_id' => $response['id'],
                'payment_url' => $response['payment_url'],
                'status' => $response['status'],
                'amount' => $response['price_amount'],
                'currency' => $response['price_currency'],
                'token' => $response['token'] ?? null,
                'expires_at' => $response['expire_at'] ?? null,
                'gateway_response' => $response
            ];

        } catch (Exception $e) {
            Log::error('CoinGate payment creation failed', [
                'error' => $e->getMessage(),
                'data' => $paymentData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway_response' => null
            ];
        }
    }

    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $response = $this->makeRequest('get', "orders/{$paymentId}");

            return [
                'success' => true,
                'payment_id' => $response['id'],
                'status' => $this->mapStatus($response['status']),
                'original_status' => $response['status'],
                'amount' => $response['price_amount'],
                'currency' => $response['price_currency'],
                'paid_amount' => $response['receive_amount'] ?? null,
                'paid_currency' => $response['receive_currency'] ?? null,
                'transaction_id' => $response['token'] ?? null,
                'created_at' => $response['created_at'],
                'updated_at' => $response['updated_at'] ?? null,
                'gateway_response' => $response
            ];

        } catch (Exception $e) {
            Log::error('CoinGate payment status check failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway_response' => null
            ];
        }
    }

    public function processWebhook(array $webhookData): array
    {
        try {
            if (!$this->validateWebhookSignature($webhookData)) {
                throw new Exception('Invalid webhook signature');
            }

            $parsedData = $this->parseWebhookData($webhookData);

            return [
                'success' => true,
                'payment_id' => $parsedData['id'],
                'status' => $this->mapStatus($parsedData['status']),
                'original_status' => $parsedData['status'],
                'amount' => $parsedData['price_amount'],
                'currency' => $parsedData['price_currency'],
                'transaction_data' => $parsedData
            ];

        } catch (Exception $e) {
            Log::error('CoinGate webhook processing failed', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        // CoinGate doesn't support automatic refunds for crypto payments
        // Refunds need to be handled manually
        return [
            'success' => false,
            'error' => 'CoinGate does not support automatic refunds. Please process refunds manually.',
            'requires_manual_refund' => true
        ];
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'BTC', 'ETH', 'LTC', 'BCH', 'XRP', 'ADA', 'DOT', 'USDT', 'USDC',
            'BNB', 'SOL', 'MATIC', 'AVAX', 'LINK', 'UNI', 'USD', 'EUR'
        ];
    }

    public function testConnection(): bool
    {
        try {
            $this->makeRequest('get', 'auth/test');
            return true;
        } catch (Exception $e) {
            Log::error('CoinGate connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function formatPaymentData(array $paymentData): array
    {
        return [
            'order_id' => $paymentData['order_id'] ?? uniqid('hospital_'),
            'price_amount' => $paymentData['amount'],
            'price_currency' => $paymentData['currency'] ?? 'USD',
            'receive_currency' => $paymentData['receive_currency'] ?? 'BTC',
            'title' => $paymentData['title'] ?? 'Hospital Payment',
            'description' => $paymentData['description'] ?? 'Medical services payment',
            'callback_url' => $paymentData['callback_url'] ?? route('payment.webhook.coingate'),
            'cancel_url' => $paymentData['cancel_url'] ?? route('patient.billing.index'),
            'success_url' => $paymentData['success_url'] ?? route('patient.billing.success'),
            'token' => $paymentData['token'] ?? uniqid(),
            'purchaser_email' => $paymentData['customer_email'] ?? null,
        ];
    }

    protected function parseWebhookData(array $webhookData): array
    {
        return $webhookData;
    }

    protected function validateWebhookSignature(array $webhookData): bool
    {
        // Implement webhook signature validation
        // CoinGate uses HMAC-SHA256 for webhook validation
        $receivedSignature = $_SERVER['HTTP_X_COINGATE_SIGNATURE'] ?? '';
        $payload = file_get_contents('php://input');
        $webhookSecret = $this->credentials['webhook_secret'] ?? '';

        if (empty($webhookSecret) || empty($receivedSignature)) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($calculatedSignature, $receivedSignature);
    }

    private function mapStatus(string $status): string
    {
        $statusMap = [
            'new' => 'pending',
            'pending' => 'pending',
            'confirming' => 'processing',
            'paid' => 'completed',
            'invalid' => 'failed',
            'expired' => 'expired',
            'canceled' => 'cancelled',
            'refunded' => 'refunded'
        ];

        return $statusMap[$status] ?? 'unknown';
    }
}
