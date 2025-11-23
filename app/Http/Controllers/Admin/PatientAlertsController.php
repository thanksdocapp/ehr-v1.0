<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientAlert;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientAlertsController extends Controller
{
    /**
     * Display a listing of alerts for a patient.
     */
    public function index(Patient $patient): View
    {
        $this->authorize('view', $patient);
        $this->authorize('viewAny', [PatientAlert::class, $patient]);

        $query = $patient->alerts()
            ->with(['creator', 'updater']);

        // Apply filters
        if (request()->has('filter')) {
            if (request('filter') === 'active') {
                $query->where('active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
            } elseif (request('filter') === 'inactive') {
                $query->where(function($q) {
                    $q->where('active', false)
                      ->orWhere(function($q2) {
                          $q2->whereNotNull('expires_at')
                             ->where('expires_at', '<=', now());
                      });
                });
            }
        }

        if (request()->has('severity')) {
            $query->where('severity', request('severity'));
        }

        if (request()->has('type')) {
            $query->where('type', request('type'));
        }

        $alerts = $query->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low', 'info')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->query());

        $alertCategories = config('alerts.categories', []);
        $severityColors = config('alerts.severity_colors', []);

        return view('admin.patients.alerts.index', compact('patient', 'alerts', 'alertCategories', 'severityColors'));
    }

    /**
     * Show the form for creating a new alert.
     */
    public function create(Patient $patient): View
    {
        $this->authorize('view', $patient);
        $this->authorize('create', [PatientAlert::class, $patient]);

        $alertCategories = config('alerts.categories', []);
        $alertTypes = config('alerts.types', []);
        $alertSeverities = config('alerts.severities', []);

        return view('admin.patients.alerts.create', compact('patient', 'alertCategories', 'alertTypes', 'alertSeverities'));
    }

    /**
     * Store a newly created alert.
     */
    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('view', $patient);
        $this->authorize('create', [PatientAlert::class, $patient]);

        // Get category config to set defaults
        $alertCategories = config('alerts.categories', []);
        $type = $request->input('type');
        $code = $request->input('code');
        $categoryConfig = $alertCategories[$type][$code] ?? null;

        $validationRules = [
            'type' => 'required|string|in:' . implode(',', config('alerts.types', [])),
            'code' => 'required|string',
            'severity' => 'nullable|string|in:' . implode(',', config('alerts.severities', [])),
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'restricted' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
        ];

        $validated = $request->validate($validationRules);

        // Set defaults from category config
        $data = [
            'patient_id' => $patient->id,
            'type' => $type,
            'code' => $code,
            'severity' => $validated['severity'] ?? ($categoryConfig['default_severity'] ?? 'medium'),
            'title' => $validated['title'] ?? $this->generateTitle($categoryConfig, $code),
            'description' => $validated['description'],
            'restricted' => $validated['restricted'] ?? ($categoryConfig['restricted'] ?? false),
            'active' => $validated['active'] ?? true,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => Auth::id(),
        ];

        $alert = PatientAlert::create($data);

        // Log activity if enabled in settings
        if ($this->shouldLogActivity('log_alert_creation', $alert->severity)) {
            \App\Models\UserActivity::log([
                'user_id' => Auth::id(),
                'action' => 'alert_created',
                'model_type' => PatientAlert::class,
                'model_id' => $alert->id,
                'description' => "Created alert '{$alert->title}' ({$alert->severity}) for patient {$patient->full_name}",
                'severity' => $this->getLogSeverity($alert->severity),
                'old_values' => null,
                'new_values' => $alert->toArray(),
            ]);
        }

        // Send email notification if enabled and alert is critical
        if ($this->shouldSendEmailNotification($alert)) {
            $this->sendCriticalAlertEmail($alert, $patient);
        }

        return redirect()->route('admin.patients.alerts.index', $patient)
            ->with('success', 'Alert created successfully.');
    }

    /**
     * Display the specified alert.
     */
    public function show(Patient $patient, PatientAlert $alert): View
    {
        $this->authorize('view', $patient);
        $this->authorize('view', $alert);

        $alert->load(['creator', 'updater', 'patient']);

        return view('admin.patients.alerts.show', compact('patient', 'alert'));
    }

    /**
     * Show the form for editing the specified alert.
     */
    public function edit(Patient $patient, PatientAlert $alert): View
    {
        $this->authorize('view', $patient);
        $this->authorize('update', $alert);

        $alertCategories = config('alerts.categories', []);
        $alertTypes = config('alerts.types', []);
        $alertSeverities = config('alerts.severities', []);

        return view('admin.patients.alerts.edit', compact('patient', 'alert', 'alertCategories', 'alertTypes', 'alertSeverities'));
    }

    /**
     * Update the specified alert.
     */
    public function update(Request $request, Patient $patient, PatientAlert $alert): RedirectResponse
    {
        $this->authorize('view', $patient);
        $this->authorize('update', $alert);

        $before = $alert->toArray();

        $validationRules = [
            'severity' => 'nullable|string|in:' . implode(',', config('alerts.severities', [])),
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'restricted' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ];

        $validated = $request->validate($validationRules);

        $validated['updated_by'] = Auth::id();

        $alert->update($validated);
        $alert->refresh();

        // Log activity if enabled in settings
        if ($this->shouldLogActivity('log_alert_update', $alert->severity)) {
            \App\Models\UserActivity::log([
                'user_id' => Auth::id(),
                'action' => 'alert_updated',
                'model_type' => PatientAlert::class,
                'model_id' => $alert->id,
                'description' => "Updated alert '{$alert->title}' ({$alert->severity}) for patient {$patient->full_name}",
                'severity' => $this->getLogSeverity($alert->severity),
                'old_values' => $before,
                'new_values' => $alert->toArray(),
            ]);
        }

        return redirect()->route('admin.patients.alerts.show', [$patient, $alert])
            ->with('success', 'Alert updated successfully.');
    }

    /**
     * Toggle active status of alert.
     */
    public function toggleActive(Patient $patient, PatientAlert $alert): RedirectResponse
    {
        $this->authorize('view', $patient);
        $this->authorize('toggleActive', $alert);

        $before = $alert->toArray();

        $isActivating = !$alert->active;
        $alert->update([
            'active' => !$alert->active,
            'updated_by' => Auth::id(),
        ]);
        $alert->refresh();

        // Log activity if enabled in settings
        $logAction = $isActivating ? 'log_alert_activation' : 'log_alert_deactivation';
        if ($this->shouldLogActivity($logAction, $alert->severity)) {
            \App\Models\UserActivity::log([
                'user_id' => Auth::id(),
                'action' => $alert->active ? 'alert_activated' : 'alert_deactivated',
                'model_type' => PatientAlert::class,
                'model_id' => $alert->id,
                'description' => ($alert->active ? 'Activated' : 'Deactivated') . " alert '{$alert->title}' ({$alert->severity}) for patient {$patient->full_name}",
                'severity' => $this->getLogSeverity($alert->severity),
                'old_values' => $before,
                'new_values' => $alert->toArray(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Alert ' . ($alert->active ? 'activated' : 'deactivated') . ' successfully.');
    }

    /**
     * Remove the specified alert (soft delete - deactivate instead).
     */
    public function destroy(Patient $patient, PatientAlert $alert): RedirectResponse
    {
        $this->authorize('view', $patient);
        $this->authorize('delete', $alert);

        // Instead of hard delete, deactivate
        $alert->update([
            'active' => false,
            'updated_by' => Auth::id(),
        ]);

        // Log activity if enabled in settings
        if ($this->shouldLogActivity('log_alert_deletion', $alert->severity)) {
            \App\Models\UserActivity::log([
                'user_id' => Auth::id(),
                'action' => 'alert_deleted',
                'model_type' => PatientAlert::class,
                'model_id' => $alert->id,
                'description' => "Deleted (deactivated) alert '{$alert->title}' ({$alert->severity}) for patient {$patient->full_name}",
                'severity' => $this->getLogSeverity($alert->severity),
                'old_values' => $alert->toArray(),
                'new_values' => null,
            ]);
        }

        return redirect()->route('admin.patients.alerts.index', $patient)
            ->with('success', 'Alert deleted successfully.');
    }

    /**
     * Generate title from category config or code.
     */
    protected function generateTitle(?array $categoryConfig, string $code): string
    {
        if ($categoryConfig && isset($categoryConfig['default_title_prefix'])) {
            return $categoryConfig['default_title_prefix'] . ucfirst(str_replace('_', ' ', $code));
        }

        return ucfirst(str_replace('_', ' ', $code));
    }

    /**
     * Check if activity should be logged based on settings.
     */
    protected function shouldLogActivity(string $action, string $severity): bool
    {
        // Check if logging is enabled
        if (!Setting::get('enable_alert_logging', true)) {
            return false;
        }

        // Check if specific action logging is enabled
        if (!Setting::get($action, true)) {
            return false;
        }

        // Check if severity level is logged
        $logSeverityLevels = explode(',', Setting::get('log_severity_levels', 'critical,high'));
        return in_array($severity, $logSeverityLevels);
    }

    /**
     * Get log severity based on alert severity.
     */
    protected function getLogSeverity(string $alertSeverity): string
    {
        return match($alertSeverity) {
            'critical' => 'critical',
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low',
            'info' => 'low',
            default => 'low'
        };
    }

    /**
     * Check if email notification should be sent.
     */
    protected function shouldSendEmailNotification(PatientAlert $alert): bool
    {
        if (!Setting::get('email_on_critical_alert', false)) {
            return false;
        }

        return $alert->severity === 'critical' && $alert->active;
    }

    /**
     * Send email notification for critical alert.
     */
    protected function sendCriticalAlertEmail(PatientAlert $alert, Patient $patient): void
    {
        try {
            $recipients = Setting::get('email_recipients', '');
            if (empty($recipients)) {
                return;
            }

            $emailAddresses = array_map('trim', explode(',', $recipients));
            $emailAddresses = array_filter($emailAddresses, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            if (empty($emailAddresses)) {
                return;
            }

            // Send email notification
            foreach ($emailAddresses as $email) {
                \Mail::raw(
                    "A critical patient alert has been created:\n\n" .
                    "Patient: {$patient->full_name} ({$patient->patient_id})\n" .
                    "Alert: {$alert->title}\n" .
                    "Severity: {$alert->severity}\n" .
                    "Type: {$alert->type}\n" .
                    "Description: {$alert->description}\n\n" .
                    "Created by: " . Auth::user()->name . "\n" .
                    "Created at: " . $alert->created_at->format('Y-m-d H:i:s'),
                    function ($message) use ($email, $alert, $patient) {
                        $message->to($email)
                               ->subject("Critical Alert: {$alert->title} - {$patient->full_name}")
                               ->from(config('mail.from.address'), config('mail.from.name'));
                    }
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send critical alert email: ' . $e->getMessage());
        }
    }
}
