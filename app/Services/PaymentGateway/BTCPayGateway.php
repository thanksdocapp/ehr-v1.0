<?php

namespace App\Services\PaymentGateway;

use Exception;
use Illuminate\Support\Facades\Log;

class BTCPayGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'btcpay';
    protected string $displayName = 'BTCPay Server';

    protected function getBaseUrl(): string
    {
        $serverUrl = rtrim($this->credentials['server_url'] ?? '', '/');
        if (empty($serverUrl)) {
            throw new Exception('BTCPay Server URL is required');
        }
        return $serverUrl . '/api/v1/';
    }

    protected function getDefaultHeaders(): array
    {
        $apiKey = $this->credentials['api_key'] ?? '';
        if (empty($apiKey)) {
            throw new Exception('BTCPay API key is required');
        }
        
        return [
            'Authorization' => 'token ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $storeId = $this->credentials['store_id'] ?? '';
            if (empty($storeId)) {
                throw new Exception('BTCPay Store ID is required');
            }
            
            $formattedData = $this->formatPaymentData($paymentData);

            $response = $this->makeRequest('post', "stores/{$storeId}/invoices", $formattedData);

            return [
                'success' => true,
                'payment_id' => $response['id'],
                'payment_url' => $response['checkoutLink'],
                'status' => $response['status'],
                'amount' => $response['amount'],
                'currency' => $response['currency'],
                'expires_at' => $response['expirationTime'],
                'gateway_response' => $response
            ];

        } catch (Exception $e) {
            Log::error('BTCPay payment creation failed', [
                'error' => $e->getMessage(),
                'data' => $paymentData,
                'credentials' => array_keys($this->credentials)
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
            $response = $this->makeRequest('get', "invoices/{$paymentId}");

            return [
                'success' => true,
                'payment_id' => $response['id'],
                'status' => $this->mapStatus($response['status']),
                'original_status' => $response['status'],
                'amount' => $response['price'],
                'currency' => $response['currency'],
                'paid_amount' => $response['received'],
                'paid_currency' => $response['currencyPaid'] ?? null,
                'created_at' => $response['createdTime'],
                'expires_at' => $response['expirationTime'],
                'gateway_response' => $response
            ];

        } catch (Exception $e) {
            Log::error('BTCPay payment status check failed', [
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
                'amount' => $parsedData['price'],
                'currency' => $parsedData['currency'],
                'transaction_data' => $parsedData
            ];

        } catch (Exception $e) {
            Log::error('BTCPay webhook processing failed', [
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
        // BTCPay does not support automatic refunds
        return [
            'success' => false,
            'error' => 'BTCPay does not support automatic refunds. Please process refunds manually.',
            'requires_manual_refund' => true
        ];
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'BTC', 'LTC', 'ETH', 'BCH', 'XMR', 'DASH', 'ZEC', 'DOGE', 'USDT', 'USDC'
        ];
    }

    public function testConnection(): bool
    {
        try {
            // First validate that we have required credentials
            $apiKey = $this->credentials['api_key'] ?? '';
            $serverUrl = $this->credentials['server_url'] ?? '';
            $storeId = $this->credentials['store_id'] ?? '';
            
            if (empty($apiKey)) {
                throw new Exception('BTCPay API key is required');
            }
            if (empty($serverUrl)) {
                throw new Exception('BTCPay Server URL is required');
            }
            if (empty($storeId)) {
                throw new Exception('BTCPay Store ID is required');
            }
            
            // Test connection by fetching store information
            $response = $this->makeRequest('get', "stores/{$storeId}");
            return true;
        } catch (Exception $e) {
            Log::error('BTCPay connection test failed', ['error' => $e->getMessage()]);
            
            // Check for permission errors and provide helpful messages
            if (strpos($e->getMessage(), 'missing-permission') !== false) {
                throw new Exception('BTCPay API key lacks required permissions. Please create a new API key with permissions: btcpay.store.canviewstoresettings, btcpay.store.cancreatenonapprovedpullpayments, btcpay.store.canmodifyinvoices');
            }
            
            throw $e;
        }
    }

    protected function formatPaymentData(array $paymentData): array
    {
        return [
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'USD',
            'orderId' => $paymentData['order_id'] ?? uniqid('hospital_'),
            'buyerEmail' => $paymentData['customer_email'] ?? null,
            'notificationURL' => $paymentData['callback_url'] ?? route('payment.webhook', ['provider' => 'btcpay']),
            'redirectURL' => $paymentData['success_url'] ?? route('payment.success'),
            'status' => 'new',
        ];
    }

    protected function parseWebhookData(array $webhookData): array
    {
        return $webhookData;
    }

    protected function validateWebhookSignature(array $webhookData): bool
    {
        // Validate webhook with HMAC-SHA256
        $webhookSecret = $this->credentials['webhook_secret'] ?? '';
        $payload = file_get_contents('php://input');
        $receivedSignature = $_SERVER['HTTP_BTCPAY_SIGNATURE'] ?? '';

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
            'paid' => 'completed',
            'expired' => 'expired',
            'invalid' => 'failed',
            'complete' => 'completed'
        ];

        return $statusMap[$status] ?? 'unknown';
    }
}

