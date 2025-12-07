<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationModule;
use App\Models\IntegrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegrationsController extends Controller
{
    /**
     * Display list of integration modules
     */
    public function index()
    {
        $modules = IntegrationModule::orderBy('name')->get();

        // Group by type
        $groupedModules = $modules->groupBy('type');

        // Get recent activity
        $recentRequests = IntegrationRequest::with(['integrationModule', 'patient'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.integrations.index', [
            'modules' => $modules,
            'groupedModules' => $groupedModules,
            'recentRequests' => $recentRequests,
        ]);
    }

    /**
     * Show integration module configuration
     */
    public function show(IntegrationModule $module)
    {
        $service = $module->getService();
        $configFields = $service ? $service->getConfigFormFields() : [];

        // Get recent requests for this module
        $recentRequests = IntegrationRequest::where('integration_module_id', $module->id)
            ->with(['patient', 'doctor'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.integrations.show', [
            'module' => $module,
            'configFields' => $configFields,
            'recentRequests' => $recentRequests,
        ]);
    }

    /**
     * Update integration configuration
     */
    public function update(Request $request, IntegrationModule $module)
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
            'environment' => 'in:sandbox,production',
            'config' => 'array',
            'settings' => 'array',
        ]);

        // Update config
        if ($request->has('config')) {
            $config = $module->config ?? [];
            foreach ($request->input('config', []) as $key => $value) {
                // Only update non-empty values (preserve existing if empty)
                if ($value !== null && $value !== '') {
                    $config[$key] = $value;
                }
            }
            $module->config = $config;
        }

        // Update settings
        if ($request->has('settings')) {
            $settings = $module->settings ?? [];
            foreach ($request->input('settings', []) as $key => $value) {
                $settings[$key] = $value;
            }
            $module->settings = $settings;
        }

        // Update other fields
        if ($request->has('is_active')) {
            $module->is_active = $request->boolean('is_active');
        }

        if ($request->has('environment')) {
            $module->environment = $request->input('environment');
        }

        // Check if configured
        $service = $module->getService();
        if ($service) {
            $errors = $service->validateConfig();
            $module->is_configured = empty($errors);
        }

        $module->save();

        return redirect()->route('admin.integrations.show', $module)
            ->with('success', 'Integration settings updated successfully.');
    }

    /**
     * Test integration connection
     */
    public function testConnection(IntegrationModule $module)
    {
        $service = $module->getService();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found for this integration',
            ]);
        }

        $result = $service->testConnection();

        if ($result['success']) {
            $module->clearError();
            $module->updateLastSync();
        }

        return response()->json($result);
    }

    /**
     * Toggle integration status
     */
    public function toggleStatus(IntegrationModule $module)
    {
        if (!$module->is_configured && !$module->is_active) {
            return redirect()->route('admin.integrations.show', $module)
                ->with('error', 'Please configure the integration before enabling it.');
        }

        $module->is_active = !$module->is_active;
        $module->save();

        $status = $module->is_active ? 'enabled' : 'disabled';

        return redirect()->route('admin.integrations.show', $module)
            ->with('success', "Integration {$status} successfully.");
    }

    /**
     * Handle webhook from integration provider
     */
    public function webhook(Request $request, string $slug)
    {
        $module = IntegrationModule::where('slug', $slug)->first();

        if (!$module || !$module->isReady()) {
            Log::warning("Webhook received for inactive/unknown module: {$slug}");
            return response()->json(['error' => 'Module not found or inactive'], 404);
        }

        try {
            // Log webhook
            $webhook = $module->webhooks()->create([
                'event_type' => $request->input('event', 'unknown'),
                'payload' => $request->all(),
                'status' => 'received',
            ]);

            // Process webhook
            $service = $module->getService();
            if ($service) {
                $result = $service->processWebhook($request->all());

                $webhook->update([
                    'status' => $result['success'] ? 'processed' : 'failed',
                    'error_message' => $result['message'] ?? null,
                    'processed_at' => now(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Webhook processing error for {$slug}: {$e->getMessage()}");

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * View integration requests/orders
     */
    public function requests(IntegrationModule $module, Request $request)
    {
        $query = IntegrationRequest::where('integration_module_id', $module->id)
            ->with(['patient', 'doctor']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
            }
        }

        $requests = $query->latest()->paginate(25);

        // Get stats
        $stats = [
            'total' => IntegrationRequest::where('integration_module_id', $module->id)->count(),
            'pending' => IntegrationRequest::where('integration_module_id', $module->id)->where('status', 'pending')->count(),
            'processing' => IntegrationRequest::where('integration_module_id', $module->id)->whereIn('status', ['submitted', 'processing'])->count(),
            'completed' => IntegrationRequest::where('integration_module_id', $module->id)->where('status', 'completed')->count(),
            'failed' => IntegrationRequest::where('integration_module_id', $module->id)->where('status', 'failed')->count(),
            'cancelled' => IntegrationRequest::where('integration_module_id', $module->id)->where('status', 'cancelled')->count(),
        ];

        return view('admin.integrations.requests', [
            'module' => $module,
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }
}
