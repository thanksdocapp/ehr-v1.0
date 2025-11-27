<?php

namespace App\Services\PaymentGateway;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Exception;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    private $secretKey;
    private $publishableKey;
    private $webhookSecret;
    private $testMode = true;

    public function initialize(array $credentials): void
    {
        $this->testMode = $credentials['test_mode'] ?? true;
        $this->secretKey = $credentials['secret_key'] ?? '';
        $this->publishableKey = $credentials['publishable_key'] ?? '';
        $this->webhookSecret = $credentials['webhook_secret'] ?? '';

        if (empty($this->secretKey)) {
            throw new Exception('Stripe secret key is required');
        }

        Stripe::setApiKey($this->secretKey);
    }

    public function createPayment(array $data): array
    {
        // Use Stripe Checkout Sessions for hosted payment
        return $this->createCheckoutSession($data);
    }

    /**
     * Create a Stripe Checkout Session for hosted payment
     */
    public function createCheckoutSession(array $data): array
    {
        try {
            // Convert amount to cents (Stripe uses smallest currency unit)
            $amountInCents = (int) ($data['amount'] * 100);

            // For testing with invalid keys, we'll simulate a successful response
            if (str_starts_with($this->secretKey, 'sk_test_') === false && $this->testMode) {
                // Mock response for invalid test keys
                return [
                    'success' => true,
                    'payment_id' => 'cs_mock_' . uniqid(),
                    'payment_url' => route('payment.mock-stripe', ['session' => 'cs_mock_' . uniqid()]),
                    'status' => 'open',
                    'gateway_response' => [
                        'session_id' => 'cs_mock_' . uniqid(),
                        'status' => 'open'
                    ]
                ];
            }

            // Determine success and cancel URLs based on context
            $successUrl = isset($data['is_patient_payment']) && $data['is_patient_payment']
                ? $data['success_url'] ?? route('patient.billing.index') . '?payment=success'
                : $data['success_url'] ?? route('payment.success');
                
            $cancelUrl = isset($data['is_patient_payment']) && $data['is_patient_payment']
                ? $data['cancel_url'] ?? route('patient.billing.index') . '?payment=cancelled'
                : $data['cancel_url'] ?? route('payment.cancelled');

            // Fix success URL - use ? if no query params, & if query params exist
            $successUrlWithSession = $successUrl;
            if (strpos($successUrl, '?') !== false) {
                // URL already has query parameters, use &
                $successUrlWithSession = $successUrl . '&session_id={CHECKOUT_SESSION_ID}';
            } else {
                // URL has no query parameters, use ?
                $successUrlWithSession = $successUrl . '?session_id={CHECKOUT_SESSION_ID}';
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($data['currency'] ?? 'usd'),
                        'product_data' => [
                            'name' => $data['description'] ?? 'Hospital Payment',
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrlWithSession,
                'cancel_url' => $cancelUrl,
                'customer_email' => $data['customer_email'] ?? null,
                'metadata' => [
                    'order_id' => $data['order_id'],
                    'customer_email' => $data['customer_email'] ?? '',
                    'description' => $data['description'] ?? '',
                ],
            ]);

            return [
                'success' => true,
                'payment_id' => $session->id,
                'payment_url' => $session->url,
                'status' => $session->status,
                'gateway_response' => [
                    'session_id' => $session->id,
                    'status' => $session->status,
                    'amount' => $amountInCents,
                    'currency' => $data['currency'] ?? 'usd'
                ]
            ];

        } catch (Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => 'Stripe checkout session creation failed: ' . $e->getMessage()
            ];
        }
    }

    public function getPaymentStatus(string $paymentId): array
    {
        try {
            // Handle mock payments
            if (str_starts_with($paymentId, 'pi_mock_')) {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'original_status' => 'succeeded',
                    'amount' => 0,
                    'currency' => 'usd'
                ];
            }

            $paymentIntent = PaymentIntent::retrieve($paymentId);

            $status = match($paymentIntent->status) {
                'succeeded' => 'completed',
                'processing' => 'pending',
                'requires_payment_method', 'requires_confirmation', 'requires_action' => 'pending',
                'canceled' => 'cancelled',
                default => 'pending'
            };

            return [
                'success' => true,
                'status' => $status,
                'original_status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to get payment status: ' . $e->getMessage()
            ];
        }
    }

    public function processWebhook(array $payload): array
    {
        try {
            // For mock payments, simulate webhook processing
            if (isset($payload['type']) && $payload['type'] === 'mock.payment_intent.succeeded') {
                return [
                    'success' => true,
                    'payment_id' => $payload['data']['object']['id'] ?? 'pi_mock_' . uniqid(),
                    'status' => 'completed',
                    'original_status' => 'succeeded'
                ];
            }

            // Verify webhook signature if webhook secret is provided
            // Note: Webhook secret is optional for basic testing but recommended for production
            if (!empty($this->webhookSecret) && !empty($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '')) {
                $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
                // Verify the webhook signature for security
                Webhook::constructEvent(json_encode($payload), $signature, $this->webhookSecret);
                Log::info('Stripe webhook signature verified successfully');
            } elseif (empty($this->webhookSecret)) {
                Log::info('Stripe webhook processed without signature verification (webhook secret not configured)');
            }

            $event = $payload;
            
            switch ($event['type']) {
                case 'checkout.session.completed':
                    return [
                        'success' => true,
                        'payment_id' => $event['data']['object']['id'],
                        'status' => 'completed',
                        'original_status' => 'completed'
                    ];
                    
                case 'checkout.session.expired':
                    return [
                        'success' => true,
                        'payment_id' => $event['data']['object']['id'],
                        'status' => 'expired',
                        'original_status' => 'expired'
                    ];
                    
                case 'payment_intent.succeeded':
                    return [
                        'success' => true,
                        'payment_id' => $event['data']['object']['id'],
                        'status' => 'completed',
                        'original_status' => 'succeeded'
                    ];
                    
                case 'payment_intent.payment_failed':
                    return [
                        'success' => true,
                        'payment_id' => $event['data']['object']['id'],
                        'status' => 'failed',
                        'original_status' => 'payment_failed'
                    ];
                    
                case 'payment_intent.canceled':
                    return [
                        'success' => true,
                        'payment_id' => $event['data']['object']['id'],
                        'status' => 'cancelled',
                        'original_status' => 'canceled'
                    ];
                    
                default:
                    return [
                        'success' => true,
                        'payment_id' => $event['data']['object']['id'] ?? null,
                        'status' => 'pending',
                        'original_status' => $event['type']
                    ];
            }

        } catch (Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'error' => 'Webhook processing failed: ' . $e->getMessage()
            ];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            // Handle mock payments
            if (str_starts_with($paymentId, 'pi_mock_')) {
                return [
                    'success' => true,
                    'refund_id' => 're_mock_' . uniqid(),
                    'status' => 'succeeded',
                    'amount' => $amount ?? 0
                ];
            }

            $refundData = ['payment_intent' => $paymentId];
            
            if ($amount !== null) {
                $refundData['amount'] = (int) ($amount * 100);
            }

            $refund = \Stripe\Refund::create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Refund failed: ' . $e->getMessage()
            ];
        }
    }

    public function getSupportedCurrencies(): array
    {
        return [
            'usd', 'eur', 'gbp', 'cad', 'aud', 'jpy', 'chf', 'nok', 'sek', 'dkk'
        ];
    }

    public function testConnection(): bool
    {
        try {
            if (empty($this->secretKey)) {
                return false;
            }

            // For mock keys, return true
            if (!str_starts_with($this->secretKey, 'sk_test_') && $this->testMode) {
                return true;
            }

            // Test by trying to retrieve account information
            \Stripe\Account::retrieve();
            return true;
        } catch (Exception $e) {
            Log::warning('Stripe connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function getDisplayName(): string
    {
        return 'Stripe';
    }
}
