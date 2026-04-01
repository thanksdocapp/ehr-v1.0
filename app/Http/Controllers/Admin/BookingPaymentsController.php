<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\BookingPaymentsService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingPaymentsController extends Controller
{
    public function index(Request $request, BookingPaymentsService $service): View
    {
        $query = $this->buildFilteredPaymentsQuery($request, $service);

        $totalAmount = (float) (clone $query)->sum('amount');

        $payments = $query
            ->with($this->bookingPaymentsEagerLoads())
            ->orderByDesc('payment_date')
            ->paginate(30)
            ->withQueryString();

        $doctors = Doctor::query()->with('user')->orderBy('last_name')->orderBy('first_name')->get();
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();

        $bookingPaymentsService = $service;

        return view('admin.booking-payments.index', compact('payments', 'doctors', 'departments', 'totalAmount', 'bookingPaymentsService'));
    }

    public function exportPdf(Request $request, BookingPaymentsService $service): StreamedResponse
    {
        $query = $this->buildFilteredPaymentsQuery($request, $service);
        $totalAmount = (float) (clone $query)->sum('amount');

        $payments = (clone $query)
            ->with($this->bookingPaymentsEagerLoads())
            ->orderByDesc('payment_date')
            ->get();

        $html = view('admin.booking-payments.pdf', [
            'payments' => $payments,
            'bookingPaymentsService' => $service,
            'totalAmount' => $totalAmount,
            'filterSummary' => $this->filterSummaryForExport($request),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = $this->bookingPaymentsExportFilename($request, 'pdf');

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename);
    }

    public function exportCsv(Request $request, BookingPaymentsService $service): StreamedResponse
    {
        $query = $this->buildFilteredPaymentsQuery($request, $service);
        $payments = (clone $query)
            ->with($this->bookingPaymentsEagerLoads())
            ->orderByDesc('payment_date')
            ->get();

        $filename = $this->bookingPaymentsExportFilename($request, 'csv');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($payments, $service) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Date',
                'Amount',
                'Method',
                'Source',
                'Invoice',
                'Doctor',
                'Clinic',
                'Patient',
                'Appointment',
                'Comments',
            ]);

            foreach ($payments as $payment) {
                $comments = $service->commentsForBookingPayment($payment);

                fputcsv($file, [
                    $payment->payment_date ? formatDateTimeUkAmPm($payment->payment_date) : '—',
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->payment_method_label,
                    $service->labelForPayment($payment),
                    $payment->invoice?->invoice_number ?? ('#'.$payment->invoice?->id),
                    $service->doctorNameForBookingPayment($payment) ?? '—',
                    $service->clinicNameForBookingPayment($payment) ?? '—',
                    $service->patientNameForBookingPayment($payment),
                    $service->appointmentSlotLabelForBookingPayment($payment),
                    $comments !== '' ? $comments : '—',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildFilteredPaymentsQuery(Request $request, BookingPaymentsService $service): Builder
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

        return $query;
    }

    /**
     * @return array<int, string>
     */
    private function bookingPaymentsEagerLoads(): array
    {
        return [
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
        ];
    }

    /**
     * Safe download filename: booking_payments_{from}_to_{to}[_doctor_{slug}][_clinic_{slug}].{ext}
     */
    private function bookingPaymentsExportFilename(Request $request, string $extension): string
    {
        $from = $request->filled('from') ? $request->string('from') : 'start';
        $to = $request->filled('to') ? $request->string('to') : 'end';
        $segments = ['booking_payments', $from, 'to', $to];

        if ($request->filled('doctor_id')) {
            $doctor = Doctor::with('user')->find($request->integer('doctor_id'));
            $label = $doctor
                ? (string) ($doctor->user->name ?? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? '')))
                : '';
            $slug = Str::slug($label, '-');
            if ($slug === '') {
                $slug = 'id-'.$request->integer('doctor_id');
            }
            $segments[] = 'doctor';
            $segments[] = $slug;
        }

        if ($request->filled('department_id')) {
            $department = Department::find($request->integer('department_id'));
            $label = $department?->name ?? '';
            $slug = Str::slug($label, '-');
            if ($slug === '') {
                $slug = 'id-'.$request->integer('department_id');
            }
            $segments[] = 'clinic';
            $segments[] = $slug;
        }

        $base = implode('_', $segments);
        $base = substr($base, 0, 180);

        return $base.'.'.ltrim($extension, '.');
    }

    private function filterSummaryForExport(Request $request): string
    {
        $parts = [];
        if ($request->filled('doctor_id')) {
            $d = Doctor::with('user')->find($request->integer('doctor_id'));
            $parts[] = 'Doctor: '.($d ? ($d->user->name ?? trim(($d->first_name ?? '').' '.($d->last_name ?? ''))) : '—');
        }
        if ($request->filled('department_id')) {
            $dept = Department::find($request->integer('department_id'));
            $parts[] = 'Clinic: '.($dept?->name ?? '—');
        }
        if ($request->filled('from')) {
            $parts[] = 'From: '.$request->string('from');
        }
        if ($request->filled('to')) {
            $parts[] = 'To: '.$request->string('to');
        }

        return $parts !== [] ? implode(' · ', $parts) : 'All completed payments (no filters)';
    }
}
