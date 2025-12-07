<?php

namespace App\Services\Integrations;

use App\Models\IntegrationModule;
use App\Models\IntegrationRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseIntegrationService
{
    protected IntegrationModule $module;
    protected array $config;

    public function __construct(IntegrationModule $module)
    {
        $this->module = $module;
        $this->config = $module->config ?? [];
    }

    /**
     * Get API base URL
     */
    protected function getBaseUrl(): string
    {
        if ($this->module->environment === 'production') {
            return $this->config['production_url'] ?? $this->config['base_url'] ?? '';
        }

        return $this->config['sandbox_url'] ?? $this->config['base_url'] ?? '';
    }

    /**
     * Get API key
     */
    protected function getApiKey(): ?string
    {
        return $this->config['api_key'] ?? null;
    }

    /**
     * Get API secret
     */
    protected function getApiSecret(): ?string
    {
        return $this->config['api_secret'] ?? null;
    }

    /**
     * Make HTTP request
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = [])
    {
        $url = rtrim($this->getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');

        $defaultHeaders = $this->getDefaultHeaders();
        $headers = array_merge($defaultHeaders, $headers);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->$method($url, $data);

            if ($response->failed()) {
                $this->logError("API request failed: {$response->status()}", [
                    'endpoint' => $endpoint,
                    'response' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            $this->logError("API request exception: {$e->getMessage()}", [
                'endpoint' => $endpoint,
            ]);

            throw $e;
        }
    }

    /**
     * Get default headers for API requests
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Log an error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->module->slug}] {$message}", $context);
        $this->module->logError($message);
    }

    /**
     * Log info
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info("[{$this->module->slug}] {$message}", $context);
    }

    /**
     * Create integration request
     */
    protected function createRequest(string $type, array $data, ?int $patientId = null, ?int $doctorId = null): IntegrationRequest
    {
        return IntegrationRequest::create([
            'integration_module_id' => $this->module->id,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'created_by' => auth()->id(),
            'request_type' => $type,
            'request_data' => $data,
            'status' => IntegrationRequest::STATUS_PENDING,
        ]);
    }

    /**
     * Check if module is ready
     */
    public function isReady(): bool
    {
        return $this->module->isReady();
    }

    /**
     * Test connection to external service
     */
    abstract public function testConnection(): array;

    /**
     * Get available options/services from the provider
     */
    abstract public function getAvailableServices(): array;

    /**
     * Validate configuration
     */
    public function validateConfig(): array
    {
        $errors = [];
        $required = $this->getRequiredConfigFields();

        foreach ($required as $field => $label) {
            if (empty($this->config[$field])) {
                $errors[] = "{$label} is required";
            }
        }

        return $errors;
    }

    /**
     * Get required config fields
     */
    protected function getRequiredConfigFields(): array
    {
        return [
            'api_key' => 'API Key',
        ];
    }

    /**
     * Get config form fields for admin UI
     */
    abstract public function getConfigFormFields(): array;

    /**
     * Process webhook
     */
    public function processWebhook(array $payload): array
    {
        // Default implementation - override in child classes
        return [
            'success' => true,
            'message' => 'Webhook received',
        ];
    }
}
