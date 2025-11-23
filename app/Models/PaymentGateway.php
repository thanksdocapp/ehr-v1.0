<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'provider',
        'is_active',
        'is_default',
        'credentials',
        'settings',
        'supported_currencies',
        'supported_countries',
        'supported_methods',
        'webhook_url',
        'webhook_secret',
        'test_mode',
        'transaction_fee_percentage',
        'transaction_fee_fixed',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'test_mode' => 'boolean',
        'credentials' => 'array',
        'settings' => 'array',
        'supported_currencies' => 'array',
        'supported_countries' => 'array',
        'supported_methods' => 'array',
        'transaction_fee_percentage' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2',
    ];

    protected $hidden = [
        'credentials',
        'webhook_secret',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    // Accessors & Mutators
    protected function credentials(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode(Crypt::decryptString($value), true) : [],
            set: fn ($value) => Crypt::encryptString(json_encode($value))
        );
    }

    // Helper Methods
    public function getCredential($key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    public function setCredential($key, $value)
    {
        $credentials = $this->credentials;
        $credentials[$key] = $value;
        $this->credentials = $credentials;
        return $this;
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }

    public function supportsCurrency($currency)
    {
        return in_array(strtoupper($currency), $this->supported_currencies ?? []);
    }

    public function supportsCountry($country)
    {
        return in_array(strtoupper($country), $this->supported_countries ?? []);
    }

    public function supportsMethod($method)
    {
        return in_array($method, $this->supported_methods ?? []);
    }

    public function calculateFee($amount)
    {
        $percentageFee = ($amount * $this->transaction_fee_percentage) / 100;
        return $percentageFee + $this->transaction_fee_fixed;
    }

    public function getLogoPath()
    {
        return "images/payment-gateways/{$this->name}.svg";
    }

    // Static methods
    public static function getActiveGateways()
    {
        return static::active()->orderBySort()->get();
    }

    public static function getDefaultGateway()
    {
        return static::active()->default()->first();
    }

    public static function getAvailableProviders()
    {
        return [
            'stripe' => [
                'name' => 'Stripe',
                'description' => 'Accept payments online. Stripe is a suite of payment APIs.',
                'countries' => ['US', 'CA', 'GB', 'AU', 'EU'],
                'currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
                'methods' => ['card', 'bank_transfer', 'apple_pay', 'google_pay'],
                'credentials' => ['publishable_key', 'secret_key', 'webhook_secret']
            ],
            'paypal' => [
                'name' => 'PayPal',
                'description' => 'The safer, easier way to pay and get paid online.',
                'countries' => ['US', 'CA', 'GB', 'AU', 'EU', 'IN'],
                'currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'INR'],
                'methods' => ['paypal', 'card'],
                'credentials' => ['client_id', 'client_secret', 'webhook_id']
            ],
            'paystack' => [
                'name' => 'Paystack',
                'description' => 'Modern online and offline payments for Africa.',
                'countries' => ['NG', 'GH', 'ZA', 'KE'],
                'currencies' => ['NGN', 'GHS', 'ZAR', 'KES'],
                'methods' => ['card', 'bank_transfer', 'ussd', 'mobile_money'],
                'credentials' => ['public_key', 'secret_key', 'webhook_secret']
            ],
            // 'razorpay' => [
            //     'name' => 'Razorpay',
            //     'description' => 'Complete payments solution for India.',
            //     'countries' => ['IN'],
            //     'currencies' => ['INR'],
            //     'methods' => ['card', 'upi', 'netbanking', 'wallet'],
            //     'credentials' => ['key_id', 'key_secret', 'webhook_secret']
            // ],
            'flutterwave' => [
                'name' => 'Flutterwave',
                'description' => 'Payment infrastructure for global merchants.',
                'countries' => ['NG', 'GH', 'KE', 'UG', 'ZA', 'US', 'GB'],
                'currencies' => ['NGN', 'USD', 'EUR', 'GBP', 'KES', 'GHS'],
                'methods' => ['card', 'bank_transfer', 'mobile_money', 'ussd'],
                'credentials' => ['public_key', 'secret_key', 'encryption_key', 'webhook_secret']
            ],
            'coingate' => [
                'name' => 'CoinGate',
                'description' => 'Accept Bitcoin, Ethereum, and 70+ cryptocurrencies. Real-time exchange rates.',
                'countries' => ['GLOBAL'],
                'currencies' => ['BTC', 'ETH', 'LTC', 'BCH', 'XRP', 'ADA', 'DOT', 'USDT', 'USDC', 'USD', 'EUR'],
                'methods' => ['bitcoin', 'ethereum', 'litecoin', 'bitcoin_cash', 'ripple', 'cardano', 'polkadot', 'tether', 'usd_coin', 'altcoins'],
                'credentials' => ['api_key', 'app_id', 'webhook_secret'],
                'features' => ['instant_settlement', 'auto_conversion', 'real_time_rates', 'multi_crypto'],
                'settlement_currencies' => ['USD', 'EUR', 'BTC', 'ETH']
            ],
            'btcpay' => [
                'name' => 'BTCPay Server',
                'description' => 'Self-hosted, open-source cryptocurrency payment processor. No fees, no middleman.',
                'countries' => ['GLOBAL'],
                'currencies' => ['BTC', 'LTC', 'ETH', 'BCH', 'XMR', 'DASH', 'ZEC', 'DOGE', 'USDT', 'USDC'],
                'methods' => ['bitcoin', 'lightning_network', 'ethereum', 'litecoin', 'bitcoin_cash', 'monero', 'dash', 'zcash', 'dogecoin', 'stablecoins'],
                'credentials' => ['server_url', 'store_id', 'api_key', 'webhook_secret'],
                'features' => ['self_hosted', 'no_fees', 'full_control', 'privacy_focused', 'lightning_network'],
                'settlement_currencies' => ['BTC', 'LTC', 'ETH', 'BCH', 'NATIVE_CRYPTO']
            ]
        ];
    }
}
