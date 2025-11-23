<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentGatewayController extends Controller
{
    /**
     * Display a listing of payment gateways.
     */
    public function index()
    {
        $gateways = PaymentGateway::orderBySort()->get();
        $availableProviders = PaymentGateway::getAvailableProviders();
        
        return view('admin.payment-gateways.index', compact('gateways', 'availableProviders'));
    }

    /**
     * Show the form for creating a new payment gateway.
     */
    public function create()
    {
        $availableProviders = PaymentGateway::getAvailableProviders();
        $existingProviders = PaymentGateway::pluck('provider')->toArray();
        
        // Filter out already configured providers
        $availableProviders = array_filter($availableProviders, function($key) use ($existingProviders) {
            return !in_array($key, $existingProviders);
        }, ARRAY_FILTER_USE_KEY);
        
        // Generate webhook URLs for all available providers
        $allWebhookUrls = [];
        foreach (array_keys($availableProviders) as $provider) {
            $allWebhookUrls[$provider] = $this->generateWebhookUrls($provider);
        }
        
        return view('admin.payment-gateways.create', compact('availableProviders', 'allWebhookUrls'));
    }

    /**
     * Store a newly created payment gateway.
     */
    public function store(Request $request)
    {
        $availableProviders = PaymentGateway::getAvailableProviders();
        
        $validationRules = [
            'provider' => ['required', 'string', Rule::in(array_keys($availableProviders)), 'unique:payment_gateways,provider'],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'test_mode' => 'boolean',
            'transaction_fee_percentage' => 'numeric|min:0|max:100',
            'transaction_fee_fixed' => 'numeric|min:0',
            'sort_order' => 'integer|min:0',
            'credentials' => 'required|array',
            'settings' => 'nullable|array',
        ];

        // Add provider-specific credential validation
        $this->addProviderCredentialRules($validationRules, $request->provider);

        $request->validate($validationRules);

        DB::transaction(function () use ($request, $availableProviders) {
            // If this is set as default, remove default from others
            if ($request->boolean('is_default')) {
                PaymentGateway::where('is_default', true)->update(['is_default' => false]);
            }

            $providerConfig = $availableProviders[$request->provider];
            
            PaymentGateway::create([
                'name' => $request->provider,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'provider' => $request->provider,
                'is_active' => $request->boolean('is_active'),
                'is_default' => $request->boolean('is_default'),
                'credentials' => $request->credentials,
                'settings' => $request->settings ?? [],
                'supported_currencies' => $providerConfig['currencies'],
                'supported_countries' => $providerConfig['countries'],
                'supported_methods' => $providerConfig['methods'],
                'test_mode' => $request->boolean('test_mode'),
                'transaction_fee_percentage' => $request->transaction_fee_percentage ?? 0,
                'transaction_fee_fixed' => $request->transaction_fee_fixed ?? 0,
                'sort_order' => $request->sort_order ?? 0,
            ]);
        });

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway configured successfully!');
    }

    /**
     * Display the specified payment gateway.
     */
    public function show(PaymentGateway $paymentGateway)
    {
        return view('admin.payment-gateways.show', compact('paymentGateway'));
    }

    /**
     * Show the form for editing the specified payment gateway.
     */
    public function edit(PaymentGateway $paymentGateway)
    {
        $availableProviders = PaymentGateway::getAvailableProviders();
        $providerConfig = $availableProviders[$paymentGateway->provider] ?? null;
        
        // Generate webhook/callback URLs for this gateway
        $webhookUrls = $this->generateWebhookUrls($paymentGateway->provider);
        
        return view('admin.payment-gateways.edit', compact('paymentGateway', 'providerConfig', 'webhookUrls'));
    }

    /**
     * Update the specified payment gateway.
     */
    public function update(Request $request, PaymentGateway $paymentGateway)
    {
        $validationRules = [
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'test_mode' => 'boolean',
            'transaction_fee_percentage' => 'numeric|min:0|max:100',
            'transaction_fee_fixed' => 'numeric|min:0',
            'sort_order' => 'integer|min:0',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
        ];

        // Add provider-specific credential validation for updates
        if ($request->filled('credentials')) {
            $this->addProviderCredentialRules($validationRules, $paymentGateway->provider, false);
        }

        $request->validate($validationRules);

        DB::transaction(function () use ($request, $paymentGateway) {
            // If this is set as default, remove default from others
            if ($request->boolean('is_default') && !$paymentGateway->is_default) {
                PaymentGateway::where('is_default', true)->update(['is_default' => false]);
            }

            $updateData = [
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'is_default' => $request->boolean('is_default'),
                'test_mode' => $request->boolean('test_mode'),
                'transaction_fee_percentage' => $request->transaction_fee_percentage ?? 0,
                'transaction_fee_fixed' => $request->transaction_fee_fixed ?? 0,
                'sort_order' => $request->sort_order ?? 0,
            ];

            // Only update credentials if provided
            if ($request->filled('credentials')) {
                $updateData['credentials'] = array_merge(
                    $paymentGateway->credentials ?? [],
                    array_filter($request->credentials ?? [])
                );
            }

            // Only update settings if provided
            if ($request->has('settings')) {
                $updateData['settings'] = array_merge(
                    $paymentGateway->settings ?? [],
                    $request->settings ?? []
                );
            }

            $paymentGateway->update($updateData);
        });

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway updated successfully!');
    }

    /**
     * Remove the specified payment gateway.
     */
    public function destroy(PaymentGateway $paymentGateway)
    {
        if ($paymentGateway->is_default) {
            return redirect()->route('admin.payment-gateways.index')
                ->with('error', 'Cannot delete the default payment gateway. Please set another gateway as default first.');
        }

        $paymentGateway->delete();

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway deleted successfully!');
    }

    /**
     * Toggle gateway status.
     */
    public function toggleStatus(PaymentGateway $paymentGateway)
    {
        $paymentGateway->update(['is_active' => !$paymentGateway->is_active]);

        $status = $paymentGateway->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.payment-gateways.index')
            ->with('success', "Payment gateway {$status} successfully!");
    }

    /**
     * Set gateway as default.
     */
    public function setDefault(PaymentGateway $paymentGateway)
    {
        if (!$paymentGateway->is_active) {
            return redirect()->route('admin.payment-gateways.index')
                ->with('error', 'Cannot set inactive gateway as default. Please activate it first.');
        }

        DB::transaction(function () use ($paymentGateway) {
            PaymentGateway::where('is_default', true)->update(['is_default' => false]);
            $paymentGateway->update(['is_default' => true]);
        });

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Default payment gateway updated successfully!');
    }

    /**
     * Test gateway connection.
     */
    public function testConnection(PaymentGateway $paymentGateway)
    {
        try {
            // Create gateway instance and test actual connection
            $gateway = \App\Services\PaymentGateway\PaymentGatewayFactory::createFromModel($paymentGateway);
            $connectionTest = $gateway->testConnection();
            
            if ($connectionTest) {
                $testResult = [
                    'success' => true,
                    'message' => 'Connection test successful!',
                    'details' => [
                        'gateway' => $paymentGateway->display_name,
                        'provider' => $paymentGateway->provider,
                        'mode' => $paymentGateway->test_mode ? 'Test' : 'Live',
                        'status' => 'Connected',
                        'supported_currencies' => $paymentGateway->supported_currencies
                    ]
                ];
            } else {
                throw new \Exception('Gateway connection test failed');
            }

            return response()->json($testResult);
        } catch (\Exception $e) {
            \Log::error('Payment gateway connection test failed', [
                'gateway' => $paymentGateway->provider,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'details' => [
                    'gateway' => $paymentGateway->display_name,
                    'provider' => $paymentGateway->provider,
                    'mode' => $paymentGateway->test_mode ? 'Test' : 'Live',
                    'status' => 'Failed'
                ]
            ], 400);
        }
    }

    /**
     * Update gateway sort order.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'gateways' => 'required|array',
            'gateways.*.id' => 'required|exists:payment_gateways,id',
            'gateways.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->gateways as $gatewayData) {
                PaymentGateway::where('id', $gatewayData['id'])
                    ->update(['sort_order' => $gatewayData['sort_order']]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Gateway order updated successfully!']);
    }

    /**
     * Add provider-specific credential validation rules.
     */
    private function addProviderCredentialRules(&$rules, $provider, $isCreation = true)
    {
        switch ($provider) {
            case 'stripe':
                $rules['credentials.publishable_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.secret_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            case 'paypal':
                $rules['credentials.client_id'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.client_secret'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_id'] = 'nullable|string'; // Always optional for PayPal
                break;

            case 'paystack':
                $rules['credentials.public_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.secret_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            case 'razorpay':
                $rules['credentials.key_id'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.key_secret'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            case 'flutterwave':
                $rules['credentials.public_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.secret_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.encryption_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            case 'btcpay':
                $rules['credentials.api_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.server_url'] = $isCreation ? 'required|url' : 'nullable|url';
                $rules['credentials.store_id'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            case 'coingate':
                $rules['credentials.api_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.app_id'] = 'nullable|string'; // Always optional
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            case 'square':
                $rules['credentials.application_id'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.access_token'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_signature_key'] = 'nullable|string'; // Always optional
                break;

            case 'mollie':
                $rules['credentials.api_key'] = $isCreation ? 'required|string' : 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;

            default:
                // For generic or unknown providers, allow flexible credential structure
                $rules['credentials.api_key'] = 'nullable|string';
                $rules['credentials.secret_key'] = 'nullable|string';
                $rules['credentials.webhook_secret'] = 'nullable|string'; // Always optional
                break;
        }
    }
    
    /**
     * Generate webhook/callback URLs for a specific payment provider.
     */
    private function generateWebhookUrls($provider)
    {
        $baseUrl = rtrim(config('app.url'), '/');
        
        $urls = [
            'webhook_url' => null,
            'callback_url' => null,
            'success_url' => null,
            'cancel_url' => null,
            'ipn_url' => null, // For PayPal IPN
        ];
        
        switch ($provider) {
            case 'stripe':
                $urls['webhook_url'] = $baseUrl . '/stripe/webhook';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'paypal':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/paypal';
                $urls['ipn_url'] = $baseUrl . '/payment/webhook/paypal';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'paystack':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/paystack';
                $urls['callback_url'] = $baseUrl . '/payment/paystack/callback';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'razorpay':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/razorpay';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'flutterwave':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/flutterwave';
                $urls['callback_url'] = $baseUrl . '/payment/flutterwave/callback';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'btcpay':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/btcpay';
                $urls['callback_url'] = $baseUrl . '/payment/btcpay/callback';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'coingate':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/coingate';
                $urls['callback_url'] = $baseUrl . '/payment/coingate/callback';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'square':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/square';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            case 'mollie':
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/mollie';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
                
            default:
                $urls['webhook_url'] = $baseUrl . '/payment/webhook/' . $provider;
                $urls['callback_url'] = $baseUrl . '/payment/' . $provider . '/callback';
                $urls['success_url'] = $baseUrl . '/payment/success';
                $urls['cancel_url'] = $baseUrl . '/payment/cancelled';
                break;
        }
        
        // Filter out null values
        return array_filter($urls);
    }
    
    /**
     * Get webhook configuration instructions for a specific provider.
     */
    public function getWebhookInstructions($provider)
    {
        $instructions = [
            'stripe' => [
                'title' => 'Stripe Webhook Configuration',
                'steps' => [
                    '1. Go to your Stripe Dashboard → Developers → Webhooks',
                    '2. Click "Add endpoint"',
                    '3. Use the Webhook URL provided below',
                    '4. Select events: payment_intent.succeeded, payment_intent.payment_failed',
                    '5. Copy the webhook signing secret to the Webhook Secret field'
                ],
                'events' => ['payment_intent.succeeded', 'payment_intent.payment_failed']
            ],
            'paystack' => [
                'title' => 'Paystack Webhook Configuration',
                'steps' => [
                    '1. Log into your Paystack Dashboard',
                    '2. Go to Settings → API Keys & Webhooks',
                    '3. In the Webhook section, add the Webhook URL provided below',
                    '4. Copy the webhook secret to the Webhook Secret field',
                    '5. Ensure events like charge.success are enabled'
                ],
                'events' => ['charge.success', 'charge.failed']
            ],
            'flutterwave' => [
                'title' => 'Flutterwave Webhook Configuration',
                'steps' => [
                    '1. Log into your Flutterwave Dashboard',
                    '2. Go to Settings → Webhooks',
                    '3. Add the Webhook URL provided below',
                    '4. Copy the webhook hash to the Webhook Secret field',
                    '5. Enable relevant events like charge.completed'
                ],
                'events' => ['charge.completed', 'charge.failed']
            ],
            'btcpay' => [
                'title' => 'BTCPay Server Webhook Configuration',
                'steps' => [
                    '1. Access your BTCPay Server admin panel',
                    '2. Go to Store Settings → Webhooks',
                    '3. Create a new webhook with the Webhook URL provided below',
                    '4. Select events: InvoiceSettled, InvoiceExpired, InvoiceInvalid',
                    '5. Copy the generated webhook secret to the Webhook Secret field',
                    '6. Also configure the success/redirect URL in Store Settings → Checkout Experience'
                ],
                'events' => ['InvoiceSettled', 'InvoiceExpired', 'InvoiceInvalid']
            ],
            'coingate' => [
                'title' => 'CoinGate Webhook Configuration',
                'steps' => [
                    '1. Log into your CoinGate account',
                    '2. Go to API → Settings',
                    '3. Add the Webhook URL provided below',
                    '4. Events are automatically configured',
                    '5. Copy the callback secret if available to the Webhook Secret field'
                ],
                'events' => ['order_paid', 'order_canceled', 'order_expired']
            ],
            'paypal' => [
                'title' => 'PayPal Webhook Configuration',
                'steps' => [
                    '1. Go to PayPal Developer Console → Applications',
                    '2. Select your application',
                    '3. In Webhooks section, add the Webhook URL provided below',
                    '4. Subscribe to events: PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.DENIED',
                    '5. Copy the webhook ID to the Webhook ID field'
                ],
                'events' => ['PAYMENT.CAPTURE.COMPLETED', 'PAYMENT.CAPTURE.DENIED']
            ]
        ];
        
        return $instructions[$provider] ?? null;
    }
}
