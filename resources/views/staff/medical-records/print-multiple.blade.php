<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Records - {{ getClinicName() }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ehr-primary: #005eb8;
            --ehr-primary-light: #e8f4fc;
            --ehr-secondary: #003087;
            --ehr-text: #212b32;
            --ehr-text-muted: #4c6272;
            --ehr-border: #d8dde0;
            --ehr-bg: #f0f4f5;
        }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 13px; line-height: 1.6; color: var(--ehr-text); margin: 0; padding: 0; }
        .print-document { max-width: 210mm; margin: 0 auto; padding: 24px; }
        .clinic-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; margin-bottom: 24px; border-bottom: 1px solid var(--ehr-border); }
        .clinic-name { font-size: 1.5rem; font-weight: 700; color: var(--ehr-primary); margin: 0; }
        .document-meta { text-align: right; }
        .document-type { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ehr-primary); margin: 0 0 4px 0; }
        .document-date { font-size: 0.8rem; color: var(--ehr-text-muted); margin: 0; }
        .record-block { margin-bottom: 36px; }
        .record-block + .record-block { page-break-before: always; }
        .patient-header { background: linear-gradient(135deg, var(--ehr-primary) 0%, var(--ehr-secondary) 100%); color: #fff; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .patient-header-inner { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .patient-name { font-size: 1.25rem; font-weight: 700; margin: 0 0 4px 0; }
        .patient-id { font-size: 0.85rem; opacity: 0.9; }
        .header-badges { display: flex; gap: 8px; flex-wrap: wrap; }
        .header-badge { background: rgba(255,255,255,0.25); padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; }
        .section { margin-bottom: 24px; page-break-inside: avoid; break-inside: avoid; }
        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--ehr-border); page-break-after: avoid; }
        .section-icon { width: 32px; height: 32px; background: var(--ehr-primary-light); color: var(--ehr-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
        .section-title { font-size: 0.95rem; font-weight: 700; color: var(--ehr-text); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 24px; }
        .info-item { display: flex; flex-direction: column; gap: 4px; }
        .info-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ehr-text-muted); }
        .info-value { font-weight: 500; color: var(--ehr-text); }
        .clinical-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; page-break-inside: avoid; }
        .clinical-block { background: var(--ehr-bg); border: 1px solid var(--ehr-border); border-left: 4px solid var(--ehr-primary); border-radius: 8px; padding: 14px 16px; page-break-inside: avoid; }
        .clinical-block.full-width { grid-column: 1 / -1; }
        .clinical-block.allergy { border-left-color: #dc2626; }
        .clinical-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ehr-primary); margin-bottom: 8px; }
        .clinical-block.allergy .clinical-label { color: #dc2626; }
        .clinical-content { font-size: 0.9rem; line-height: 1.6; }
        .vitals-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .vital-pill { background: var(--ehr-bg); border: 1px solid var(--ehr-border); border-radius: 10px; padding: 14px; text-align: center; }
        .vital-value { font-size: 1.1rem; font-weight: 700; color: var(--ehr-primary); }
        .vital-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--ehr-text-muted); }
        .rx-list { list-style: none; padding: 0; margin: 0; }
        .rx-item { padding: 12px 0; border-bottom: 1px solid var(--ehr-border); }
        .rx-item:last-child { border-bottom: none; }
        .rx-name { font-weight: 500; }
        .rx-detail { font-size: 0.85rem; color: var(--ehr-text-muted); }
        .main-grid { display: grid; grid-template-columns: 1fr 280px; gap: 28px; }
        .signature-block { margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--ehr-border); display: flex; flex-wrap: wrap; gap: 8px 24px; align-items: baseline; }
        .signature-name { font-weight: 600; }
        .signature-role { font-size: 0.9rem; color: var(--ehr-text-muted); }
        .signature-clinic { font-size: 0.9rem; color: var(--ehr-primary); font-weight: 500; }
        .signature-date { font-size: 0.85rem; color: var(--ehr-text-muted); margin-left: auto; }
        .print-footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid var(--ehr-border); font-size: 0.75rem; color: var(--ehr-text-muted); text-align: center; }
        .no-print { margin-bottom: 16px; }
        @media print {
            body { padding: 0; }
            .print-document { padding: 16px; max-width: none; }
            .main-grid { grid-template-columns: 1fr; }
            .no-print { display: none !important; }
            /* Repeating header on every printed page */
            .clinic-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #fff;
                z-index: 9999;
                padding: 8px 16px 10px;
                margin: 0;
                border-bottom: 1px solid var(--ehr-border);
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .clinic-header .clinic-name { font-size: 1.1rem; }
            .clinic-header .document-type { font-size: 0.7rem; }
            .clinic-header .document-date { font-size: 0.75rem; }
            .print-document { padding-top: 60px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 16px;">
        <button type="button" onclick="window.print()" style="padding: 10px 20px; background: #005eb8; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
            <i class="fas fa-print" style="margin-right: 8px;"></i>Print
        </button>
        <a href="{{ route('staff.medical-records.index') }}" style="margin-left: 12px; padding: 10px 20px; border: 1px solid #005eb8; color: #005eb8; border-radius: 8px; text-decoration: none; font-weight: 600;">Back to Records</a>
    </div>
    <div class="print-document">
        <header class="clinic-header">
            <h1 class="clinic-name">{{ getClinicName() }}</h1>
            <div class="document-meta">
                <p class="document-type">Consultation Records ({{ $medicalRecords->count() }})</p>
                <p class="document-date">Printed {{ now()->format('d M Y') }}</p>
            </div>
        </header>

        @foreach($medicalRecords as $record)
        <div class="record-block">
            @php
                $patient = $record->patient;
                $doctor = $record->doctor;
                $recordDate = $record->record_date ?? $record->created_at;
            @endphp

            <div class="patient-header">
                <div class="patient-header-inner">
                    <div>
                        <h2 class="patient-name">
                            @if($patient)
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            @else
                                Patient record deleted
                            @endif
                        </h2>
                        @if($patient && $patient->patient_id)
                            <span class="patient-id">{{ $patient->patient_id }}</span>
                        @endif
                    </div>
                    <div class="header-badges">
                        <span class="header-badge">{{ ucfirst(str_replace('_', ' ', $record->record_type ?? 'consultation')) }}</span>
                        <span class="header-badge">{{ $recordDate ? $recordDate->format('d M Y') : $record->created_at->format('d M Y') }}</span>
                        <span class="header-badge">#{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
            </div>

            <div class="main-grid">
                <main>
                    <section class="section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-file-medical"></i></div>
                            <h3 class="section-title">Record</h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Record ID</span>
                                <span class="info-value">#{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Date</span>
                                <span class="info-value">{{ $recordDate ? $recordDate->format('d M Y') : $record->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Created</span>
                                <span class="info-value">{{ $record->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            @if($record->appointment)
                            <div class="info-item">
                                <span class="info-label">Appointment</span>
                                <span class="info-value">#{{ $record->appointment->appointment_number ?? $record->appointment->id }} - {{ $record->appointment->appointment_date ? \Carbon\Carbon::parse($record->appointment->appointment_date)->format('d M Y') : '' }} {{ $record->appointment->appointment_time ?? '' }}</span>
                            </div>
                            @endif
                        </div>
                    </section>

                    @if($patient)
                    <section class="section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-user"></i></div>
                            <h3 class="section-title">Patient</h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Full name</span>
                                <span class="info-value">{{ $patient->first_name }} {{ $patient->last_name }}</span>
                            </div>
                            @if($patient->date_of_birth)
                            <div class="info-item">
                                <span class="info-label">DOB</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') }} ({{ \Carbon\Carbon::parse($patient->date_of_birth)->age }}y)</span>
                            </div>
                            @endif
                            @if($patient->gender)
                            <div class="info-item">
                                <span class="info-label">Gender</span>
                                <span class="info-value">{{ ucfirst($patient->gender) }}</span>
                            </div>
                            @endif
                            @if($patient->phone)
                            <div class="info-item">
                                <span class="info-label">Phone</span>
                                <span class="info-value">{{ $patient->phone }}</span>
                            </div>
                            @endif
                        </div>
                    </section>
                    @endif

                    <section class="section section-clinical">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-notes-medical"></i></div>
                            <h3 class="section-title">Clinical</h3>
                        </div>
                        <div class="clinical-grid">
                            <div class="clinical-block">
                                <div class="clinical-label">PC (Presenting complaint)</div>
                                <div class="clinical-content">{!! nl2br(e($record->presenting_complaint ?? $record->chief_complaint ?? 'N/A')) !!}</div>
                            </div>
                            <div class="clinical-block">
                                <div class="clinical-label">HPC (History)</div>
                                <div class="clinical-content">{!! nl2br(e($record->history_of_presenting_complaint ?? $record->present_illness ?? 'N/A')) !!}</div>
                            </div>
                            <div class="clinical-block">
                                <div class="clinical-label">PMH</div>
                                <div class="clinical-content">{!! nl2br(e($record->past_medical_history ?? 'N/A')) !!}</div>
                            </div>
                            <div class="clinical-block">
                                <div class="clinical-label">DH (Drug history)</div>
                                <div class="clinical-content">{!! nl2br(e($record->drug_history ?? 'N/A')) !!}</div>
                            </div>
                            <div class="clinical-block allergy">
                                <div class="clinical-label">Allergies</div>
                                <div class="clinical-content">{!! nl2br(e($record->allergies ?? 'N/A')) !!}</div>
                            </div>
                            <div class="clinical-block">
                                <div class="clinical-label">SH</div>
                                <div class="clinical-content">{!! nl2br(e($record->social_history ?? 'N/A')) !!}</div>
                            </div>
                            <div class="clinical-block full-width">
                                <div class="clinical-label">Plan</div>
                                <div class="clinical-content">{!! nl2br(e($record->plan ?? 'N/A')) !!}</div>
                            </div>
                        </div>
                    </section>

                    @php $vitals = $record->vital_signs ?? []; @endphp
                    @if(!empty(array_filter($vitals)))
                    <section class="section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-heartbeat"></i></div>
                            <h3 class="section-title">Vitals</h3>
                        </div>
                        <div class="vitals-grid">
                            @if(!empty($vitals['blood_pressure']))<div class="vital-pill"><div class="vital-value">{{ $vitals['blood_pressure'] }}</div><div class="vital-label">BP</div></div>@endif
                            @if(!empty($vitals['temperature']))<div class="vital-pill"><div class="vital-value">{{ $vitals['temperature'] }}{{ !str_contains($vitals['temperature'] ?? '', '°') ? '°C' : '' }}</div><div class="vital-label">Temp</div></div>@endif
                            @if(!empty($vitals['pulse']))<div class="vital-pill"><div class="vital-value">{{ $vitals['pulse'] }}{{ !str_contains($vitals['pulse'] ?? '', 'bpm') ? ' bpm' : '' }}</div><div class="vital-label">Pulse</div></div>@endif
                            @if(!empty($vitals['oxygen_saturation']))<div class="vital-pill"><div class="vital-value">{{ $vitals['oxygen_saturation'] }}{{ !str_contains($vitals['oxygen_saturation'] ?? '', '%') ? '%' : '' }}</div><div class="vital-label">SpO₂</div></div>@endif
                            @if(!empty($vitals['weight']))<div class="vital-pill"><div class="vital-value">{{ $vitals['weight'] }}</div><div class="vital-label">Weight</div></div>@endif
                            @if(!empty($vitals['height']))<div class="vital-pill"><div class="vital-value">{{ $vitals['height'] }}</div><div class="vital-label">Height</div></div>@endif
                        </div>
                    </section>
                    @endif
                </main>

                <aside>
                    @if($doctor)
                    <div class="signature-block">
                        <span class="signature-name">{{ formatDoctorName($doctor->name ?? $doctor->first_name . ' ' . $doctor->last_name) }}</span>
                        <span class="signature-role">{{ $doctor->specialization ?? 'GP' }}</span>
                        <span class="signature-clinic">{{ getClinicName() }}</span>
                        <span class="signature-date">{{ $recordDate ? $recordDate->format('d M Y') : $record->created_at->format('d M Y') }}</span>
                    </div>
                    @endif

                    @if($record->prescriptions && $record->prescriptions->count() > 0)
                    <section class="section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-prescription-bottle-alt"></i></div>
                            <h3 class="section-title">Prescribed</h3>
                        </div>
                        <ul class="rx-list">
                            @foreach($record->prescriptions->take(10) as $rx)
                            <li class="rx-item">
                                <span class="rx-name">{{ $rx->medication_name }}</span>
                                <span class="rx-detail">{{ $rx->dosage ?? '' }} {{ $rx->frequency ?? '' }}</span>
                            </li>
                            @endforeach
                            @if($record->prescriptions->count() > 10)
                                <li class="rx-item"><span class="rx-detail">+ {{ $record->prescriptions->count() - 10 }} more</span></li>
                            @endif
                        </ul>
                    </section>
                    @endif
                </aside>
            </div>
        </div>
        @endforeach

        <footer class="print-footer">
            Confidential medical record. For patient use when attending other healthcare providers. {{ getClinicName() }} © {{ date('Y') }}
        </footer>
    </div>
</body>
</html>
