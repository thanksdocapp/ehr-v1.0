<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\BookingPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingPaymentsController extends Controller
{
    public function index(Request $request, BookingPaymentsService $service): View
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $query = $service->completedPaymentsForDoctor($doctor)
            ->with([
                'invoice.patient',
                'invoice.appointment',
                'invoice.pendingBookings',
                'invoice.billing.doctor.user',
                'invoice.billing.appointment',
                'invoice.doctorBookingDiscountCode',
                'invoice.clinicBookingDiscountCode',
            ])
            ->orderByDesc('payment_date');

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->string('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->string('to'));
        }

        $payments = $query->paginate(25)->withQueryString();
        $stats = $service->doctorBookingPaymentStats($doctor);

        $bookingPaymentsService = $service;

        return view('staff.booking-payments.index', compact('payments', 'doctor', 'stats', 'bookingPaymentsService'));
    }
}
