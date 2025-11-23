<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 16px;
            margin: 0;
        }
        
        .prescription-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .info-section {
            flex: 1;
            min-width: 250px;
            margin-right: 20px;
        }
        
        .info-section:last-child {
            margin-right: 0;
        }
        
        .info-section h3 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .medications {
            margin: 30px 0;
        }
        
        .medications h3 {
            color: #007bff;
            margin: 0 0 20px 0;
            font-size: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        
        .medication-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        
        .medication-name {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .medication-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }
        
        .medication-detail {
            background: white;
            padding: 8px;
            border-radius: 3px;
            border-left: 3px solid #007bff;
        }
        
        .medication-detail .label {
            font-weight: bold;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            display: block;
        }
        
        .medication-detail .value {
            color: #333;
            font-size: 14px;
        }
        
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .instructions .label {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
            display: block;
        }
        
        .additional-info {
            margin-top: 30px;
        }
        
        .notes-section {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .notes-section h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 10px;
            height: 30px;
        }
        
        .signature-label {
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-dispensed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .print-container {
                max-width: none;
                margin: 0;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'Hospital Management System') }}</h1>
            <p class="subtitle">Medical Prescription</p>
        </div>

        <!-- Prescription Information -->
        <div class="prescription-info">
            <div class="info-section">
                <h3>Patient Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Patient ID:</span>
                    <span class="info-value">{{ $prescription->patient->patient_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value">{{ $prescription->patient->date_of_birth ? $prescription->patient->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $prescription->patient->phone ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Prescription Details</h3>
                <div class="info-row">
                    <span class="info-label">Prescription #:</span>
                    <span class="info-value">{{ $prescription->prescription_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Prescribed:</span>
                    <span class="info-value">{{ $prescription->prescription_date ? \Carbon\Carbon::parse($prescription->prescription_date)->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">{{ ucfirst($prescription->prescription_type) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ $prescription->status }}">{{ ucfirst($prescription->status) }}</span>
                </div>
                @if($prescription->refills_allowed > 0)
                <div class="info-row">
                    <span class="info-label">Refills Allowed:</span>
                    <span class="info-value">{{ $prescription->refills_allowed }}</span>
                </div>
                @endif
            </div>

            <div class="info-section">
                <h3>Doctor Information</h3>
                @if($prescription->doctor)
                <div class="info-row">
                    <span class="info-label">Doctor:</span>
                    <span class="info-value">{{ $prescription->doctor->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Specialisation:</span>
                    <span class="info-value">{{ $prescription->doctor->specialization ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">License #:</span>
                    <span class="info-value">{{ $prescription->doctor->license_number ?? 'N/A' }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="info-value">Doctor information not available</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Medications -->
        <div class="medications">
            <h3>Prescribed Medications</h3>
            @if(is_array($prescription->medications) && count($prescription->medications) > 0)
                @foreach($prescription->medications as $medication)
                <div class="medication-item">
                    <div class="medication-name">{{ $medication['name'] ?? 'N/A' }}</div>
                    
                    <div class="medication-details">
                        <div class="medication-detail">
                            <span class="label">Dosage</span>
                            <span class="value">{{ $medication['dosage'] ?? 'N/A' }}</span>
                        </div>
                        <div class="medication-detail">
                            <span class="label">Frequency</span>
                            <span class="value">{{ $medication['frequency'] ?? 'N/A' }}</span>
                        </div>
                        <div class="medication-detail">
                            <span class="label">Duration</span>
                            <span class="value">{{ $medication['duration'] ?? 'N/A' }}</span>
                        </div>
                        @if(isset($medication['form']) && $medication['form'])
                        <div class="medication-detail">
                            <span class="label">Form</span>
                            <span class="value">{{ $medication['form'] }}</span>
                        </div>
                        @endif
                    </div>
                    
                    @if(isset($medication['instructions']) && $medication['instructions'])
                    <div class="instructions">
                        <span class="label">Special Instructions:</span>
                        {{ $medication['instructions'] }}
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div class="medication-item">
                    <p>No medications specified</p>
                </div>
            @endif
        </div>

        <!-- Additional Information -->
        <div class="additional-info">
            @if($prescription->diagnosis)
            <div class="notes-section">
                <h4>Diagnosis</h4>
                <p>{{ $prescription->diagnosis }}</p>
            </div>
            @endif

            @if($prescription->notes)
            <div class="notes-section">
                <h4>Doctor's Notes</h4>
                <p>{{ $prescription->notes }}</p>
            </div>
            @endif

            @if($prescription->pharmacist_notes)
            <div class="notes-section">
                <h4>Pharmacist Notes</h4>
                <p>{{ $prescription->pharmacist_notes }}</p>
            </div>
            @endif

            @if($prescription->follow_up_date)
            <div class="notes-section">
                <h4>Follow-up Date</h4>
                <p>{{ \Carbon\Carbon::parse($prescription->follow_up_date)->format('M d, Y') }}</p>
            </div>
            @endif
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Doctor's Signature</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Pharmacist's Signature</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Date Dispensed</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus for better printing experience
        window.onload = function() {
            window.focus();
        };
    </script>
</body>
</html>
