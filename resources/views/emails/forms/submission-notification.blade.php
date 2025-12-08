<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Completed - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 5px 0;
        }
        .info-box td:first-child {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        .form-data {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .form-data h3 {
            margin-top: 0;
            color: #2e7d32;
            border-bottom: 1px solid #c8e6c9;
            padding-bottom: 10px;
        }
        .form-field {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .form-field:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .form-field-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .form-field-value {
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Form Completed</h1>
    </div>

    <div class="content">
        <p>Hello {{ $formRequest->requester->name ?? 'Doctor' }},</p>

        <p>A patient has completed a form you sent. Please review the submitted information below.</p>

        <div class="info-box">
            <table>
                <tr>
                    <td>Form:</td>
                    <td>{{ $template->name }}</td>
                </tr>
                <tr>
                    <td>Patient:</td>
                    <td>{{ $patient->full_name ?? $patient->first_name . ' ' . $patient->last_name }}</td>
                </tr>
                <tr>
                    <td>Submitted:</td>
                    <td>{{ $formRequest->completed_at->format('F d, Y \a\t H:i') }}</td>
                </tr>
                <tr>
                    <td>Reference:</td>
                    <td>#{{ $formRequest->id }}</td>
                </tr>
            </table>
        </div>

        <div class="form-data">
            <h3>Submitted Data</h3>
            @if($formData && count($formData) > 0)
                @foreach($formData as $fieldName => $fieldValue)
                    <div class="form-field">
                        <div class="form-field-label">{{ ucwords(str_replace('_', ' ', $fieldName)) }}</div>
                        <div class="form-field-value">
                            @if(is_array($fieldValue))
                                {{ implode(', ', $fieldValue) }}
                            @elseif(str_starts_with($fieldValue ?? '', 'data:image'))
                                <em>[Signature captured]</em>
                            @else
                                {{ $fieldValue ?: '-' }}
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <p>No data submitted.</p>
            @endif
        </div>

        <p>You can view the full submission in your dashboard.</p>

        <p>
            Best regards,<br>
            {{ config('app.name') }} System
        </p>
    </div>

    <div class="footer">
        <p>This is an automated notification from {{ config('app.name') }}.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>
