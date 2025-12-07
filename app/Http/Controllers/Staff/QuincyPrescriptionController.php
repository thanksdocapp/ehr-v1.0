<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\IntegrationModule;
use App\Models\Prescription;
use App\Models\PrescriptionOrder;
use App\Services\Integrations\QuincyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuincyPrescriptionController extends Controller
{
    /**
     * Check if Quincy integration is available
     */
    protected function getQuincyModule(): ?IntegrationModule
    {
        return IntegrationModule::where('slug', 'quincy')
            ->where('is_active', true)
            ->where('is_configured', true)
            ->first();
    }

    /**
     * Get QuincyService instance
     */
    protected function getQuincyService(): ?QuincyService
    {
        $module = $this->getQuincyModule();
        return $module ? $module->getService() : null;
    }

    /**
     * Show the Send to Quincy form/modal data
     */
    public function showSendForm(Prescription $prescription)
    {
        $module = $this->getQuincyModule();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
            ], 400);
        }

        $service = $module->getService();

        // Check if prescription already has a Quincy order
        $existingOrder = PrescriptionOrder::where('prescription_id', $prescription->id)
            ->whereHas('integrationRequest', function ($q) use ($module) {
                $q->where('integration_module_id', $module->id);
            })
            ->latest()
            ->first();

        // Get delivery options
        $deliveryOptions = $service->getAvailableServices();

        // Get config defaults
        $defaultDeliveryOption = $module->config['default_delivery_option'] ?? 'deliver_customer';
        $defaultPayee = $module->config['default_payee'] ?? 'patient';

        return response()->json([
            'success' => true,
            'prescription' => [
                'id' => $prescription->id,
                'patient_name' => $prescription->patient->full_name,
                'medications' => $prescription->medications,
                'status' => $prescription->status,
            ],
            'existing_order' => $existingOrder ? [
                'id' => $existingOrder->id,
                'external_id' => $existingOrder->external_order_id,
                'status' => $existingOrder->status,
                'created_at' => $existingOrder->created_at->format('d M Y H:i'),
            ] : null,
            'delivery_options' => $deliveryOptions,
            'defaults' => [
                'delivery_option' => $defaultDeliveryOption,
                'payee' => $defaultPayee,
            ],
        ]);
    }

    /**
     * Send prescription to Quincy
     */
    public function send(Request $request, Prescription $prescription)
    {
        $request->validate([
            'delivery_option' => 'required|in:deliver_customer,deliver_clinic,issue_token',
            'payee' => 'required|in:patient,clinic',
        ]);

        $module = $this->getQuincyModule();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
            ], 400);
        }

        // Check prescription status - must be approved
        if (!in_array($prescription->status, ['approved', 'dispensed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription must be approved before sending to Quincy',
            ], 400);
        }

        // Check for existing pending order
        $existingOrder = PrescriptionOrder::where('prescription_id', $prescription->id)
            ->whereIn('status', [
                PrescriptionOrder::STATUS_DRAFT,
                PrescriptionOrder::STATUS_SUBMITTED,
                PrescriptionOrder::STATUS_ACCEPTED,
                PrescriptionOrder::STATUS_DISPENSING,
            ])
            ->whereHas('integrationRequest', function ($q) use ($module) {
                $q->where('integration_module_id', $module->id);
            })
            ->first();

        if ($existingOrder) {
            return response()->json([
                'success' => false,
                'message' => 'This prescription already has a pending Quincy order (Order #' . $existingOrder->order_number . ')',
                'order' => [
                    'id' => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'status' => $existingOrder->status,
                ],
            ], 400);
        }

        try {
            $service = $module->getService();
            $patient = $prescription->patient;
            $doctor = $prescription->doctor;

            // Prepare medications with quantities
            $medications = collect($prescription->medications)->map(function ($med) {
                return [
                    'name' => $med['name'] ?? 'Unknown',
                    'dosage' => $med['dosage'] ?? '',
                    'frequency' => $med['frequency'] ?? '',
                    'duration' => $med['duration'] ?? '',
                    'instructions' => $med['instructions'] ?? ($med['dosage'] . ' ' . ($med['frequency'] ?? '')),
                    'quantity' => $med['quantity'] ?? 1,
                    'drug_id' => $med['drug_id'] ?? null,
                    'form' => $med['form'] ?? null,
                ];
            })->toArray();

            // Create the order
            $order = $service->createOrder(
                $patient,
                $doctor,
                $medications,
                $request->delivery_option,
                $request->payee,
                $prescription->id
            );

            // Submit to Quincy
            $result = $service->submitOrder(
                $order,
                $request->delivery_option,
                $request->payee
            );

            if ($result['success']) {
                Log::info("Prescription {$prescription->id} sent to Quincy successfully", [
                    'order_id' => $order->id,
                    'external_id' => $result['external_id'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Prescription sent to Quincy successfully',
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'external_id' => $result['external_id'] ?? null,
                        'status' => $order->status,
                        'token' => $result['token'] ?? null,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to send prescription to Quincy',
            ], 500);

        } catch (\Exception $e) {
            Log::error("Error sending prescription to Quincy: " . $e->getMessage(), [
                'prescription_id' => $prescription->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending to Quincy: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status of a Quincy order
     */
    public function checkStatus(PrescriptionOrder $order)
    {
        $module = $this->getQuincyModule();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
            ], 400);
        }

        $service = $module->getService();
        $result = $service->checkOrderStatus($order);

        return response()->json($result);
    }

    /**
     * Cancel a Quincy order
     */
    public function cancel(Request $request, PrescriptionOrder $order)
    {
        $module = $this->getQuincyModule();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
            ], 400);
        }

        $service = $module->getService();
        $result = $service->cancelOrder($order, $request->input('reason'));

        return response()->json($result);
    }

    /**
     * Search medicines via Quincy API
     */
    public function searchMedicines(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3|max:100',
        ]);

        $module = $this->getQuincyModule();

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Quincy integration is not configured or active',
                'data' => [],
            ]);
        }

        $service = $module->getService();
        $results = $service->searchMedicines(
            $request->query('query'),
            $request->query('limit', 20),
            $request->boolean('expand_packs', false)
        );

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get Quincy orders for a prescription
     */
    public function getOrders(Prescription $prescription)
    {
        $module = $this->getQuincyModule();

        if (!$module) {
            return response()->json([
                'success' => false,
                'orders' => [],
            ]);
        }

        $orders = PrescriptionOrder::where('prescription_id', $prescription->id)
            ->whereHas('integrationRequest', function ($q) use ($module) {
                $q->where('integration_module_id', $module->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'external_id' => $order->external_order_id,
                    'status' => $order->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                    'delivery_method' => $order->delivery_method,
                    'created_at' => $order->created_at->format('d M Y H:i'),
                    'can_cancel' => $order->canBeCancelled(),
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'quincy_active' => true,
        ]);
    }
}
