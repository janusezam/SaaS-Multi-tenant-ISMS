<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <p>Hello {{ $university->tenant_admin_name ?? 'Tenant Admin' }},</p>

    <p>{{ $introLine }}</p>

    <ul>
        @foreach ($details as $label => $value)
            <li><strong>{{ $label }}:</strong> {{ $value }}</li>
        @endforeach
    </ul>

    <p>If you have questions about billing or plan changes, please contact support.</p>

    <p>Regards,<br>ISMS Notifications</p>
</body>
</html>
