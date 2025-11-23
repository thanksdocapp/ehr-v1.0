<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaystackGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'paystack';
    protected string $displayName = 'Paystack';

    protected function getBaseUrl(): string
    {
        return 'https://api.paystack.co';
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $formattedData = $this->formatPaymentData($paymentData);
            $response = $this->makeRequest('post', '/transaction/initialize', $formattedData);

            if (!$response['status']) {
                throw new Exception($response['message'] ?? 'Payment initialization failed');
            }

            return [
                'success' => true,
                'payment_id' => $response['data']['reference'],
                'payment_url' => $response['data']['authorization_url'],
                'status' => 'pending',
                'gateway_response' => $response,
                'expires_at' => now()->addHours(1),
            ];

        } catch (Exception $e) {
            Log::error('Paystack payment creation failed', [
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
            $response = $this->makeRequest('get', "/transaction/verify/{$paymentId}");

            if (!$response['status']) {
                throw new Exception($response['message'] ?? 'Payment verification failed');
            }

            $status = $this->mapPaystackStatus($response['data']['status']);

            return [
                'success' => true,
                'status' => $status,
                'original_status' => $response['data']['status'],
                'amount' => $response['data']['amount'] / 100, // Convert from kobo to main currency
                'currency' => $response['data']['currency'],
                'gateway_response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('Paystack payment status check failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processWebhook(array $webhookData): array
    {
        try {
            // Verify webhook signature
            if (!$this->validateWebhookSignature($webhookData)) {
                throw new Exception('Invalid webhook signature');
            }

            $parsedData = $this->parseWebhookData($webhookData);
            $status = $this->mapPaystackStatus($parsedData['status']);

            return [
                'success' => true,
                'payment_id' => $parsedData['reference'],
                'status' => $status,
                'original_status' => $parsedData['status'],
                'amount' => $parsedData['amount'],
                'currency' => $parsedData['currency'],
                'gateway_response' => $webhookData,
            ];

        } catch (Exception $e) {
            Log::error('Paystack webhook processing failed', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            $data = ['transaction' => $paymentId];
            
            if ($amount !== null) {
                $data['amount'] = $amount * 100; // Convert to kobo
            }

            $response = $this->makeRequest('post', '/refund', $data);

            if (!$response['status']) {
                throw new Exception($response['message'] ?? 'Refund failed');
            }

            return [
                'success' => true,
                'refund_id' => $response['data']['id'],
                'amount' => $response['data']['amount'] / 100,
                'status' => 'refunded',
                'gateway_response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('Paystack refund failed', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'NGN', // Nigerian Naira (primary)
            'GHS', // Ghanaian Cedi
            'ZAR', // South African Rand
            'KES', // Kenyan Shilling
            'USD', // US Dollar (limited support)
        ];
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('get', '/bank');
            return isset($response['status']) && $response['status'] === true;
        } catch (Exception $e) {
            Log::error('Paystack connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function formatPaymentData(array $paymentData): array
    {
        // Log the incoming payment data to debug currency issues
        \Log::info('PaystackGateway formatPaymentData', [
            'incoming_currency' => $paymentData['currency'] ?? 'NOT_SET',
            'full_payment_data' => $paymentData
        ]);
        
        // Convert amount to kobo (smallest currency unit for NGN)
        $amount = $paymentData['amount'];
        if (in_array($paymentData['currency'], ['NGN', 'GHS', 'KES'])) {
            $amount = $amount * 100;
        }

        return [
            'email' => $paymentData['customer_email'],
            'amount' => $amount,
            'currency' => $paymentData['currency'] ?? 'GHS',  // Changed default from NGN to GHS
            'reference' => $paymentData['order_id'],
            'callback_url' => route('payment.paystack.callback'), // Use our callback route instead
            'metadata' => [
                'order_id' => $paymentData['order_id'],
                'description' => $paymentData['description'],
                'title' => $paymentData['title'],
                'success_url' => $paymentData['success_url'],
                'cancel_url' => $paymentData['cancel_url'],
            ],
        ];
    }

    protected function parseWebhookData(array $webhookData): array
    {
        $event = $webhookData['event'] ?? '';
        $data = $webhookData['data'] ?? [];

        return [
            'event' => $event,
            'reference' => $data['reference'] ?? '',
            'status' => $data['status'] ?? '',
            'amount' => ($data['amount'] ?? 0) / 100, // Convert from kobo
            'currency' => $data['currency'] ?? 'NGN',
            'customer_email' => $data['customer']['email'] ?? '',
            'gateway_response' => $data['gateway_response'] ?? '',
            'paid_at' => $data['paid_at'] ?? null,
        ];
    }

    protected function validateWebhookSignature(array $webhookData): bool
    {
        // Get the signature from headers
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
        
        if (empty($signature)) {
            return false;
        }

        // Get the raw POST body
        $payload = file_get_contents('php://input');
        
        // Calculate expected signature
        $expectedSignature = hash_hmac('sha512', $payload, $this->credentials['secret_key']);
        
        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }

    private function mapPaystackStatus(string $paystackStatus): string
    {
        $statusMap = [
            'success' => 'completed',
            'failed' => 'failed',
            'abandoned' => 'cancelled',
            'pending' => 'pending',
            'ongoing' => 'pending',
            'processing' => 'pending',
        ];

        return $statusMap[$paystackStatus] ?? 'pending';
    }
}
