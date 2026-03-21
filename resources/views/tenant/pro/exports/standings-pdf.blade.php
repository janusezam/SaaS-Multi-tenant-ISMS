<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Standings Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Intramurals Standings</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Team</th>
                <th>P</th>
                <th>W</th>
                <th>D</th>
                <th>L</th>
                <th>GF</th>
                <th>GA</th>
                <th>GD</th>
                <th>Pts</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['team'] }}</td>
                    <td>{{ $row['played'] }}</td>
                    <td>{{ $row['wins'] }}</td>
                    <td>{{ $row['draws'] }}</td>
                    <td>{{ $row['losses'] }}</td>
                    <td>{{ $row['gf'] }}</td>
                    <td>{{ $row['ga'] }}</td>
                    <td>{{ $row['gd'] }}</td>
                    <td>{{ $row['points'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No completed games.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
