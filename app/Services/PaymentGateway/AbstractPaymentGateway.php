<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected array $credentials = [];
    protected bool $testMode = false;
    protected string $baseUrl = '';
    protected string $gatewayName = '';
    protected string $displayName = '';

    public function initialize(array $credentials): void
    {
        $this->credentials = $credentials;
        $this->testMode = $credentials['test_mode'] ?? false;
        $this->baseUrl = $this->getBaseUrl();
    }

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * Make HTTP request to gateway API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $defaultHeaders = $this->getDefaultHeaders();
            $headers = array_merge($defaultHeaders, $headers);

            Log::info("Payment Gateway Request", [
                'gateway' => $this->gatewayName,
                'method' => $method,
                'url' => $url,
                'headers' => $headers,
                'data' => $data
            ]);

            $response = Http::withHeaders($headers)->$method($url, $data);

            $responseData = $response->json();

            Log::info("Payment Gateway Response", [
                'gateway' => $this->gatewayName,
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if (!$response->successful()) {
                throw new Exception("Gateway API request failed: " . $response->body());
            }

            return $responseData ?? [];

        } catch (Exception $e) {
            Log::error("Payment Gateway Error", [
                'gateway' => $this->gatewayName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new Exception("Payment gateway error: " . $e->getMessage());
        }
    }

    /**
     * Get base URL for the gateway API
     */
    abstract protected function getBaseUrl(): string;

    /**
     * Get default headers for API requests
     */
    abstract protected function getDefaultHeaders(): array;

    /**
     * Format payment data for the specific gateway
     */
    abstract protected function formatPaymentData(array $paymentData): array;

    /**
     * Parse webhook data from the specific gateway
     */
    abstract protected function parseWebhookData(array $webhookData): array;

    /**
     * Validate webhook signature
     */
    abstract protected function validateWebhookSignature(array $webhookData): bool;
}
