<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Admin Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <p>Hello {{ $recipientName }},</p>

    <p>
        You have been invited as tenant admin for <strong>{{ $university->name }}</strong>.
        Use the secure link below to set your password and activate your access.
    </p>

    <p>
        <a href="{{ $inviteUrl }}" style="display: inline-block; padding: 10px 16px; background: #0f766e; color: #ffffff; text-decoration: none; border-radius: 6px;">
            Set Your Password
        </a>
    </p>

    <p>If the button does not work, open this URL:</p>
    <p><a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a></p>

    <p>This link expires in 48 hours.</p>

    <p>Regards,<br>ISMS Notifications</p>
</body>
</html>
