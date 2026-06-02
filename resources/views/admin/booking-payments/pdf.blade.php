@php
    use App\Helpers\CurrencyHelper;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking payments</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
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
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 4px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #2c3e50;
            font-size: 7px;
        }
        td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-end { text-align: right; }
        .comments {
            max-width: 140px;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #7f8c8d;
            font-size: 8px;
        }
        .fee-note {
            margin-top: 10px;
            padding: 8px 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            text-align: left;
            color: #444;
            font-size: 7px;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking payments</h1>
        <p>Generated: {{ formatDateTimeUkAmPm(now()) }}</p>
        <p>{{ $filterSummary }}</p>
    </div>

    <div class="summary">
        <strong>Filtered total:</strong> {{ CurrencyHelper::format((float) $totalAmount) }}
        &nbsp;|&nbsp;
        <strong>Rows:</strong> {{ $rows->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-end">Amount</th>
                <th>Method</th>
                <th>Source</th>
                <th>Invoice</th>
                <th>Doctor</th>
                <th>Booking capture</th>
                <th>Patient</th>
                <th>Appointment</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    $src = $bookingPaymentsService->labelForRow($row);
                    $capture = $bookingPaymentsService->bookingCaptureForRow($row);
                    $comments = $bookingPaymentsService->commentsForRow($row);
                    $captureText = $bookingPaymentsService->formatCaptureForDisplay($capture);
                    $sortAt = $row->sortAt();
                @endphp
                <tr>
                    <td>{{ $sortAt ? formatDateTimeUkAmPm($sortAt) : '—' }}</td>
                    <td class="text-end">{{ CurrencyHelper::format($row->amount()) }}</td>
                    <td>{{ $bookingPaymentsService->methodLabelForRow($row) }}</td>
                    <td>{{ $src }}</td>
                    <td>{{ $bookingPaymentsService->invoiceLabelForRow($row) }}</td>
                    <td>{{ $bookingPaymentsService->doctorNameForRow($row) ?? '—' }}</td>
                    <td>{{ $captureText !== '' ? $captureText : '—' }}</td>
                    <td>{{ $bookingPaymentsService->patientNameForRow($row) }}</td>
                    <td>{{ $bookingPaymentsService->appointmentSlotLabelForRow($row) }}</td>
                    <td class="comments">{{ $comments !== '' ? $comments : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($rows->isEmpty())
        <p style="margin-top: 12px; text-align: center; color: #7f8c8d;">No payments match the current filters.</p>
    @endif

    <div class="footer">
        ThanksDoc EPR — Booking payments export
    </div>

    <div class="fee-note">
        <strong>Note:</strong> Amounts shown are payment totals recorded in the system. Where card payments are processed via Stripe, Stripe deducts its own processing fees from the charge; the net amount settled to your account may therefore be lower than the figures in this report.
        <br><br>
        <strong>UK (GBP) — indicative Stripe card fees</strong> (subject to change; your mix of card types may differ): Stripe’s published UK pricing is typically around <strong>1.5% + 20p</strong> per successful charge for standard UK cards, <strong>1.9% + 20p</strong> for UK commercial cards, <strong>2.5% + 20p</strong> for EEA cards, and <strong>3.25% + 20p</strong> for international cards. Currency conversion, disputes, or other products may add further costs.
        <br><br>
        Refer to <strong>Stripe’s UK pricing</strong> at <strong>stripe.com/gb/pricing</strong> and your Stripe Dashboard for current fees and payouts.
    </div>
</body>
</html>
