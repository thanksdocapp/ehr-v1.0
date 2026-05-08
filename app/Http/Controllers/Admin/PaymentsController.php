<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\BookingPaymentsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentsController extends Controller
{
    public function index(Request $request, BookingPaymentsService $bookingPaymentsService): View
    {
        $query = Payment::query()
            ->with([
                'invoice.patient',
                'invoice.appointment.doctor.user',
                'invoice.pendingBookings.doctor.user',
                'invoice.pendingClinicBookings.department',
                'invoice.billing.doctor.user',
                'invoice.billing.appointment',
                'invoice.doctorBookingDiscountCode',
                'invoice.clinicBookingDiscountCode',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method'));
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->string('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->string('to'));
        }

        if ($request->filled('invoice_number')) {
            $term = '%'.$request->string('invoice_number').'%';
            $query->whereHas('invoice', fn ($q) => $q->where('invoice_number', 'like', $term));
        }

        if ($request->filled('patient')) {
            $term = '%'.$request->string('patient').'%';
            $query->whereHas('invoice.patient', function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhereRaw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) like ?", [$term]);
            });
        }

        $totalAmount = (float) (clone $query)->sum('amount');

        $payments = $query
            ->orderByDesc('payment_date')
            ->paginate(40)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'totalAmount', 'bookingPaymentsService'));
    }
}
