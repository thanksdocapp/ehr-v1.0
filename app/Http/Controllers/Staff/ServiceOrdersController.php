<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        $query = ServiceOrder::with(['patient', 'service', 'doctor', 'department'])
            ->orderByDesc('created_at');

        if ($doctor) {
            $query->where('doctor_id', $doctor->id);
        } elseif (! $user->isAdmin()) {
            abort(403);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('staff.service-orders.index', compact('orders', 'doctor'));
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $this->authorizeOrder($serviceOrder);

        $serviceOrder->load(['patient', 'service', 'doctor', 'department', 'invoice']);

        return view('staff.service-orders.show', compact('serviceOrder'));
    }

    public function markContacted(ServiceOrder $serviceOrder)
    {
        $this->authorizeOrder($serviceOrder);

        if (! in_array($serviceOrder->status, [ServiceOrder::STATUS_PAID, ServiceOrder::STATUS_CONTACTED], true)) {
            return redirect()->back()->with('error', 'This order cannot be marked as contacted.');
        }

        $serviceOrder->update([
            'status' => ServiceOrder::STATUS_CONTACTED,
            'contacted_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Order marked as contacted.');
    }

    public function markCompleted(ServiceOrder $serviceOrder)
    {
        $this->authorizeOrder($serviceOrder);

        if (! in_array($serviceOrder->status, [ServiceOrder::STATUS_PAID, ServiceOrder::STATUS_CONTACTED], true)) {
            return redirect()->back()->with('error', 'This order cannot be marked as completed.');
        }

        $serviceOrder->update(['status' => ServiceOrder::STATUS_COMPLETED]);

        return redirect()->back()->with('success', 'Order marked as completed.');
    }

    private function authorizeOrder(ServiceOrder $serviceOrder): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return;
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (! $doctor || (int) $serviceOrder->doctor_id !== (int) $doctor->id) {
            abort(403);
        }
    }
}
