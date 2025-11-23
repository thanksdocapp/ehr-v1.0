<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'stripe',
                'display_name' => 'Stripe',
                'description' => 'Secure online payments with Stripe. Accepts major credit and debit cards worldwide.',
                'provider' => 'stripe',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => ''
                ]),
                'settings' => json_encode([
                    'capture_method' => 'automatic',
                    'payment_method_types' => ['card']
                ]),
                'supported_currencies' => json_encode([
                    'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'SEK', 'NOK', 'DKK'
                ]),
                'supported_countries' => json_encode([
                    'US', 'CA', 'GB', 'AU', 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 
                    'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 
                    'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'JP', 'SG', 'HK', 'MY'
                ]),
                'supported_methods' => json_encode(['card', 'bank_transfer', 'wallet']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 2.90,
                'transaction_fee_fixed' => 0.30,
                'sort_order' => 1
            ],
            [
                'name' => 'paypal',
                'display_name' => 'PayPal',
                'description' => 'Pay securely with your PayPal account or credit card.',
                'provider' => 'paypal',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                    'webhook_id' => ''
                ]),
                'settings' => json_encode([
                    'intent' => 'capture',
                    'application_context' => [
                        'brand_name' => 'Hospital Management System',
                        'user_action' => 'PAY_NOW'
                    ]
                ]),
                'supported_currencies' => json_encode([
                    'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'HKD', 'SGD', 'SEK', 
                    'DKK', 'PLN', 'NOK', 'HUF', 'CZK', 'ILS', 'MXN', 'BRL', 'MYR', 'PHP'
                ]),
                'supported_countries' => json_encode([
                    'US', 'CA', 'GB', 'AU', 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 
                    'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 
                    'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'JP', 'SG', 'HK', 'MY', 'MX', 'BR'
                ]),
                'supported_methods' => json_encode(['paypal', 'card']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 3.49,
                'transaction_fee_fixed' => 0.49,
                'sort_order' => 2
            ],
            [
                'name' => 'paystack',
                'display_name' => 'Paystack',
                'description' => 'Accept payments from customers in Africa with Paystack. Supports cards, bank transfers, and mobile money.',
                'provider' => 'paystack',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => ''
                ]),
                'settings' => json_encode([
                    'callback_url' => '',
                    'channels' => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer']
                ]),
                'supported_currencies' => json_encode([
                    'NGN', 'USD', 'GHS', 'ZAR', 'KES'
                ]),
                'supported_countries' => json_encode([
                    'NG', 'GH', 'ZA', 'KE'
                ]),
                'supported_methods' => json_encode(['card', 'bank_transfer', 'ussd', 'qr', 'mobile_money']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 1.95,
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 3
            ],
            [
                'name' => 'flutterwave',
                'display_name' => 'Flutterwave',
                'description' => 'Accept payments across Africa and globally with Flutterwave. Supports multiple payment methods and currencies.',
                'provider' => 'flutterwave',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'encryption_key' => '',
                    'webhook_secret' => ''
                ]),
                'settings' => json_encode([
                    'redirect_url' => '',
                    'payment_options' => 'card,mobilemoney,ussd,banktransfer,barter,account',
                    'payment_plan' => '',
                    'customizations' => [
                        'title' => 'Hospital Management System',
                        'description' => 'Payment for medical services',
                        'logo' => '/images/logos/flutterwave-logo.svg'
                    ]
                ]),
                'supported_currencies' => json_encode([
                    'NGN', 'USD', 'EUR', 'GBP', 'CAD', 'KES', 'UGX', 'TZS', 'ZAR', 'GHS', 
                    'XAF', 'XOF', 'EGP', 'MAD', 'RWF', 'ZMW', 'MWK', 'AOA', 'MZN', 'SLL'
                ]),
                'supported_countries' => json_encode([
                    'NG', 'KE', 'UG', 'TZ', 'ZA', 'GH', 'CM', 'SN', 'CI', 'BF', 'ML', 'NE', 
                    'TD', 'EG', 'MA', 'RW', 'ZM', 'MW', 'AO', 'MZ', 'SL', 'US', 'GB', 'CA'
                ]),
                'supported_methods' => json_encode(['card', 'bank_transfer', 'ussd', 'mobile_money', 'qr', 'barter']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 1.40,
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 4
            ],
            [
                'name' => 'razorpay',
                'display_name' => 'Razorpay',
                'description' => 'Accept payments in India with Razorpay. Supports cards, UPI, net banking, and wallets.',
                'provider' => 'razorpay',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'key_id' => '',
                    'key_secret' => '',
                    'webhook_secret' => ''
                ]),
                'settings' => json_encode([
                    'theme' => [
                        'color' => '#3399cc'
                    ],
                    'prefill' => true
                ]),
                'supported_currencies' => json_encode([
                    'INR'
                ]),
                'supported_countries' => json_encode([
                    'IN'
                ]),
                'supported_methods' => json_encode(['card', 'netbanking', 'wallet', 'upi']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 2.00,
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 5
            ],
            [
                'name' => 'btcpay',
                'display_name' => 'BTCPay Server',
                'description' => 'Accept Bitcoin and other cryptocurrency payments with BTCPay Server.',
                'provider' => 'btcpay',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'server_url' => '',
                    'store_id' => '',
                    'api_key' => '',
                    'webhook_secret' => ''
                ]),
                'settings' => json_encode([
                    'speed_policy' => 'MediumSpeed',
                    'checkout_expiry' => 15
                ]),
                'supported_currencies' => json_encode([
                    'BTC', 'LTC', 'ETH', 'XMR', 'USD', 'EUR', 'GBP'
                ]),
                'supported_countries' => json_encode([
                    'GLOBAL'
                ]),
                'supported_methods' => json_encode(['cryptocurrency']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 0.00,
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 6
            ],
            [
                'name' => 'coingate',
                'display_name' => 'CoinGate',
                'description' => 'Accept Bitcoin and 70+ other cryptocurrencies with CoinGate.',
                'provider' => 'coingate',
                'is_active' => false,
                'is_default' => false,
                'credentials' => json_encode([
                    'api_token' => '',
                    'webhook_secret' => ''
                ]),
                'settings' => json_encode([
                    'receive_currency' => 'EUR',
                    'callback_url' => '',
                    'cancel_url' => '',
                    'success_url' => ''
                ]),
                'supported_currencies' => json_encode([
                    'BTC', 'ETH', 'LTC', 'XRP', 'BCH', 'USDT', 'EUR', 'USD', 'GBP'
                ]),
                'supported_countries' => json_encode([
                    'GLOBAL'
                ]),
                'supported_methods' => json_encode(['cryptocurrency']),
                'webhook_url' => null,
                'webhook_secret' => null,
                'test_mode' => true,
                'transaction_fee_percentage' => 1.00,
                'transaction_fee_fixed' => 0.00,
                'sort_order' => 7
            ]
        ];

        foreach ($gateways as $gateway) {
            DB::table('payment_gateways')->updateOrInsert(
                ['name' => $gateway['name']],
                array_merge($gateway, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('Payment gateways seeded successfully!');
    }
}
