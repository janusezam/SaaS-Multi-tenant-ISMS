<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Password OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <p>Hello {{ $recipientName }},</p>

    <p>Your one-time password (OTP) for ISMS tenant password reset is:</p>

    <p style="font-size: 28px; font-weight: 700; letter-spacing: 4px; color: #0f766e;">{{ $otpCode }}</p>

    <p>This OTP expires in {{ $expiresInMinutes }} minutes.</p>

    <p>If you did not request this reset, please ignore this email.</p>

    <p>Regards,<br>ISMS Notifications</p>
</body>
</html>
