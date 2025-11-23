<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingsController extends Controller
{
    /**
     * Display a listing of the billings.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Billing::with(['patient', 'doctor', 'appointment']);

        // Department-based filtering
        $departmentId = null;
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            $departmentId = $doctor ? $doctor->department_id : null;
        } else {
            $departmentId = $user->department_id;
        }
        if ($departmentId) {
            $query->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // ===== QUICK SEARCH (Multi-field) =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('patient_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('doctor', function($dq) use ($search) {
                      $dq->whereHas('user', function($uq) use ($search) {
                          $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        // ===== PATIENT FILTERS =====
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
        if ($request->filled('patient_name')) {
            $patientName = $request->patient_name;
            $query->whereHas('patient', function($q) use ($patientName) {
                $q->where('first_name', 'like', "%{$patientName}%")
                  ->orWhere('last_name', 'like', "%{$patientName}%");
            });
        }

        // ===== DOCTOR & DEPARTMENT FILTERS =====
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('doctor', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // ===== STATUS & TYPE FILTERS =====
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // ===== DATE & TIME FILTERS =====
        if ($request->filled('billing_date_from')) {
            $dateFrom = Carbon::parse($request->billing_date_from)->format('Y-m-d');
            $query->whereDate('billing_date', '>=', $dateFrom);
        }
        if ($request->filled('billing_date_to')) {
            $dateTo = Carbon::parse($request->billing_date_to)->format('Y-m-d');
            $query->whereDate('billing_date', '<=', $dateTo);
        }
        if ($request->filled('billing_date')) {
            $date = Carbon::parse($request->billing_date)->format('Y-m-d');
            $query->whereDate('billing_date', $date);
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }
        if ($request->filled('created_from')) {
            $query->where('created_at', '>=', $request->created_from . ' 00:00:00');
        }
        if ($request->filled('created_to')) {
            $query->where('created_at', '<=', $request->created_to . ' 23:59:59');
        }

        // ===== DATE RANGE FILTERS =====
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('billing_date', today());
                    break;
                case 'yesterday':
                    $query->whereDate('billing_date', today()->copy()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('billing_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $lastWeek = now()->copy()->subWeek();
                    $query->whereBetween('billing_date', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('billing_date', now()->month)
                          ->whereYear('billing_date', now()->year);
                    break;
                case 'last_month':
                    $lastMonth = now()->copy()->subMonth();
                    $query->whereMonth('billing_date', $lastMonth->month)
                          ->whereYear('billing_date', $lastMonth->year);
                    break;
                case 'this_year':
                    $query->whereYear('billing_date', now()->year);
                    break;
            }
        }

        // ===== AMOUNT FILTERS =====
        if ($request->filled('total_amount_min')) {
            $query->where('total_amount', '>=', $request->total_amount_min);
        }
        if ($request->filled('total_amount_max')) {
            $query->where('total_amount', '<=', $request->total_amount_max);
        }
        if ($request->filled('balance_min')) {
            $query->where('balance', '>=', $request->balance_min);
        }
        if ($request->filled('balance_max')) {
            $query->where('balance', '<=', $request->balance_max);
        }
        if ($request->filled('paid_amount_min')) {
            $query->where('paid_amount', '>=', $request->paid_amount_min);
        }
        if ($request->filled('paid_amount_max')) {
            $query->where('paid_amount', '<=', $request->paid_amount_max);
        }

        // ===== PAYMENT FILTERS =====
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('has_payment')) {
            if ($request->has_payment === 'yes') {
                $query->where('paid_amount', '>', 0);
            } elseif ($request->has_payment === 'no') {
                $query->where('paid_amount', '=', 0);
            }
        }
        if ($request->filled('has_payment_reference')) {
            if ($request->has_payment_reference === 'yes') {
                $query->whereNotNull('payment_reference');
            } elseif ($request->has_payment_reference === 'no') {
                $query->whereNull('payment_reference');
            }
        }

        // ===== OVERDUE FILTERS =====
        if ($request->filled('overdue')) {
            $query->where(function($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'pending')
                           ->where('due_date', '<', today());
                  });
            });
        }
        if ($request->filled('due_soon')) {
            $query->where('due_date', '>=', today())
                  ->where('due_date', '<=', today()->copy()->addDays(7))
                  ->whereIn('status', ['pending', 'partially_paid']);
        }

        // ===== RELATIONSHIP FILTERS =====
        if ($request->filled('has_appointment')) {
            if ($request->has_appointment === 'yes') {
                $query->whereNotNull('appointment_id');
            } elseif ($request->has_appointment === 'no') {
                $query->whereNull('appointment_id');
            }
        }

        // ===== DESCRIPTION FILTERS =====
        if ($request->filled('description')) {
            $query->where('description', 'like', "%{$request->description}%");
        }

        // Prepare data for view
        $doctors = Doctor::with('user')->get()->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user ? $doctor->user->name : 'Unknown'
            ];
        });
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $billingTypes = ['consultation', 'procedure', 'medication', 'lab_test', 'other'];
        $paymentMethods = ['cash', 'card', 'insurance', 'bank_transfer'];
        $statuses = ['pending', 'paid', 'partially_paid', 'overdue', 'cancelled'];

        // Sort by date and time
        $bills = $query->orderBy('billing_date', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->paginate(15)->appends($request->query());

        return view('staff.billing.index', compact('bills', 'doctors', 'departments', 'billingTypes', 'paymentMethods', 'statuses'));
    }

    /**
     * Show the form for creating a new billing.
     */
    public function create(Request $request): View
    {
        // Filter patients by department for all roles
        $user = Auth::user();
        $query = Patient::query()->visibleTo(Auth::user());
        $departmentId = null;
        $currentDoctor = null;
        
        if ($user->role === 'doctor') {
            $currentDoctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $departmentId = $currentDoctor ? $currentDoctor->department_id : null;
        } else {
            $departmentId = $user->department_id;
        }
        if ($departmentId) {
            $query->byDepartment($departmentId);
        }
        $patients = $query->orderBy('first_name')->get();
        
        // For doctors, don't show other doctors in dropdown
        // For other staff, show all doctors
        $doctors = ($user->role === 'doctor') ? collect([]) : Doctor::orderBy('first_name')->get();
        $appointments = [];
        
        // If appointment_id is provided, pre-select appointment
        $selectedAppointment = null;
        if ($request->filled('appointment_id')) {
            $selectedAppointment = Appointment::with(['patient', 'doctor'])->find($request->appointment_id);
            if ($selectedAppointment) {
                $appointments = [$selectedAppointment];
            }
        }
        
        return view('staff.billing.create', compact('patients', 'doctors', 'appointments', 'selectedAppointment', 'currentDoctor'));
    }

    /**
     * Display the specified billing.
     */
    public function show(Billing $billing): View
    {
        return view('staff.billing.show', compact('billing'));
    }

    /**
     * Store a newly created billing.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $doctorId = $request->doctor_id;
        
        // If user is a doctor, automatically assign their doctor ID
        if ($user->role === 'doctor') {
            $currentDoctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            if ($currentDoctor) {
                $doctorId = $currentDoctor->id;
            }
        }
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'billing_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:billing_date',
            'type' => 'required|string|in:consultation,procedure,medication,lab_test,other',
            'description' => 'required|string|max:500',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $subtotal = $request->subtotal;
        $discount = $request->discount ?? 0;
        $tax = $request->tax ?? 0;
        $totalAmount = $subtotal - $discount + $tax;

        Billing::create([
            'bill_number' => Billing::generateBillNumber(),
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctorId,
            'appointment_id' => $request->appointment_id,
            'billing_date' => $request->billing_date,
            'due_date' => $request->due_date,
            'type' => $request->type,
            'description' => $request->description,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'balance' => $totalAmount,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('staff.billing.index')
            ->with('success', 'Billing record created successfully!');
    }

}

