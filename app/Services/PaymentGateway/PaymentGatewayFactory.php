<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use Exception;

class PaymentGatewayFactory
{
    /**
     * Create a payment gateway instance
     */
    public static function create(string $provider, array $credentials = []): PaymentGatewayInterface
    {
        switch ($provider) {
            case 'stripe':
                return new StripeGateway();
            
            case 'paypal':
                return new PayPalGateway();
            
            case 'paystack':
                return new PaystackGateway();
            
            case 'coingate':
                return new CoinGateGateway();
            
            case 'btcpay':
                return new BTCPayGateway();
            
            case 'flutterwave':
                return new FlutterwaveGateway();
            
            default:
                throw new Exception("Unsupported payment gateway provider: {$provider}");
        }
    }

    /**
     * Create gateway from PaymentGateway model
     */
    public static function createFromModel(PaymentGateway $gateway): PaymentGatewayInterface
    {
        $gatewayInstance = self::create($gateway->provider);
        
        $credentials = array_merge($gateway->credentials ?? [], [
            'test_mode' => $gateway->test_mode
        ]);
        
        $gatewayInstance->initialize($credentials);
        
        return $gatewayInstance;
    }

    /**
     * Get available gateway providers
     */
    public static function getAvailableProviders(): array
    {
        return [
            'stripe' => StripeGateway::class,
            'paypal' => PayPalGateway::class,
            'paystack' => PaystackGateway::class,
            'coingate' => CoinGateGateway::class,
            'btcpay' => BTCPayGateway::class,
            'flutterwave' => FlutterwaveGateway::class,
        ];
    }

    /**
     * Check if provider is supported
     */
    public static function isProviderSupported(string $provider): bool
    {
        return array_key_exists($provider, self::getAvailableProviders());
    }
}
