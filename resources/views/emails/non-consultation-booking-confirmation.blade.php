<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <p>Dear {{ $patient_name }},</p>

    <p>Thank you for your booking with {{ $hospital_name }}.</p>

    <p><strong>Service:</strong> {{ $service_name }}<br>
    <strong>Reference:</strong> {{ $order_number }}<br>
    <strong>Amount:</strong> {{ $amount_paid }}</p>

    <p>You will be contacted by <strong>{{ $doctor_name }}</strong> at {{ $clinic_name }} regarding your booking for <strong>{{ $service_name }}</strong>.</p>

    <p>If you have any questions in the meantime, please contact us{{ $hospital_phone ? ' on ' . $hospital_phone : '' }}.</p>

    <p style="color: #666; font-size: 12px; margin-top: 24px;">
        This message relates to a non-consultation service. No appointment time has been scheduled.
    </p>
</body>
</html>
