<?php

namespace App\Services\Integrations;

use App\Models\IntegrationRequest;
use App\Models\LabTestOrder;
use App\Models\Patient;
use App\Models\Doctor;

class RandoxService extends BaseIntegrationService
{
    /**
     * Test connection to Randox API
     */
    public function testConnection(): array
    {
        try {
            // In production, this would call the actual Randox API health endpoint
            $apiKey = $this->getApiKey();

            if (empty($apiKey)) {
                return [
                    'success' => false,
                    'message' => 'API key not configured',
                ];
            }

            // Simulated test - replace with actual API call
            // $response = $this->makeRequest('get', '/api/health');

            return [
                'success' => true,
                'message' => 'Connection successful',
                'environment' => $this->module->environment,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available lab tests from Randox
     */
    public function getAvailableServices(): array
    {
        // In production, fetch from Randox API
        // For now, return common lab tests
        return [
            ['code' => 'FBC', 'name' => 'Full Blood Count', 'price' => 25.00],
            ['code' => 'LFT', 'name' => 'Liver Function Test', 'price' => 35.00],
            ['code' => 'RFT', 'name' => 'Renal Function Test', 'price' => 30.00],
            ['code' => 'TFT', 'name' => 'Thyroid Function Test', 'price' => 40.00],
            ['code' => 'LIPID', 'name' => 'Lipid Profile', 'price' => 35.00],
            ['code' => 'HBA1C', 'name' => 'HbA1c (Diabetes)', 'price' => 25.00],
            ['code' => 'GLUCOSE', 'name' => 'Fasting Glucose', 'price' => 15.00],
            ['code' => 'VITD', 'name' => 'Vitamin D', 'price' => 45.00],
            ['code' => 'VITB12', 'name' => 'Vitamin B12', 'price' => 35.00],
            ['code' => 'IRON', 'name' => 'Iron Studies', 'price' => 40.00],
            ['code' => 'CRP', 'name' => 'C-Reactive Protein', 'price' => 20.00],
            ['code' => 'PSA', 'name' => 'PSA (Prostate)', 'price' => 30.00],
            ['code' => 'URINE', 'name' => 'Urinalysis', 'price' => 15.00],
            ['code' => 'COAG', 'name' => 'Coagulation Screen', 'price' => 35.00],
            ['code' => 'ESR', 'name' => 'ESR', 'price' => 15.00],
        ];
    }

    /**
     * Create a lab test order
     */
    public function createOrder(
        Patient $patient,
        Doctor $doctor,
        array $tests,
        string $priority = 'routine',
        ?string $clinicalNotes = null,
        ?string $specialInstructions = null,
        ?int $appointmentId = null
    ): LabTestOrder {
        // Create integration request
        $request = $this->createRequest('order', [
            'patient_id' => $patient->id,
            'tests' => $tests,
            'priority' => $priority,
        ], $patient->id, $doctor->id);

        // Create lab test order
        $order = LabTestOrder::create([
            'integration_request_id' => $request->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointmentId,
            'tests_requested' => $tests,
            'priority' => $priority,
            'clinical_notes' => $clinicalNotes,
            'special_instructions' => $specialInstructions,
            'status' => LabTestOrder::STATUS_DRAFT,
        ]);

        return $order;
    }

    /**
     * Submit order to Randox
     */
    public function submitOrder(LabTestOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Randox integration is not configured or active',
            ];
        }

        try {
            $patient = $order->patient;

            // Prepare order data for Randox API
            $orderData = [
                'patient' => [
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                    'gender' => $patient->gender,
                    'email' => $patient->email,
                    'phone' => $patient->phone,
                    'nhs_number' => $patient->nhs_number ?? null,
                ],
                'tests' => $order->tests_requested,
                'priority' => $order->priority,
                'clinical_notes' => $order->clinical_notes,
                'reference' => $order->order_number,
            ];

            // In production, send to Randox API
            // $response = $this->makeRequest('post', '/api/orders', $orderData);

            // Simulated response
            $externalOrderId = 'RDX-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Update order
            $order->markOrdered($externalOrderId);

            // Update integration request
            $order->integrationRequest->markSubmitted($externalOrderId);

            $this->logInfo("Lab test order submitted: {$order->order_number}", [
                'external_id' => $externalOrderId,
            ]);

            return [
                'success' => true,
                'message' => 'Order submitted successfully',
                'external_order_id' => $externalOrderId,
            ];
        } catch (\Exception $e) {
            $order->integrationRequest->markFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to submit order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check order status
     */
    public function checkOrderStatus(LabTestOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Randox integration is not configured or active',
            ];
        }

        try {
            // In production, call Randox API
            // $response = $this->makeRequest('get', "/api/orders/{$order->external_order_id}");

            return [
                'success' => true,
                'status' => $order->status,
                'message' => 'Status retrieved',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to check status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch results for an order
     */
    public function fetchResults(LabTestOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Randox integration is not configured or active',
            ];
        }

        try {
            // In production, fetch from Randox API
            // $response = $this->makeRequest('get', "/api/orders/{$order->external_order_id}/results");

            // Simulated results - in production this comes from Randox
            $results = [
                'received_at' => now()->toIso8601String(),
                'tests' => [],
            ];

            return [
                'success' => true,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch results: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(LabTestOrder $order, string $reason = null): array
    {
        if (!in_array($order->status, [LabTestOrder::STATUS_DRAFT, LabTestOrder::STATUS_ORDERED])) {
            return [
                'success' => false,
                'message' => 'Order cannot be cancelled in current status',
            ];
        }

        try {
            // In production, cancel via Randox API if already submitted
            if ($order->external_order_id) {
                // $response = $this->makeRequest('post', "/api/orders/{$order->external_order_id}/cancel");
            }

            $order->update(['status' => LabTestOrder::STATUS_CANCELLED]);
            $order->integrationRequest->markCancelled($reason);

            return [
                'success' => true,
                'message' => 'Order cancelled successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook from Randox
     */
    public function processWebhook(array $payload): array
    {
        $eventType = $payload['event'] ?? 'unknown';

        switch ($eventType) {
            case 'results_ready':
                return $this->handleResultsReadyWebhook($payload);

            case 'sample_received':
                return $this->handleSampleReceivedWebhook($payload);

            default:
                $this->logInfo("Unknown webhook event: {$eventType}");
                return ['success' => true, 'message' => 'Event ignored'];
        }
    }

    /**
     * Handle results ready webhook
     */
    protected function handleResultsReadyWebhook(array $payload): array
    {
        $externalOrderId = $payload['order_id'] ?? null;

        if (!$externalOrderId) {
            return ['success' => false, 'message' => 'Missing order_id'];
        }

        $order = LabTestOrder::where('external_order_id', $externalOrderId)->first();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Fetch and store results
        $results = $payload['results'] ?? [];
        $pdfUrl = $payload['pdf_url'] ?? null;

        $order->markResultsReady($results, $pdfUrl);
        $order->integrationRequest->markCompleted(['results' => $results]);

        $this->logInfo("Results received for order: {$order->order_number}");

        return ['success' => true, 'message' => 'Results processed'];
    }

    /**
     * Handle sample received webhook
     */
    protected function handleSampleReceivedWebhook(array $payload): array
    {
        $externalOrderId = $payload['order_id'] ?? null;

        if (!$externalOrderId) {
            return ['success' => false, 'message' => 'Missing order_id'];
        }

        $order = LabTestOrder::where('external_order_id', $externalOrderId)->first();

        if ($order) {
            $order->markSampleCollected();
            $this->logInfo("Sample collected for order: {$order->order_number}");
        }

        return ['success' => true, 'message' => 'Sample status updated'];
    }

    /**
     * Get required config fields
     */
    protected function getRequiredConfigFields(): array
    {
        return [
            'api_key' => 'API Key',
            'client_id' => 'Client ID',
        ];
    }

    /**
     * Get config form fields for admin UI
     */
    public function getConfigFormFields(): array
    {
        return [
            [
                'name' => 'client_id',
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'help' => 'Your Randox client identifier',
            ],
            [
                'name' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'help' => 'Your Randox API key',
            ],
            [
                'name' => 'sandbox_url',
                'label' => 'Sandbox URL',
                'type' => 'url',
                'required' => false,
                'default' => 'https://sandbox-api.randox.com',
                'help' => 'Randox sandbox API URL',
            ],
            [
                'name' => 'production_url',
                'label' => 'Production URL',
                'type' => 'url',
                'required' => false,
                'default' => 'https://api.randox.com',
                'help' => 'Randox production API URL',
            ],
            [
                'name' => 'webhook_secret',
                'label' => 'Webhook Secret',
                'type' => 'password',
                'required' => false,
                'help' => 'Secret key for validating webhooks from Randox',
            ],
        ];
    }
}
