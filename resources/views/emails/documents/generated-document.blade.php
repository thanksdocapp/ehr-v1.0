<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document from {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .document-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .document-info table {
            width: 100%;
        }
        .document-info td {
            padding: 5px 0;
        }
        .document-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
        .message-box {
            background: #e8f4fd;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Document Attached</h1>
    </div>

    <div class="content">
        <p>Hello,</p>

        <p>Please find attached a document from {{ config('app.name') }}.</p>

        <div class="document-info">
            <table>
                <tr>
                    <td>Document:</td>
                    <td>{{ $document->title }}</td>
                </tr>
                <tr>
                    <td>Type:</td>
                    <td>{{ ucfirst($document->template->type ?? 'Document') }}</td>
                </tr>
                <tr>
                    <td>Patient:</td>
                    <td>{{ $document->patient->full_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td>{{ $document->created_at->format('F d, Y') }}</td>
                </tr>
            </table>
        </div>

        @if($customMessage)
            <div class="message-box">
                <strong>Message from sender:</strong><br>
                {{ $customMessage }}
            </div>
        @endif

        <p>The document is attached to this email as a PDF file.</p>

        <p>If you have any questions, please contact us.</p>

        <p>
            Best regards,<br>
            {{ config('app.name') }} Team
        </p>
    </div>

    <div class="footer">
        <p>This email was sent from {{ config('app.name') }}.</p>
        <p>This document is confidential and intended for the recipient only.</p>
    </div>
</body>
</html>
