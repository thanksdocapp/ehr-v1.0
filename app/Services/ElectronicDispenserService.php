<?php

namespace App\Services;

use App\Models\Prescription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ElectronicDispenserService
{
    /**
     * Base URL for the electronic dispenser API
     */
    protected $baseUrl;

    /**
     * API credentials
     */
    protected $apiKey;
    protected $apiSecret;

    /**
     * Whether the integration is enabled
     */
    protected $enabled;

    /**
     * Timeout for API requests (in seconds)
     */
    protected $timeout = 30;

    public function __construct()
    {
        $this->baseUrl = config('hospital.dispenser_api.base_url', '');
        $this->apiKey = config('hospital.dispenser_api.api_key', '');
        $this->apiSecret = config('hospital.dispenser_api.api_secret', '');
        $this->enabled = config('hospital.dispenser_api.enabled', false);
        $this->timeout = config('hospital.dispenser_api.timeout', 30);
    }

    /**
     * Check if the electronic dispenser integration is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->baseUrl) && !empty($this->apiKey);
    }

    /**
     * Send prescription to electronic dispenser
     *
     * @param Prescription $prescription
     * @return array
     * @throws Exception
     */
    public function sendPrescription(Prescription $prescription): array
    {
        if (!$this->isEnabled()) {
            Log::info('Electronic dispenser integration is disabled', [
                'prescription_id' => $prescription->id
            ]);
            return [
                'success' => false,
                'message' => 'Electronic dispenser integration is disabled',
                'sent' => false
            ];
        }

        try {
            // Load relationships
            $prescription->load(['patient', 'doctor', 'appointment', 'medicalRecord']);

            // Format prescription data for the API
            $prescriptionData = $this->formatPrescriptionData($prescription);

            // Make API request
            $response = $this->makeApiRequest('POST', '/prescriptions', $prescriptionData);

            // Log success
            Log::info('Prescription sent to electronic dispenser successfully', [
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'api_response' => $response
            ]);

            return [
                'success' => true,
                'message' => 'Prescription sent to electronic dispenser successfully',
                'sent' => true,
                'api_response' => $response
            ];

        } catch (Exception $e) {
            // Log error but don't fail the approval process
            Log::error('Failed to send prescription to electronic dispenser', [
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send prescription to electronic dispenser: ' . $e->getMessage(),
                'sent' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format prescription data for the electronic dispenser API
     *
     * @param Prescription $prescription
     * @return array
     */
    protected function formatPrescriptionData(Prescription $prescription): array
    {
        $medications = $prescription->medications;
        if (is_string($medications)) {
            $medications = json_decode($medications, true) ?? [];
        }
        if (!is_array($medications)) {
            $medications = [];
        }

        return [
            'prescription_id' => $prescription->id,
            'prescription_number' => $prescription->prescription_number ?? 'RX' . $prescription->id,
            'status' => $prescription->status,
            'prescription_date' => $prescription->prescription_date 
                ? $prescription->prescription_date->format('Y-m-d') 
                : now()->format('Y-m-d'),
            'approved_at' => $prescription->approved_at 
                ? $prescription->approved_at->format('Y-m-d H:i:s') 
                : now()->format('Y-m-d H:i:s'),
            
            // Patient information
            'patient' => [
                'id' => $prescription->patient->id,
                'patient_number' => $prescription->patient->patient_number ?? 'P' . $prescription->patient->id,
                'first_name' => $prescription->patient->first_name,
                'last_name' => $prescription->patient->last_name,
                'full_name' => $prescription->patient->first_name . ' ' . $prescription->patient->last_name,
                'date_of_birth' => $prescription->patient->date_of_birth 
                    ? $prescription->patient->date_of_birth->format('Y-m-d') 
                    : null,
                'gender' => $prescription->patient->gender,
                'phone' => $prescription->patient->phone,
                'email' => $prescription->patient->email,
            ],
            
            // Doctor information
            'doctor' => [
                'id' => $prescription->doctor->id ?? null,
                'name' => $prescription->doctor ? 
                    ($prescription->doctor->first_name . ' ' . $prescription->doctor->last_name) : 
                    'Unknown Doctor',
                'specialization' => $prescription->doctor->specialization ?? null,
                'license_number' => $prescription->doctor->license_number ?? null,
            ],
            
            // Medications
            'medications' => array_map(function($med) {
                return [
                    'name' => $med['name'] ?? 'Unknown Medication',
                    'dosage' => $med['dosage'] ?? null,
                    'frequency' => $med['frequency'] ?? null,
                    'duration' => $med['duration'] ?? null,
                    'quantity' => $med['quantity'] ?? null,
                    'instructions' => $med['instructions'] ?? null,
                ];
            }, $medications),
            
            // Additional information
            'diagnosis' => $prescription->diagnosis,
            'notes' => $prescription->notes,
            'follow_up_date' => $prescription->follow_up_date 
                ? $prescription->follow_up_date->format('Y-m-d') 
                : null,
            'refills_allowed' => $prescription->refills_allowed ?? 0,
            'expiry_date' => $prescription->expiry_date 
                ? $prescription->expiry_date->format('Y-m-d') 
                : null,
        ];
    }

    /**
     * Make API request to electronic dispenser
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array
     * @throws Exception
     */
    protected function makeApiRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        // Prepare headers
        $headers = $this->getDefaultHeaders();

        // Log request
        Log::info('Sending request to electronic dispenser API', [
            'method' => $method,
            'url' => $url,
            'endpoint' => $endpoint,
            'data' => $data,
            'headers' => array_keys($headers) // Don't log sensitive headers
        ]);

        try {
            // Make HTTP request
            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->{strtolower($method)}($url, $data);

            $statusCode = $response->status();
            $responseData = $response->json() ?? [];
            $responseBody = $response->body();

            // Log response
            Log::info('Electronic dispenser API response', [
                'method' => $method,
                'url' => $url,
                'status_code' => $statusCode,
                'response' => $responseData
            ]);

            // Check if request was successful
            if (!$response->successful()) {
                throw new Exception(
                    "API request failed with status {$statusCode}: " . $responseBody,
                    $statusCode
                );
            }

            return $responseData;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection failed to electronic dispenser API: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Electronic dispenser API error: " . $e->getMessage());
        }
    }

    /**
     * Get default headers for API requests
     *
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => getAppName() . '/' . getAppVersion(),
        ];

        // Add authentication headers based on configuration
        $authType = config('hospital.dispenser_api.auth_type', 'bearer'); // 'bearer', 'api_key', 'basic'

        switch ($authType) {
            case 'bearer':
                if (!empty($this->apiKey)) {
                    $headers['Authorization'] = 'Bearer ' . $this->apiKey;
                }
                break;

            case 'api_key':
                if (!empty($this->apiKey)) {
                    $headers['X-API-Key'] = $this->apiKey;
                    if (!empty($this->apiSecret)) {
                        $headers['X-API-Secret'] = $this->apiSecret;
                    }
                }
                break;

            case 'basic':
                if (!empty($this->apiKey) && !empty($this->apiSecret)) {
                    $headers['Authorization'] = 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret);
                }
                break;
        }

        return $headers;
    }

    /**
     * Get prescription status from electronic dispenser
     *
     * @param string $prescriptionId
     * @return array
     * @throws Exception
     */
    public function getPrescriptionStatus(string $prescriptionId): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Electronic dispenser integration is disabled'
            ];
        }

        try {
            $response = $this->makeApiRequest('GET', "/prescriptions/{$prescriptionId}/status");
            return [
                'success' => true,
                'status' => $response['status'] ?? null,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Failed to get prescription status from electronic dispenser', [
                'prescription_id' => $prescriptionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update prescription status in electronic dispenser
     *
     * @param string $prescriptionId
     * @param string $status
     * @return array
     * @throws Exception
     */
    public function updatePrescriptionStatus(string $prescriptionId, string $status): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Electronic dispenser integration is disabled'
            ];
        }

        try {
            $response = $this->makeApiRequest('PUT', "/prescriptions/{$prescriptionId}/status", [
                'status' => $status,
                'updated_at' => now()->toIso8601String()
            ]);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Failed to update prescription status in electronic dispenser', [
                'prescription_id' => $prescriptionId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

