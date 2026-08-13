@php
    use App\Helpers\CurrencyHelper;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Doctor settlement #{{ $doctorSettlement->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .header p {
            margin: 4px 0;
            color: #7f8c8d;
            font-size: 9px;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 4px;
            font-size: 10px;
        }
        .summary p {
            margin: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #2c3e50;
            font-size: 9px;
        }
        td {
            padding: 7px 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-end { text-align: right; }
        .notes {
            margin-top: 12px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            white-space: pre-wrap;
            font-size: 9px;
        }
        .footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #7f8c8d;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Doctor settlement request #{{ $doctorSettlement->id }}</h1>
        <p>Generated {{ formatDateTimeUkAmPm(now()) }}</p>
    </div>

    <div class="summary">
        <p><strong>Doctor:</strong> {{ $doctorSettlement->doctor->user->name ?? 'Doctor #'.$doctorSettlement->doctor_id }}</p>
        <p><strong>Period:</strong> {{ formatDateUk($doctorSettlement->period_start) }} — {{ formatDateUk($doctorSettlement->period_end) }} ({{ ucfirst($doctorSettlement->period_type) }})</p>
        <p><strong>Status:</strong> {{ ucfirst($doctorSettlement->status) }}</p>
        <p><strong>Total:</strong> {{ CurrencyHelper::format((float) $doctorSettlement->total_amount) }}</p>
        @if($doctorSettlement->submitted_at)
            <p><strong>Submitted:</strong> {{ formatDateTimeUkAmPm($doctorSettlement->submitted_at) }}</p>
        @endif
        @if($doctorSettlement->reviewed_at)
            <p><strong>Reviewed:</strong> {{ formatDateTimeUkAmPm($doctorSettlement->reviewed_at) }}
                @if($doctorSettlement->reviewedByUser)
                    by {{ $doctorSettlement->reviewedByUser->name }}
                @endif
            </p>
        @endif
    </div>

    @if($doctorSettlement->notes)
        <div class="notes">
            <strong>Notes</strong><br>
            {{ $doctorSettlement->notes }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Billing ID</th>
                <th>Bill number</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctorSettlement->lines as $line)
            @php
                $patient = $line->billing?->patient;
                $patientName = $patient ? trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) : '';
                $patientName = $patientName !== '' ? $patientName : '—';
            @endphp
            <tr>
                <td>{{ $patientName }}</td>
                <td>{{ $line->description }}</td>
                <td>{{ $line->billing_id ?? '—' }}</td>
                <td>{{ $line->billing?->bill_number ?? '—' }}</td>
                <td class="text-end">{{ CurrencyHelper::format((float) $line->amount) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #7f8c8d;">No line items.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        ThanksDoc EPR — Doctor settlement export
    </div>
</body>
</html>
