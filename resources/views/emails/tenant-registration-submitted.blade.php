<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Tenant Registration Pending</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <p>Hello {{ $adminName }},</p>

    <p>A new tenant account request is waiting for your review.</p>

    <ul>
        <li>Name: {{ $requesterName }}</li>
        <li>Email: {{ $requesterEmail }}</li>
        <li>Requested Role: {{ str_replace('_', ' ', ucfirst($requesterRole)) }}</li>
    </ul>

    <p>Sign in to your tenant admin users page to review and approve or delete this request.</p>

    <p>Regards,<br>ISMS Notifications</p>
</body>
</html>
