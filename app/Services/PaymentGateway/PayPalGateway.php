<?php

namespace App\Services\PaymentGateway;

use Exception;
use Illuminate\Support\Facades\Http;

class PayPalGateway extends AbstractPaymentGateway
{
    private string $clientId;
    private string $clientSecret;
    protected bool $testMode = true;
    private ?string $accessToken = null;

    /**
     * Initialize PayPal Gateway
     */
    public function initialize(array $credentials): void
    {
        $this->clientId = $credentials['client_id'] ?? '';
        $this->clientSecret = $credentials['client_secret'] ?? '';
        $this->testMode = $credentials['test_mode'] ?? true;

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception('PayPal client ID and client secret are required');
        }
    }

    /**
     * Get PayPal access token
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
            ])->withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post($this->getBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get PayPal access token: ' . $response->body());
            }

            $data = $response->json();
            $this->accessToken = $data['access_token'];
            
            return $this->accessToken;
        } catch (Exception $e) {
            throw new Exception('PayPal authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a PayPal payment
     */
    public function createPayment(array $paymentData): array
    {
        try {
            // Validate required data
            $this->validatePaymentData($paymentData);

            $amount = number_format((float) $paymentData['amount'], 2, '.', '');
            $currency = $paymentData['currency'] ?? 'USD';
            $description = $paymentData['description'] ?? 'Medical Payment';
            
            \Log::info('PayPal order creation data', [
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'original_data' => $paymentData
            ]);

            $accessToken = $this->getAccessToken();

            // Create order request
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $paymentData['order_id'] ?? uniqid(),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount
                    ],
                    'description' => $description
                ]],
                'application_context' => [
                    'return_url' => $paymentData['success_url'],
                    'cancel_url' => $paymentData['cancel_url'],
                    'brand_name' => config('app.name', 'Hospital Management'),
                    'locale' => 'en-US',
                    'user_action' => 'PAY_NOW'
                ]
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                'Prefer' => 'return=representation'
            ])->post($this->getBaseUrl() . '/v2/checkout/orders', $orderData);

            if (!$response->successful()) {
                throw new Exception('PayPal order creation failed: ' . $response->body());
            }

            $order = $response->json();

            // Get approval URL
            $approvalUrl = null;
            foreach ($order['links'] ?? [] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            return [
                'success' => true,
                'payment_id' => $order['id'],
                'payment_url' => $approvalUrl,
                'gateway_response' => [
                    'order_id' => $order['id'],
                    'status' => $order['status'],
                    'approval_url' => $approvalUrl,
                    'created_time' => $order['create_time'] ?? null,
                ],
            ];

        } catch (Exception $e) {
            \Log::error('PayPal Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'payment_id' => null,
                'payment_url' => null,
            ];
        }
    }

    /**
     * Execute PayPal payment after approval (capture order)
     */
    public function executePayment(string $orderId, string $payerId = null): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                'Prefer' => 'return=representation'
            ])->post($this->getBaseUrl() . "/v2/checkout/orders/{$orderId}/capture");

            if (!$response->successful()) {
                throw new Exception('PayPal order capture failed: ' . $response->body());
            }

            $order = $response->json();
            $capture = $order['purchase_units'][0]['payments']['captures'][0] ?? null;
            
            return [
                'success' => true,
                'payment_id' => $order['id'],
                'transaction_id' => $capture['id'] ?? null,
                'amount' => (float) ($capture['amount']['value'] ?? 0),
                'currency' => $capture['amount']['currency_code'] ?? 'USD',
                'state' => $order['status'],
                'gateway_response' => [
                    'order_id' => $order['id'],
                    'status' => $order['status'],
                    'capture_id' => $capture['id'] ?? null,
                    'payer_id' => $order['payer']['payer_id'] ?? $payerId,
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'PayPal capture error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check payment status
     */
    public function getPaymentStatus(string $orderId): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($this->getBaseUrl() . "/v2/checkout/orders/{$orderId}");

            if (!$response->successful()) {
                throw new Exception('PayPal order status check failed: ' . $response->body());
            }

            $order = $response->json();
            $capture = $order['purchase_units'][0]['payments']['captures'][0] ?? null;

            return [
                'success' => true,
                'payment_id' => $order['id'],
                'state' => $order['status'],
                'amount' => (float) ($capture['amount']['value'] ?? 0),
                'currency' => $capture['amount']['currency_code'] ?? 'USD',
                'created_time' => $order['create_time'] ?? null,
                'updated_time' => $order['update_time'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'PayPal status check error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund
     */
    public function processRefund(string $transactionId, float $amount, array $options = []): array
    {
        try {
            // PayPal refund implementation
            return [
                'success' => false,
                'error' => 'PayPal refunds not implemented yet',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook from PayPal
     */
    public function handleWebhook(array $payload): array
    {
        try {
            // Note: Webhook handling is optional for sandbox testing
            \Log::info('PayPal webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'resource_id' => $payload['resource']['id'] ?? null,
                'payload' => $payload
            ]);
            
            return [
                'success' => true,
                'event_type' => $payload['event_type'] ?? 'unknown',
                'resource_id' => $payload['resource']['id'] ?? null,
            ];
        } catch (Exception $e) {
            \Log::error('PayPal webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate payment data
     */
    private function validatePaymentData(array $data): void
    {
        $required = ['amount', 'success_url', 'cancel_url'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception('Invalid payment amount');
        }

        if (!filter_var($data['success_url'], FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid success URL');
        }

        if (!filter_var($data['cancel_url'], FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid cancel URL');
        }
    }

    /**
     * Get base URL for the gateway API
     */
    protected function getBaseUrl(): string
    {
        return $this->testMode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    /**
     * Get default headers for API requests
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Format payment data for PayPal
     */
    protected function formatPaymentData(array $paymentData): array
    {
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $paymentData['currency'] ?? 'USD',
                    'value' => number_format((float) $paymentData['amount'], 2, '.', '')
                ],
                'description' => $paymentData['description'] ?? 'Medical Payment',
                'reference_id' => $paymentData['order_id'] ?? uniqid()
            ]],
            'application_context' => [
                'return_url' => $paymentData['success_url'],
                'cancel_url' => $paymentData['cancel_url']
            ]
        ];
    }

    /**
     * Parse webhook data from PayPal
     */
    protected function parseWebhookData(array $webhookData): array
    {
        return [
            'event_type' => $webhookData['event_type'] ?? 'unknown',
            'resource_id' => $webhookData['resource']['id'] ?? null,
            'resource_type' => $webhookData['resource_type'] ?? null,
            'summary' => $webhookData['summary'] ?? '',
            'resource' => $webhookData['resource'] ?? [],
        ];
    }

    /**
     * Validate webhook signature
     */
    protected function validateWebhookSignature(array $webhookData): bool
    {
        // For sandbox testing, we can skip signature validation
        if ($this->testMode) {
            return true;
        }
        
        // TODO: Implement actual webhook signature validation for production
        return false;
    }

    /**
     * Process webhook callback
     */
    public function processWebhook(array $webhookData): array
    {
        return $this->handleWebhook($webhookData);
    }

    /**
     * Refund a payment
     */
    public function refundPayment(string $paymentId, float $amount = null): array
    {
        return $this->processRefund($paymentId, $amount ?? 0.0);
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return [
            'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'NOK', 'SEK', 'DKK',
            'PLN', 'CZK', 'HUF', 'ILS', 'MXN', 'BRL', 'MYR', 'PHP', 'TWD', 'THB',
            'SGD', 'HKD', 'RUB', 'INR'
        ];
    }

    /**
     * Test gateway connection
     */
    public function testConnection(): bool
    {
        try {
            // Test the connection by getting an access token
            $this->getAccessToken();
            return true;
        } catch (Exception $e) {
            \Log::error('PayPal connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
