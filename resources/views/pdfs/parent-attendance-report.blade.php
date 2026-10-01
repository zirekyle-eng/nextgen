<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        .header { text-align: center; margin-bottom: 18px; }
        .header h2 { margin: 0 0 4px; }
        .meta { margin-bottom: 12px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 4px 6px; }
        .summary { margin: 12px 0; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 6px; border: 1px solid #e5e7eb; text-align: center; }
        table.report { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.report th, table.report td { border: 1px solid #e5e7eb; padding: 6px; }
        table.report th { background: #f3f4f6; text-align: left; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Attendance Report</h2>
        <div class="muted">{{ $termLabel }} · {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</div>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td><strong>Student:</strong> {{ $student->name }}</td>
                <td><strong>Parent:</strong> {{ $parent->name }}</td>
            </tr>
            <tr>
                <td><strong>Class:</strong> {{ $record->my_class->name ?? '-' }}</td>
                <td><strong>Section:</strong> {{ $record->section->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Present</strong><br>{{ $summary['present'] }}</td>
                <td><strong>Late</strong><br>{{ $summary['late'] }}</td>
                <td><strong>Absent</strong><br>{{ $summary['absent'] }}</td>
            </tr>
        </table>
    </div>

    <table class="report">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th>Meeting</th>
                <th style="width: 8%;">Join</th>
                <th style="width: 8%;">Left</th>
                <th style="width: 8%;">Cam Opens</th>
                <th style="width: 10%;">Cam First</th>
                <th style="width: 10%;">Cam Last</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 8%;">Late</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['meeting'] }}</td>
                    <td>{{ $row['join_at'] }}</td>
                    <td>{{ $row['left_at'] }}</td>
                    <td>{{ $row['camera_on_count'] }}</td>
                    <td>{{ $row['first_camera_on_at'] }}</td>
                    <td>{{ $row['last_camera_on_at'] }}</td>
                    <td>{{ ucfirst($row['status']) }}</td>
                    <td>{{ $row['late_minutes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="muted">No attendance records for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
