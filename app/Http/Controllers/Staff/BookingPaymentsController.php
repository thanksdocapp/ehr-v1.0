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

        $request->merge(['doctor_id' => $doctor->id]);

        $rows = $service->paginateBookingPaymentRows($request, 25);
        $stats = $service->doctorBookingPaymentStats($doctor);

        $bookingPaymentsService = $service;

        return view('staff.booking-payments.index', compact('rows', 'doctor', 'stats', 'bookingPaymentsService'));
    }
}
