<?php

namespace App\Services\Integrations;

use App\Models\IntegrationRequest;
use App\Models\IntegrationModule;
use App\Models\PrescriptionOrder;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuincyService extends BaseIntegrationService
{
    /**
     * Get the API base URL based on environment
     */
    protected function getBaseUrl(): string
    {
        if ($this->module->environment === 'production') {
            return $this->config['production_url'] ?? 'https://app.quincy.health/api';
        }
        return $this->config['sandbox_url'] ?? 'https://app.quincy.health/api';
    }

    /**
     * Test connection to Quincy API using whoami endpoint
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

            // Call the whoami endpoint to verify connection
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->get($this->getBaseUrl() . '/v1/user/whoami');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful - Authenticated as ' . ($response->json('email') ?? 'Unknown'),
                    'environment' => $this->module->environment,
                ];
            }

            return [
                'success' => false,
                'message' => 'Authentication failed: ' . ($response->json('message') ?? 'Invalid API key'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if Quincy integration is successfully configured and connected
     * Returns true if integration is active, configured, and connection test passes
     */
    public function isQuincyIntegrationSuccessful(): bool
    {
        try {
            // Check if module is ready (active and configured)
            if (!$this->isReady()) {
                return false;
            }

            // Test connection - if successful, return true
            $testResult = $this->testConnection();
            return $testResult['success'] === true;
        } catch (\Exception $e) {
            // Log error but don't throw - return false instead
            $this->logError("Quincy integration check failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get prescription delivery status for a doctor
     * Returns statistics about successful and failed prescription deliveries to Quincy
     * 
     * @param int $doctorId
     * @return array Contains success status, counts, and recent orders
     */
    public function getDoctorPrescriptionDeliveryStatus(int $doctorId): array
    {
        try {
            if (!$this->isReady()) {
                return [
                    'success' => false,
                    'available' => false,
                    'message' => 'Quincy integration is not configured or active',
                    'stats' => [
                        'total' => 0,
                        'successful' => 0,
                        'failed' => 0,
                        'pending' => 0,
                    ],
                    'recent_failed' => [],
                ];
            }

            // Use the module instance from this service
            $quincyModule = $this->module;

            // Get all prescription orders for this doctor through Quincy
            $orders = PrescriptionOrder::where('doctor_id', $doctorId)
                ->whereHas('integrationRequest', function ($q) use ($quincyModule) {
                    $q->where('integration_module_id', $quincyModule->id);
                })
                ->with(['patient', 'prescription', 'integrationRequest'])
                ->latest()
                ->get();

            // Count by status
            $total = $orders->count();
            $successful = $orders->whereIn('status', [
                PrescriptionOrder::STATUS_SUBMITTED,
                PrescriptionOrder::STATUS_ACCEPTED,
                PrescriptionOrder::STATUS_DISPENSING,
                PrescriptionOrder::STATUS_READY,
                PrescriptionOrder::STATUS_COLLECTED,
                PrescriptionOrder::STATUS_DELIVERED,
            ])->count();

            // Failed: rejected orders or integration requests that failed
            $failed = $orders->filter(function ($order) {
                return $order->status === PrescriptionOrder::STATUS_REJECTED ||
                       ($order->integrationRequest && $order->integrationRequest->status === IntegrationRequest::STATUS_FAILED);
            })->count();

            $pending = $orders->whereIn('status', [
                PrescriptionOrder::STATUS_DRAFT,
            ])->count();

            // Get recent failed orders (last 5)
            $recentFailed = $orders->filter(function ($order) {
                return $order->status === PrescriptionOrder::STATUS_REJECTED ||
                       ($order->integrationRequest && $order->integrationRequest->status === IntegrationRequest::STATUS_FAILED);
            })
            ->take(5)
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'patient_name' => $order->patient ? $order->patient->full_name : 'Unknown',
                    'status' => $order->status,
                    'rejection_reason' => $order->rejection_reason ?? ($order->integrationRequest->notes ?? 'Unknown error'),
                    'created_at' => formatDateTimeUk($order->created_at),
                    'prescription_id' => $order->prescription_id,
                ];
            })
            ->values();

            // Overall success rate
            $successRate = $total > 0 ? round(($successful / $total) * 100, 1) : 0;

            return [
                'success' => true,
                'available' => true,
                'message' => $total > 0 ? "{$successful} of {$total} prescriptions delivered successfully" : 'No prescriptions sent to Quincy yet',
                'stats' => [
                    'total' => $total,
                    'successful' => $successful,
                    'failed' => $failed,
                    'pending' => $pending,
                    'success_rate' => $successRate,
                ],
                'recent_failed' => $recentFailed,
                'has_failures' => $failed > 0,
            ];
        } catch (\Exception $e) {
            $this->logError("Failed to get doctor prescription delivery status: {$e->getMessage()}");
            return [
                'success' => false,
                'available' => false,
                'message' => 'Error checking delivery status: ' . $e->getMessage(),
                'stats' => [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'success_rate' => 0,
                ],
                'recent_failed' => [],
                'has_failures' => false,
            ];
        }
    }

    /**
     * Search medicines by name or PIP code
     */
    public function searchMedicines(string $query, int $limit = 100, bool $expandPacks = false): array
    {
        if (!$this->isReady() || strlen($query) < 3) {
            return [];
        }

        try {
            $response = Http::withToken($this->getApiKey())
                ->timeout(15)
                ->get($this->getBaseUrl() . '/v1/medicines', [
                    'query' => $query,
                    'limit' => min($limit, 100),
                    'expand_packs' => $expandPacks ? 1 : 0,
                ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            $this->logError("Medicine search failed: " . $response->body());
            return [];
        } catch (\Exception $e) {
            $this->logError("Medicine search exception: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Get available pharmacies - Quincy doesn't have pharmacy search, prescriptions are fulfilled by partner pharmacies
     */
    public function getAvailableServices(): array
    {
        return [
            ['id' => 'quincy_network', 'name' => 'Quincy Pharmacy Network', 'type' => 'deliver_customer'],
            ['id' => 'clinic_delivery', 'name' => 'Deliver to Clinic', 'type' => 'deliver_clinic'],
            ['id' => 'token_issue', 'name' => 'Issue Token (Patient Chooses Pharmacy)', 'type' => 'issue_token'],
        ];
    }

    /**
     * Create or find patient in Quincy
     */
    public function createOrUpdatePatient(Patient $patient): ?string
    {
        if (!$this->isReady()) {
            return null;
        }

        try {
            // Check if patient exists by source_id
            $sourceId = 'EHR-' . $patient->id;

            $searchResponse = Http::withToken($this->getApiKey())
                ->timeout(15)
                ->get($this->getBaseUrl() . '/v1/patients', [
                    'query' => $patient->email,
                    'limit' => 10,
                ]);

            if ($searchResponse->successful()) {
                $patients = $searchResponse->json('data') ?? [];
                foreach ($patients as $p) {
                    if (($p['source_id'] ?? '') === $sourceId) {
                        return $p['id'];
                    }
                }
            }

            // Create new patient
            $patientData = [
                'source_id' => $sourceId,
                'firstname' => $patient->first_name,
                'lastname' => $patient->last_name,
                'dob' => $patient->date_of_birth->format('Y-m-d'),
                'sex' => $this->mapGender($patient->gender),
                'email' => $patient->email,
                'phone' => $patient->phone ?? '',
                'address_line1' => $patient->address ?? '',
                'city' => $patient->city ?? 'London',
                'postcode' => $patient->postal_code ?? '',
                'country' => 'United Kingdom',
            ];

            if ($patient->nhs_number) {
                $patientData['nhs_number'] = $patient->nhs_number;
            }

            $response = Http::withToken($this->getApiKey())
                ->timeout(15)
                ->post($this->getBaseUrl() . '/v1/patients', $patientData);

            if ($response->successful()) {
                return $response->json('id');
            }

            $this->logError("Failed to create patient: " . $response->body());
            return null;
        } catch (\Exception $e) {
            $this->logError("Patient creation exception: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Map gender to Quincy format
     */
    protected function mapGender(?string $gender): string
    {
        return match (strtolower($gender ?? '')) {
            'male', 'm' => 'Male',
            'female', 'f' => 'Female',
            default => 'Other',
        };
    }

    /**
     * Create prescription order
     */
    public function createOrder(
        Patient $patient,
        Doctor $doctor,
        array $medications,
        string $deliveryOption = 'deliver_customer',
        string $payee = 'patient',
        ?int $prescriptionId = null,
        ?string $clinicalNotes = null,
        array $repeatOptions = []
    ): PrescriptionOrder {
        // Create integration request
        $request = $this->createRequest('create_prescription', [
            'patient_id' => $patient->id,
            'medications' => $medications,
            'delivery_option' => $deliveryOption,
        ], $patient->id, $doctor->id);

        // Map delivery option to method
        $deliveryMethod = match ($deliveryOption) {
            'deliver_customer' => 'delivery',
            'deliver_clinic' => 'collection',
            'issue_token' => 'collection',
            default => 'collection',
        };

        // Create prescription order
        $order = PrescriptionOrder::create([
            'integration_request_id' => $request->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'prescription_id' => $prescriptionId,
            'medications' => $medications,
            'delivery_method' => $deliveryMethod,
            'delivery_address' => $deliveryOption === 'deliver_customer' ? $patient->address : null,
            'status' => PrescriptionOrder::STATUS_DRAFT,
        ]);

        return $order;
    }

    /**
     * Submit prescription to Quincy
     */
    public function submitOrder(PrescriptionOrder $order, string $deliveryOption = 'deliver_customer', string $payee = 'patient'): array
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

            // Build prescription lines from medications
            $lines = [];
            foreach ($order->medications as $medication) {
                $line = [
                    'quantity' => $medication['quantity'] ?? 1,
                    'instructions' => $medication['instructions'] ?? $medication['dosage'] ?? 'As directed',
                ];

                // Use drug_id if available, otherwise custom_drug_name
                if (!empty($medication['drug_id'])) {
                    $line['drug_id'] = $medication['drug_id'];
                } else {
                    $line['custom_drug_name'] = $medication['name'] ?? $medication['medication_name'] ?? 'Unknown medication';
                }

                if (!empty($medication['notes'])) {
                    $line['notes_to_pharmacist'] = $medication['notes'];
                }

                $lines[] = $line;
            }

            // Build prescription data according to Quincy API
            $prescriptionData = [
                'patient' => [
                    'source_id' => 'EHR-' . $patient->id,
                    'firstname' => $patient->first_name,
                    'lastname' => $patient->last_name,
                    'dob' => $patient->date_of_birth->format('Y-m-d'),
                    'sex' => $this->mapGender($patient->gender),
                    'email' => $patient->email,
                    'phone' => $patient->phone ?? '',
                    'address_line1' => $patient->address ?? '',
                    'city' => $patient->city ?? 'London',
                    'postcode' => $patient->postal_code ?? '',
                    'country' => 'United Kingdom',
                ],
                'lines' => $lines,
                'delivery_option' => $deliveryOption,
                'payee' => $payee,
                'repeat_option' => 'no_repeats',
            ];

            // Add NHS number if available
            if ($patient->nhs_number) {
                $prescriptionData['patient']['nhs_number'] = $patient->nhs_number;
            }

            // Add prescriber fee if configured
            if (!empty($this->config['prescriber_fee'])) {
                $prescriptionData['prescriber_fee'] = (float) $this->config['prescriber_fee'];
            }

            // Submit to Quincy API
            $response = Http::withToken($this->getApiKey())
                ->timeout(30)
                ->post($this->getBaseUrl() . '/v1/prescriptions', $prescriptionData);

            if ($response->successful()) {
                $data = $response->json();
                $externalId = $data['id'] ?? null;
                $token = $data['token'] ?? null;

                // Update order with external reference
                $order->external_order_id = $externalId;
                $order->status = PrescriptionOrder::STATUS_SUBMITTED;
                $order->save();

                // Update integration request
                $order->integrationRequest->markSubmitted($externalId);

                $this->logInfo("Prescription submitted: {$order->id}", [
                    'external_id' => $externalId,
                    'token' => $token,
                ]);

                return [
                    'success' => true,
                    'message' => 'Prescription submitted successfully',
                    'external_id' => $externalId,
                    'token' => $token,
                    'status' => $data['status'] ?? 'Submitted',
                ];
            }

            $errorMessage = $response->json('message') ?? $response->body();
            $order->integrationRequest->markFailed($errorMessage);

            $this->logError("Prescription submission failed: " . $errorMessage);

            return [
                'success' => false,
                'message' => 'Failed to submit prescription: ' . $errorMessage,
            ];
        } catch (\Exception $e) {
            $order->integrationRequest->markFailed($e->getMessage());
            $this->logError("Prescription submission exception: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'Failed to submit prescription: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check prescription status
     */
    public function checkOrderStatus(PrescriptionOrder $order): array
    {
        if (!$this->isReady() || !$order->external_order_id) {
            return [
                'success' => false,
                'message' => 'Cannot check status - integration not ready or no external ID',
            ];
        }

        try {
            $response = Http::withToken($this->getApiKey())
                ->timeout(15)
                ->get($this->getBaseUrl() . '/v1/prescriptions/' . $order->external_order_id);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'Unknown';

                // Map Quincy status to our status
                $mappedStatus = $this->mapQuincyStatus($status);
                if ($mappedStatus !== $order->status) {
                    $order->status = $mappedStatus;
                    $order->save();
                }

                return [
                    'success' => true,
                    'status' => $status,
                    'mapped_status' => $mappedStatus,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to retrieve status: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to check status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Map Quincy status to our internal status
     */
    protected function mapQuincyStatus(string $quincyStatus): string
    {
        return match (strtolower($quincyStatus)) {
            'awaiting payment', 'pending' => PrescriptionOrder::STATUS_SUBMITTED,
            'paid', 'accepted', 'processing' => PrescriptionOrder::STATUS_ACCEPTED,
            'dispensing' => PrescriptionOrder::STATUS_DISPENSING,
            'ready', 'ready for collection' => PrescriptionOrder::STATUS_READY,
            'dispatched', 'shipped' => PrescriptionOrder::STATUS_READY,
            'collected' => PrescriptionOrder::STATUS_COLLECTED,
            'delivered' => PrescriptionOrder::STATUS_DELIVERED,
            'cancelled' => PrescriptionOrder::STATUS_CANCELLED,
            'rejected' => PrescriptionOrder::STATUS_REJECTED,
            default => $quincyStatus,
        };
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
            // Note: Quincy API may not support cancellation - check their docs
            // For now, just update local status
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
        $eventType = $payload['event'] ?? $payload['type'] ?? 'unknown';
        $prescriptionId = $payload['prescription_id'] ?? $payload['id'] ?? null;

        $this->logInfo("Quincy webhook received: {$eventType}", ['prescription_id' => $prescriptionId]);

        $order = $this->findOrderByExternalId($prescriptionId);
        if (!$order) {
            $this->logWarning("Webhook received for unknown prescription: {$prescriptionId}");
            return ['success' => true, 'message' => 'Prescription not found locally'];
        }

        // Get status from payload
        $status = $payload['status'] ?? null;
        if ($status) {
            $mappedStatus = $this->mapQuincyStatus($status);
            $order->status = $mappedStatus;
            $order->save();

            // Mark integration request as completed if final status
            if (in_array($mappedStatus, [PrescriptionOrder::STATUS_COLLECTED, PrescriptionOrder::STATUS_DELIVERED])) {
                $order->integrationRequest->markCompleted();
            }
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
        ];
    }

    /**
     * Get config form fields for admin UI
     */
    public function getConfigFormFields(): array
    {
        return [
            [
                'name' => 'api_key',
                'label' => 'API Key (Bearer Token)',
                'type' => 'password',
                'required' => true,
                'help' => 'Your Quincy API key from the API Keys page in your Quincy dashboard',
            ],
            [
                'name' => 'prescriber_fee',
                'label' => 'Prescriber Fee (£)',
                'type' => 'number',
                'required' => false,
                'help' => 'Optional fee to add to prescriptions',
            ],
            [
                'name' => 'default_delivery_option',
                'label' => 'Default Delivery Option',
                'type' => 'select',
                'required' => false,
                'options' => [
                    'deliver_customer' => 'Deliver to Patient',
                    'deliver_clinic' => 'Deliver to Clinic',
                    'issue_token' => 'Issue Token (Patient Chooses)',
                ],
                'help' => 'Default delivery method for prescriptions',
            ],
            [
                'name' => 'default_payee',
                'label' => 'Default Payee',
                'type' => 'select',
                'required' => false,
                'options' => [
                    'patient' => 'Patient Pays',
                    'clinic' => 'Clinic Pays',
                ],
                'help' => 'Who pays for the prescription by default',
            ],
            [
                'name' => 'sandbox_url',
                'label' => 'API URL (Sandbox)',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://app.quincy.health/api',
                'help' => 'Quincy API URL for sandbox testing',
            ],
            [
                'name' => 'production_url',
                'label' => 'API URL (Production)',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://app.quincy.health/api',
                'help' => 'Quincy API URL for production',
            ],
        ];
    }
}
