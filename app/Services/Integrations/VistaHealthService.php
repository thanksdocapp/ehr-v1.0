<?php

namespace App\Services\Integrations;

use App\Models\IntegrationRequest;
use App\Models\ImagingOrder;
use App\Models\Patient;
use App\Models\Doctor;

class VistaHealthService extends BaseIntegrationService
{
    /**
     * Test connection to Vista Health API
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
     * Get available scan types and locations
     */
    public function getAvailableServices(): array
    {
        // In production, fetch from Vista Health API
        return [
            'scan_types' => ImagingOrder::getScanTypes(),
            'locations' => [
                ['id' => 'VH-LON-01', 'name' => 'Vista Health - London Central', 'address' => '123 Harley Street, London'],
                ['id' => 'VH-LON-02', 'name' => 'Vista Health - London East', 'address' => '45 City Road, London'],
                ['id' => 'VH-MAN-01', 'name' => 'Vista Health - Manchester', 'address' => '78 Deansgate, Manchester'],
                ['id' => 'VH-BIR-01', 'name' => 'Vista Health - Birmingham', 'address' => '12 Colmore Row, Birmingham'],
            ],
        ];
    }

    /**
     * Get available appointment slots for a scan type and location
     */
    public function getAvailableSlots(string $scanType, string $locationId, string $date): array
    {
        if (!$this->isReady()) {
            return [];
        }

        try {
            // In production, call Vista Health API
            // $response = $this->makeRequest('get', '/api/availability', [
            //     'scan_type' => $scanType,
            //     'location_id' => $locationId,
            //     'date' => $date,
            // ]);

            // Simulated slots
            return [
                ['time' => '09:00', 'available' => true],
                ['time' => '10:00', 'available' => true],
                ['time' => '11:00', 'available' => false],
                ['time' => '14:00', 'available' => true],
                ['time' => '15:00', 'available' => true],
                ['time' => '16:00', 'available' => true],
            ];
        } catch (\Exception $e) {
            $this->logError("Failed to get available slots: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Create imaging order
     */
    public function createOrder(
        Patient $patient,
        Doctor $doctor,
        string $scanType,
        string $bodyPart,
        string $priority = 'routine',
        ?string $clinicalIndication = null,
        ?string $clinicalHistory = null,
        ?string $specialInstructions = null,
        bool $contrastRequired = false,
        ?int $appointmentId = null
    ): ImagingOrder {
        // Create integration request
        $request = $this->createRequest('order', [
            'patient_id' => $patient->id,
            'scan_type' => $scanType,
            'body_part' => $bodyPart,
            'priority' => $priority,
        ], $patient->id, $doctor->id);

        // Create imaging order
        $order = ImagingOrder::create([
            'integration_request_id' => $request->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointmentId,
            'scan_type' => $scanType,
            'body_part' => $bodyPart,
            'priority' => $priority,
            'clinical_indication' => $clinicalIndication,
            'clinical_history' => $clinicalHistory,
            'special_instructions' => $specialInstructions,
            'contrast_required' => $contrastRequired,
            'status' => ImagingOrder::STATUS_DRAFT,
        ]);

        return $order;
    }

    /**
     * Submit referral to Vista Health
     */
    public function submitReferral(ImagingOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Vista Health integration is not configured or active',
            ];
        }

        try {
            $patient = $order->patient;
            $doctor = $order->doctor;

            // Prepare referral data
            $referralData = [
                'patient' => [
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                    'gender' => $patient->gender,
                    'email' => $patient->email,
                    'phone' => $patient->phone,
                    'nhs_number' => $patient->nhs_number ?? null,
                    'address' => $patient->address,
                ],
                'referrer' => [
                    'name' => $doctor->full_name,
                    'gmc_number' => $doctor->gmc_number ?? null,
                    'email' => $doctor->user->email ?? null,
                    'phone' => $doctor->phone ?? null,
                ],
                'scan_type' => $order->scan_type,
                'body_part' => $order->body_part,
                'priority' => $order->priority,
                'clinical_indication' => $order->clinical_indication,
                'clinical_history' => $order->clinical_history,
                'special_instructions' => $order->special_instructions,
                'contrast_required' => $order->contrast_required,
                'reference' => $order->order_number,
            ];

            // In production, send to Vista Health API
            // $response = $this->makeRequest('post', '/api/referrals', $referralData);

            // Simulated response
            $externalOrderId = 'VH-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Update order
            $order->markReferred($externalOrderId);

            // Update integration request
            $order->integrationRequest->markSubmitted($externalOrderId);

            $this->logInfo("Imaging referral submitted: {$order->order_number}", [
                'external_id' => $externalOrderId,
            ]);

            return [
                'success' => true,
                'message' => 'Referral submitted to Vista Health',
                'external_order_id' => $externalOrderId,
            ];
        } catch (\Exception $e) {
            $order->integrationRequest->markFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to submit referral: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Book appointment slot
     */
    public function bookAppointment(ImagingOrder $order, string $locationId, \DateTime $scheduledAt): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Vista Health integration is not configured or active',
            ];
        }

        try {
            // In production, call Vista Health API
            // $response = $this->makeRequest('post', "/api/referrals/{$order->external_order_id}/book", [
            //     'location_id' => $locationId,
            //     'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            // ]);

            // Get location name
            $services = $this->getAvailableServices();
            $location = collect($services['locations'])->firstWhere('id', $locationId);

            $order->markScheduled($scheduledAt, $location['name'] ?? $locationId);

            $this->logInfo("Scan appointment booked: {$order->order_number}", [
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i'),
                'location' => $location['name'] ?? $locationId,
            ]);

            return [
                'success' => true,
                'message' => 'Appointment booked successfully',
                'scheduled_at' => $scheduledAt->format('l, j F Y \a\t g:i A'),
                'location' => $location['name'] ?? $locationId,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to book appointment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check order status
     */
    public function checkOrderStatus(ImagingOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Vista Health integration is not configured or active',
            ];
        }

        try {
            // In production, call Vista Health API
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
     * Fetch report and images
     */
    public function fetchResults(ImagingOrder $order): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Vista Health integration is not configured or active',
            ];
        }

        try {
            // In production, fetch from Vista Health API
            return [
                'success' => true,
                'has_report' => $order->hasReport(),
                'report' => $order->report,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch results: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel referral
     */
    public function cancelOrder(ImagingOrder $order, string $reason = null): array
    {
        if (!in_array($order->status, [ImagingOrder::STATUS_DRAFT, ImagingOrder::STATUS_REFERRED, ImagingOrder::STATUS_SCHEDULED])) {
            return [
                'success' => false,
                'message' => 'Referral cannot be cancelled in current status',
            ];
        }

        try {
            // In production, cancel via Vista Health API
            if ($order->external_order_id) {
                // $response = $this->makeRequest('post', "/api/referrals/{$order->external_order_id}/cancel");
            }

            $order->update(['status' => ImagingOrder::STATUS_CANCELLED]);
            $order->integrationRequest->markCancelled($reason);

            return [
                'success' => true,
                'message' => 'Referral cancelled',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel referral: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook from Vista Health
     */
    public function processWebhook(array $payload): array
    {
        $eventType = $payload['event'] ?? 'unknown';

        switch ($eventType) {
            case 'appointment_scheduled':
                return $this->handleScheduledWebhook($payload);

            case 'scan_completed':
                return $this->handleCompletedWebhook($payload);

            case 'report_ready':
                return $this->handleReportReadyWebhook($payload);

            default:
                $this->logInfo("Unknown webhook event: {$eventType}");
                return ['success' => true, 'message' => 'Event ignored'];
        }
    }

    protected function handleScheduledWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['referral_id'] ?? null);

        if ($order) {
            $scheduledAt = isset($payload['scheduled_at'])
                ? new \DateTime($payload['scheduled_at'])
                : now();

            $order->markScheduled($scheduledAt, $payload['location'] ?? null);
            $this->logInfo("Scan scheduled: {$order->order_number}");
        }

        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function handleCompletedWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['referral_id'] ?? null);

        if ($order) {
            $order->markCompleted();
            $this->logInfo("Scan completed: {$order->order_number}");
        }

        return ['success' => true, 'message' => 'Status updated'];
    }

    protected function handleReportReadyWebhook(array $payload): array
    {
        $order = $this->findOrderByExternalId($payload['referral_id'] ?? null);

        if ($order) {
            $report = $payload['report'] ?? '';
            $pdfUrl = $payload['pdf_url'] ?? null;
            $images = $payload['images'] ?? null;

            $order->markReported($report, $pdfUrl, $images);
            $order->integrationRequest->markCompleted(['report' => $report]);

            $this->logInfo("Report ready: {$order->order_number}");
            // TODO: Notify doctor of report
        }

        return ['success' => true, 'message' => 'Report received'];
    }

    protected function findOrderByExternalId(?string $externalId): ?ImagingOrder
    {
        if (!$externalId) return null;
        return ImagingOrder::where('external_order_id', $externalId)->first();
    }

    /**
     * Get required config fields
     */
    protected function getRequiredConfigFields(): array
    {
        return [
            'api_key' => 'API Key',
            'clinic_id' => 'Clinic ID',
        ];
    }

    /**
     * Get config form fields for admin UI
     */
    public function getConfigFormFields(): array
    {
        return [
            [
                'name' => 'clinic_id',
                'label' => 'Clinic ID',
                'type' => 'text',
                'required' => true,
                'help' => 'Your Vista Health clinic identifier',
            ],
            [
                'name' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'help' => 'Your Vista Health API key',
            ],
            [
                'name' => 'api_secret',
                'label' => 'API Secret',
                'type' => 'password',
                'required' => false,
                'help' => 'Your Vista Health API secret (if required)',
            ],
            [
                'name' => 'sandbox_url',
                'label' => 'Sandbox URL',
                'type' => 'url',
                'required' => false,
                'default' => 'https://sandbox-api.vistahealth.co.uk',
                'help' => 'Vista Health sandbox API URL',
            ],
            [
                'name' => 'production_url',
                'label' => 'Production URL',
                'type' => 'url',
                'required' => false,
                'default' => 'https://api.vistahealth.co.uk',
                'help' => 'Vista Health production API URL',
            ],
            [
                'name' => 'webhook_secret',
                'label' => 'Webhook Secret',
                'type' => 'password',
                'required' => false,
                'help' => 'Secret for validating Vista Health webhooks',
            ],
            [
                'name' => 'default_location',
                'label' => 'Default Location',
                'type' => 'text',
                'required' => false,
                'help' => 'Default Vista Health location ID for bookings',
            ],
        ];
    }
}
