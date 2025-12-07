<?php

namespace App\Services\Integrations;

use App\Models\IntegrationRequest;
use App\Models\PrescriptionOrder;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;

class QuincyService extends BaseIntegrationService
{
    /**
     * Test connection to Quincy API
     */
    public function testConnection(): array
    {
        try {
            $apiKey = $this->getApiKey();

            if (empty($apiKey)) {
                return [
                    'success' => false,
                    'message' => 'API key not configured',
                ];
            }

            // Simulated test - replace with actual API call
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
     * Get available pharmacies
     */
    public function getAvailableServices(): array
    {
        // In production, fetch from Quincy API based on location
        return [
            ['id' => 'BOOTS001', 'name' => 'Boots Pharmacy - High Street', 'distance' => '0.2 miles'],
            ['id' => 'LLOYDS001', 'name' => 'Lloyds Pharmacy - Market Square', 'distance' => '0.4 miles'],
            ['id' => 'WELL001', 'name' => 'Well Pharmacy - Station Road', 'distance' => '0.6 miles'],
            ['id' => 'SUPERDRUG001', 'name' => 'Superdrug Pharmacy - Shopping Centre', 'distance' => '0.8 miles'],
        ];
    }

    /**
     * Search pharmacies by location
     */
    public function searchPharmacies(string $postcode, int $radius = 5): array
    {
        if (!$this->isReady()) {
            return [];
        }

        try {
            // In production, call Quincy API
            // $response = $this->makeRequest('get', '/api/pharmacies', [
            //     'postcode' => $postcode,
            //     'radius' => $radius,
            // ]);

            return $this->getAvailableServices();
        } catch (\Exception $e) {
            $this->logError("Failed to search pharmacies: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Create prescription order
     */
    public function createOrder(
        Patient $patient,
        Doctor $doctor,
        array $medications,
        ?string $pharmacyId = null,
        ?string $pharmacyName = null,
        string $deliveryMethod = 'collection',
        ?string $deliveryAddress = null,
        ?int $prescriptionId = null,
        ?string $clinicalNotes = null
    ): PrescriptionOrder {
        // Create integration request
        $request = $this->createRequest('order', [
            'patient_id' => $patient->id,
            'medications' => $medications,
            'pharmacy_id' => $pharmacyId,
        ], $patient->id, $doctor->id);

        // Create prescription order
        $order = PrescriptionOrder::create([
            'integration_request_id' => $request->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_id' => $prescriptionId,
            'pharmacy_id' => $pharmacyId,
            'pharmacy_name' => $pharmacyName,
            'medications' => $medications,
            'delivery_method' => $deliveryMethod,
            'delivery_address' => $deliveryAddress,
            'clinical_notes' => $clinicalNotes,
            'status' => PrescriptionOrder::STATUS_DRAFT,
        ]);

        return $order;
    }

    /**
     * Submit prescription to Quincy/Pharmacy
     */
    public function submitOrder(PrescriptionOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
            ];
        }

        try {
            $patient = $order->patient;
            $doctor = $order->doctor;

            // Prepare prescription data
            $prescriptionData = [
                'patient' => [
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                    'nhs_number' => $patient->nhs_number ?? null,
                    'address' => $patient->address,
                    'phone' => $patient->phone,
                ],
                'prescriber' => [
                    'name' => $doctor->full_name,
                    'gmc_number' => $doctor->gmc_number ?? null,
                    'prescriber_type' => 'doctor',
                ],
                'pharmacy_id' => $order->pharmacy_id,
                'medications' => $order->medications,
                'delivery_method' => $order->delivery_method,
                'delivery_address' => $order->delivery_address,
                'reference' => $order->order_number,
            ];

            // In production, send to Quincy API
            // $response = $this->makeRequest('post', '/api/prescriptions', $prescriptionData);

            // Simulated response
            $externalOrderId = 'QNC-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Update order
            $order->markSubmitted($externalOrderId);

            // Update integration request
            $order->integrationRequest->markSubmitted($externalOrderId);

            $this->logInfo("Prescription submitted: {$order->order_number}", [
                'external_id' => $externalOrderId,
            ]);

            return [
                'success' => true,
                'message' => 'Prescription submitted to pharmacy',
                'external_order_id' => $externalOrderId,
            ];
        } catch (\Exception $e) {
            $order->integrationRequest->markFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to submit prescription: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check order status
     */
    public function checkOrderStatus(PrescriptionOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
            ];
        }

        try {
            // In production, call Quincy API
            // $response = $this->makeRequest('get', "/api/prescriptions/{$order->external_order_id}");

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
     * Cancel prescription order
     */
    public function cancelOrder(PrescriptionOrder $order, string $reason = null): array
    {
        if (!$order->canBeCancelled()) {
            return [
                'success' => false,
                'message' => 'Prescription cannot be cancelled in current status',
            ];
        }

        try {
            // In production, cancel via Quincy API
            if ($order->external_order_id) {
                // $response = $this->makeRequest('post', "/api/prescriptions/{$order->external_order_id}/cancel");
            }

            $order->markCancelled();
            $order->integrationRequest->markCancelled($reason);

            return [
                'success' => true,
                'message' => 'Prescription cancelled',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel prescription: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook from Quincy
     */
    public function processWebhook(array $payload): array
    {
        $eventType = $payload['event'] ?? 'unknown';

        switch ($eventType) {
            case 'prescription_accepted':
                return $this->handleAcceptedWebhook($payload);

            case 'prescription_dispensed':
                return $this->handleDispensedWebhook($payload);

            case 'prescription_ready':
                return $this->handleReadyWebhook($payload);

            case 'prescription_collected':
                return $this->handleCollectedWebhook($payload);

            case 'prescription_rejected':
                return $this->handleRejectedWebhook($payload);

            default:
                $this->logInfo("Unknown webhook event: {$eventType}");
                return ['success' => true, 'message' => 'Event ignored'];
        }
    }

    protected function handleAcceptedWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['prescription_id'] ?? null);
        if ($order) {
            $order->markAccepted();
            $this->logInfo("Prescription accepted: {$order->order_number}");
        }
        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function handleDispensedWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['prescription_id'] ?? null);
        if ($order) {
            $order->markDispensing();
        }
        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function handleReadyWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['prescription_id'] ?? null);
        if ($order) {
            $order->markReady();
            // TODO: Notify patient prescription is ready
        }
        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function handleCollectedWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['prescription_id'] ?? null);
        if ($order) {
            $order->markCollected();
            $order->integrationRequest->markCompleted();
        }
        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function handleRejectedWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['prescription_id'] ?? null);
        if ($order) {
            $reason = $payload['reason'] ?? 'Unknown reason';
            $order->markRejected($reason);
            $order->integrationRequest->markFailed($reason);
            // TODO: Notify doctor of rejection
        }
        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function findOrderByExternalId(?string $externalId): ?PrescriptionOrder
    {
        if (!$externalId) return null;
        return PrescriptionOrder::where('external_order_id', $externalId)->first();
    }

    /**
     * Get required config fields
     */
    protected function getRequiredConfigFields(): array
    {
        return [
            'api_key' => 'API Key',
            'organisation_id' => 'Organisation ID',
        ];
    }

    /**
     * Get config form fields for admin UI
     */
    public function getConfigFormFields(): array
    {
        return [
            [
                'name' => 'organisation_id',
                'label' => 'Organisation ID',
                'type' => 'text',
                'required' => true,
                'help' => 'Your Quincy organisation identifier',
            ],
            [
                'name' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'help' => 'Your Quincy API key',
            ],
            [
                'name' => 'prescriber_id',
                'label' => 'Default Prescriber ID',
                'type' => 'text',
                'required' => false,
                'help' => 'Default prescriber identifier (optional)',
            ],
            [
                'name' => 'sandbox_url',
                'label' => 'Sandbox URL',
                'type' => 'url',
                'required' => false,
                'default' => 'https://sandbox.quincy.co.uk/api',
                'help' => 'Quincy sandbox API URL',
            ],
            [
                'name' => 'production_url',
                'label' => 'Production URL',
                'type' => 'url',
                'required' => false,
                'default' => 'https://api.quincy.co.uk',
                'help' => 'Quincy production API URL',
            ],
            [
                'name' => 'webhook_secret',
                'label' => 'Webhook Secret',
                'type' => 'password',
                'required' => false,
                'help' => 'Secret for validating Quincy webhooks',
            ],
        ];
    }
}
