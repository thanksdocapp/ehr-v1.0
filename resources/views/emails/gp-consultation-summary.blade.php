<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Notes</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; line-height: 1.4; color: #333; margin: 20px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        .record { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
        .record:last-child { border-bottom: none; }
        .record h2 { font-size: 13px; margin: 0 0 8px 0; color: #1a202c; }
        .label { font-weight: 600; color: #4a5568; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>Consultation Notes</h1>
    <div class="meta">
        <p><span class="label">Patient:</span> {{ $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name) }}</p>
        <p><span class="label">Patient ID:</span> {{ $patient->patient_id ?? 'N/A' }}</p>
        <p><span class="label">Date of birth:</span> {{ $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : 'N/A' }}</p>
        <p><span class="label">Generated:</span> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @foreach($medicalRecords as $record)
    <div class="record">
        <h2>{{ $record->record_date ? $record->record_date->format('d/m/Y') : $record->created_at->format('d/m/Y') }} – {{ ucfirst($record->record_type ?? 'Consultation') }}{{ optional($record->doctor)->full_name ? ' – Dr ' . $record->doctor->full_name : '' }}</h2>
        @if($record->presenting_complaint)
        <p><span class="label">Presenting complaint:</span> {{ $record->presenting_complaint }}</p>
        @endif
        @if($record->diagnosis)
        <p><span class="label">Diagnosis:</span> {{ $record->diagnosis }}</p>
        @endif
        @if($record->treatment)
        <p><span class="label">Treatment:</span> {{ $record->treatment }}</p>
        @endif
        @if($record->plan)
        <p><span class="label">Plan:</span> {{ $record->plan }}</p>
        @endif
        @if($record->notes)
        <p><span class="label">Notes:</span> {{ $record->notes }}</p>
        @endif
    </div>
    @endforeach
</body>
</html>
