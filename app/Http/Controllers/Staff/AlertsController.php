<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PatientAlert;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        
        $query = PatientAlert::with(['patient', 'creator', 'updater']);

        // Apply visibility filter - users can only see alerts for patients they have access to
        if ($user->role === 'doctor') {
            // Doctors see alerts for their own patients
            $query->whereHas('patient', function($q) use ($user) {
                $q->visibleTo($user);
            });
        } elseif (!in_array($user->role, ['admin', 'doctor'])) {
            // Other staff see alerts for patients in their department
            $query->whereHas('patient', function($q) use ($user) {
                $q->visibleTo($user);
            });
        }

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
            $patient = Patient::find($request->patient_id);
            if ($patient && Gate::allows('view', $patient)) {
                $query->where('patient_id', $request->patient_id);
            }
        }

        // Additional filtering for restricted alerts based on role
        if (!in_array($user->role, ['admin', 'doctor'])) {
            $query->where('restricted', false);
        }

        $alerts = $query->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low', 'info')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $alertCategories = config('alerts.categories', []);
        $severities = config('alerts.severities', ['critical', 'high', 'medium', 'low', 'info']);
        $patients = Patient::visibleTo($user)->active()->orderBy('first_name')->get();

        return view('staff.alerts.index', compact('alerts', 'alertCategories', 'severities', 'patients'));
    }
}

