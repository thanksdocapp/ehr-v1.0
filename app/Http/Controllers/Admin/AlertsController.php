<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientAlert;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class AlertsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of all patient alerts.
     */
    public function index(Request $request): View
    {
        $query = PatientAlert::with(['patient', 'creator', 'updater']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
            } elseif ($request->status === 'inactive') {
                $query->where(function($q) {
                    $q->where('active', false)
                      ->orWhere(function($q2) {
                          $q2->whereNotNull('expires_at')
                             ->where('expires_at', '<=', now());
                      });
                });
            }
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $alerts = $query->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low', 'info')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $alertCategories = config('alerts.categories', []);
        $severities = config('alerts.severities', ['critical', 'high', 'medium', 'low', 'info']);
        $patients = Patient::active()->orderBy('first_name')->get();

        return view('admin.alerts.index', compact('alerts', 'alertCategories', 'severities', 'patients'));
    }
}

