<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FlutterwaveGateway extends AbstractPaymentGateway
{
    protected string $gatewayName = 'flutterwave';
    protected string $displayName = 'Flutterwave';

    protected function getBaseUrl(): string
    {
        return $this->testMode ? 'https://api.flutterwave.com/v3' : 'https://api.flutterwave.com/v3';
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
            $response = $this->makeRequest('post', '/payments', $formattedData);

            if ($response['status'] !== 'success') {
                throw new Exception($response['message'] ?? 'Payment initialization failed');
            }

            // Use the tx_ref from our original data since Flutterwave doesn't return it
            return [
                'success' => true,
                'payment_id' => $formattedData['tx_ref'], // Use our tx_ref since it's not in response
                'payment_url' => $response['data']['link'],
                'status' => 'pending',
                'gateway_response' => $response,
                'expires_at' => now()->addHours(1),
            ];

        } catch (Exception $e) {
            Log::error('Flutterwave payment creation failed', [
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
            $response = $this->makeRequest('get', "/transactions/{$paymentId}/verify");

            if ($response['status'] !== 'success') {
                throw new Exception($response['message'] ?? 'Payment verification failed');
            }

            $status = $this->mapFlutterwaveStatus($response['data']['status']);

            return [
                'success' => true,
                'status' => $status,
                'original_status' => $response['data']['status'],
                'amount' => $response['data']['amount'],
                'currency' => $response['data']['currency'],
                'gateway_response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('Flutterwave payment status check failed', [
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
            $status = $this->mapFlutterwaveStatus($parsedData['status']);

            return [
                'success' => true,
                'payment_id' => $parsedData['tx_ref'],
                'status' => $status,
                'original_status' => $parsedData['status'],
                'amount' => $parsedData['amount'],
                'currency' => $parsedData['currency'],
                'gateway_response' => $webhookData,
            ];

        } catch (Exception $e) {
            Log::error('Flutterwave webhook processing failed', [
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
            $data = [];
            
            if ($amount !== null) {
                $data['amount'] = $amount;
            }

            $response = $this->makeRequest('post', "/transactions/{$paymentId}/refund", $data);

            if ($response['status'] !== 'success') {
                throw new Exception($response['message'] ?? 'Refund failed');
            }

            return [
                'success' => true,
                'refund_id' => $response['data']['id'],
                'amount' => $response['data']['amount'],
                'status' => 'refunded',
                'gateway_response' => $response,
            ];

        } catch (Exception $e) {
            Log::error('Flutterwave refund failed', [
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
            'NGN', // Nigerian Naira
            'GHS', // Ghanaian Cedi
            'KES', // Kenyan Shilling
            'UGX', // Ugandan Shilling
            'TZS', // Tanzanian Shilling
            'ZAR', // South African Rand
            'USD', // US Dollar
            'EUR', // Euro
            'GBP', // British Pound
            'XAF', // Central African CFA Franc
            'XOF', // West African CFA Franc
            'RWF', // Rwandan Franc
            'ZMW', // Zambian Kwacha
        ];
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('get', '/banks/NG');
            return isset($response['status']) && $response['status'] === 'success';
        } catch (Exception $e) {
            Log::error('Flutterwave connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function formatPaymentData(array $paymentData): array
    {
        // Log the incoming payment data to debug currency issues
        Log::info('FlutterwaveGateway formatPaymentData', [
            'incoming_currency' => $paymentData['currency'] ?? 'NOT_SET',
            'full_payment_data' => $paymentData
        ]);

        return [
            'tx_ref' => $paymentData['order_id'],
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'GHS',
            'redirect_url' => route('payment.flutterwave.callback'),
            'payment_options' => 'card,mobilemoney,ussd,banktransfer',
            'customer' => [
                'email' => $paymentData['customer_email'],
                'name' => $paymentData['customer_name'] ?? 'Customer',
            ],
            'customizations' => [
                'title' => $paymentData['title'] ?? 'Hospital Payment',
                'description' => $paymentData['description'] ?? 'Payment for medical services',
                'logo' => asset('images/logos/flutterwave-logo.png'),
            ],
            'meta' => [
                'order_id' => $paymentData['order_id'],
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
            'tx_ref' => $data['tx_ref'] ?? '',
            'flw_ref' => $data['flw_ref'] ?? '',
            'status' => $data['status'] ?? '',
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? 'NGN',
            'customer_email' => $data['customer']['email'] ?? '',
            'customer_name' => $data['customer']['name'] ?? '',
            'payment_type' => $data['payment_type'] ?? '',
            'created_at' => $data['created_at'] ?? null,
        ];
    }

    protected function validateWebhookSignature(array $webhookData): bool
    {
        // Get the signature from headers
        $signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';
        
        if (empty($signature)) {
            return false;
        }

        // Get the webhook secret hash from credentials
        $secretHash = $this->credentials['webhook_secret'] ?? '';
        
        if (empty($secretHash)) {
            Log::warning('Flutterwave webhook secret not configured');
            return false;
        }

        // Compare signatures
        return hash_equals($secretHash, $signature);
    }

    private function mapFlutterwaveStatus(string $flutterwaveStatus): string
    {
        $statusMap = [
            'successful' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            'pending' => 'pending',
        ];

        return $statusMap[$flutterwaveStatus] ?? 'pending';
    }
}
