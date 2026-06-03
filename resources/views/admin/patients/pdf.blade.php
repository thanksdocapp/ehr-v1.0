@php
    use App\Helpers\CurrencyHelper;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Patient list</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #2c3e50;
        }
        .header p {
            margin: 3px 0;
            color: #7f8c8d;
            font-size: 8px;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 5px 3px;
            text-align: left;
            border: 1px solid #2c3e50;
            font-size: 6.5px;
        }
        td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 6.5px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-end { text-align: right; }
        .notes {
            max-width: 120px;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #7f8c8d;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Patient list</h1>
        <p>Generated: {{ formatDateTimeUkAmPm(now()) }}</p>
        <p>{{ $filterSummary }}</p>
    </div>

    <div class="summary">
        <strong>Patients:</strong> {{ $patients->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>DOB</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Clinic(s)</th>
                <th>Assigned Doctor</th>
                <th>Last Appt</th>
                <th>Source</th>
                <th class="text-end">Invoices</th>
                <th class="text-end">Total Invoiced</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patients as $patient)
                @php
                    $departments = $patient->departments->pluck('name')->join(', ')
                        ?: ($patient->department ? $patient->department->name : '—');
                    $latestAppointment = $patient->appointments->first();
                    $lastAppointment = $latestAppointment && $latestAppointment->appointment_date
                        ? $latestAppointment->appointment_date->format('d/m/Y')
                        : '—';
                    $bookingSource = $latestAppointment ? ($latestAppointment->created_from ?? '—') : '—';
                    $invoiceCount = $patient->invoices->count();
                    $totalInvoiced = $invoiceCount ? (float) $patient->invoices->sum('total_amount') : 0.0;
                    $assignedDoctor = $patient->assignedDoctor
                        ? ($patient->assignedDoctor->user->name ?? $patient->assignedDoctor->full_name ?? '—')
                        : '—';
                    $age = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '—';
                @endphp
                <tr>
                    <td>{{ $patient->patient_id }}</td>
                    <td>{{ $patient->full_name }}</td>
                    <td>{{ $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : '—' }}</td>
                    <td>{{ $age }}</td>
                    <td>{{ ucfirst($patient->gender ?? '') ?: '—' }}</td>
                    <td>{{ $patient->phone ?: '—' }}</td>
                    <td>{{ $patient->email ?: '—' }}</td>
                    <td>{{ $departments }}</td>
                    <td>{{ $assignedDoctor }}</td>
                    <td>{{ $lastAppointment }}</td>
                    <td>{{ $bookingSource }}</td>
                    <td class="text-end">{{ $invoiceCount }}</td>
                    <td class="text-end">{{ $invoiceCount ? CurrencyHelper::format($totalInvoiced) : '—' }}</td>
                    <td class="notes">{{ $patient->notes ? \Illuminate\Support\Str::limit($patient->notes, 120) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($patients->isEmpty())
        <p style="margin-top: 12px; text-align: center; color: #7f8c8d;">No patients match the current filters.</p>
    @endif

    <div class="footer">
        Patient list export
    </div>
</body>
</html>
