<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document from {{ $clinicName }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #2c3e50;">{{ $clinicName }}</h2>
    </div>

    <div style="margin-bottom: 20px;">
        <p>Dear {{ $recipientName }},</p>
        
        <p>Please find attached a document from {{ $clinicName }} regarding {{ $patient->full_name }}.</p>
        
        <div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0;">
            <strong>Document Details:</strong><br>
            <strong>Title:</strong> {{ $document->title }}<br>
            <strong>Type:</strong> {{ ucfirst($document->type) }}<br>
            <strong>Patient:</strong> {{ $patient->full_name }}<br>
            @if($document->created_at)
                <strong>Date:</strong> {{ $document->created_at->format('F d, Y') }}
            @endif
        </div>
        
        <p>The document is attached to this email as a PDF file. Please review it at your earliest convenience.</p>
        
        <p>If you have any questions or concerns, please contact us.</p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
        <p>This is an automated message from {{ $clinicName }}.</p>
        <p>Please do not reply to this email directly.</p>
    </div>

    {{-- Email open tracking pixel --}}
    @if(isset($trackingToken) && $trackingToken)
    <img src="{{ route('document.track.open', $trackingToken) }}" width="1" height="1" style="display:none;visibility:hidden;width:1px;height:1px;" alt="" />
    @endif
</body>
</html>

