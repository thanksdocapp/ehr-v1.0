<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\BookingPaymentsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingPaymentsController extends Controller
{
    public function index(Request $request, BookingPaymentsService $service): View
    {
        $query = $request->filled('doctor_id')
            ? $service->completedPaymentsForDoctor(Doctor::findOrFail($request->integer('doctor_id')))
            : $service->completedBookingPaymentsBase();

        if ($request->filled('department_id')) {
            $departmentId = $request->integer('department_id');
            Department::query()->where('is_active', true)->whereKey($departmentId)->firstOrFail();
            $query = $service->restrictPaymentsToDepartment($query, $departmentId);
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->string('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->string('to'));
        }

        $totalAmount = (float) (clone $query)->sum('amount');

        $payments = $query
            ->with([
                'invoice.patient',
                'invoice.appointment.doctor.user',
                'invoice.appointment.department',
                'invoice.pendingBookings.doctor.user',
                'invoice.pendingBookings.department',
                'invoice.pendingClinicBookings.department',
                'invoice.billing.doctor.user',
                'invoice.billing.doctor.department',
                'invoice.billing.doctor.departments',
                'invoice.billing.appointment.doctor.user',
                'invoice.billing.appointment.department',
                'invoice.doctorBookingDiscountCode.doctor.user',
                'invoice.doctorBookingDiscountCode.doctor.department',
                'invoice.doctorBookingDiscountCode.doctor.departments',
                'invoice.clinicBookingDiscountCode.department',
            ])
            ->orderByDesc('payment_date')
            ->paginate(30)
            ->withQueryString();

        $doctors = Doctor::query()->with('user')->orderBy('last_name')->orderBy('first_name')->get();
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();

        $bookingPaymentsService = $service;

        return view('admin.booking-payments.index', compact('payments', 'doctors', 'departments', 'totalAmount', 'bookingPaymentsService'));
    }
}
