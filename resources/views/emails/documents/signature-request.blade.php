<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Required</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .document-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .document-info h3 {
            margin: 0 0 10px;
            color: #667eea;
        }
        .document-info p {
            margin: 5px 0;
            color: #666;
        }
        .custom-message {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .custom-message p {
            margin: 0;
            font-style: italic;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .text-center {
            text-align: center;
        }
        .expiry-notice {
            font-size: 14px;
            color: #dc3545;
            margin-top: 15px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .link-fallback {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Signature Required</h1>
            <p>Please review and sign your document</p>
        </div>

        <div class="email-body">
            <p class="greeting">Dear {{ $patient->full_name }},</p>

            <p>You have a document that requires your signature from <strong>{{ $hospitalName }}</strong>.</p>

            <div class="document-info">
                <h3>{{ $document->title }}</h3>
                <p><strong>Document Type:</strong> {{ ucfirst($document->type) }}</p>
                <p><strong>Created:</strong> {{ $document->created_at->format('F d, Y') }}</p>
            </div>

            @if($customMessage)
            <div class="custom-message">
                <p><strong>Message from your healthcare provider:</strong></p>
                <p>{{ $customMessage }}</p>
            </div>
            @endif

            <div class="text-center">
                <a href="{{ $signatureUrl }}" class="cta-button">
                    Review & Sign Document
                </a>
                <p class="expiry-notice">
                    <i>This link will expire in {{ $expiresIn }}.</i>
                </p>
            </div>

            <p>By signing this document, you acknowledge that you have read and understood its contents.</p>

            <div class="link-fallback">
                <strong>Can't click the button?</strong> Copy and paste this link into your browser:<br>
                {{ $signatureUrl }}
            </div>
        </div>

        <div class="email-footer">
            <p><strong>{{ $hospitalName }}</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you did not expect this email or have questions, please contact your healthcare provider.</p>
        </div>
    </div>
</body>
</html>
