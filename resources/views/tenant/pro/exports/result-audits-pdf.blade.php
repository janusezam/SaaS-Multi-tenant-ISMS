<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Result Audits Export</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <h1>Result Audits Export</h1>
    <table>
        <thead>
            <tr>
                <th>Changed At</th>
                <th>Sport</th>
                <th>Match</th>
                <th>Changed By</th>
                <th>Status</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['changed_at'] }}</td>
                    <td>{{ $row['sport'] }}</td>
                    <td>{{ $row['match'] }}</td>
                    <td>{{ $row['changed_by'] }}</td>
                    <td>{{ $row['previous_status'] }} -> {{ $row['new_status'] }}</td>
                    <td>{{ $row['previous_score'] }} -> {{ $row['new_score'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No result audit rows found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
