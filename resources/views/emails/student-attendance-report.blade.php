<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; max-width: 640px; margin: 0 auto;">
    <div style="background: #0f766e; color: #ffffff; padding: 18px 22px; border-radius: 8px 8px 0 0;">
        <h2 style="margin: 0; font-size: 20px;">Attendance Report</h2>
        <p style="margin: 6px 0 0 0; font-size: 13px; opacity: 0.9;">{{ strtoupper($termKey) }} Summary</p>
    </div>

    <div style="border: 1px solid #e5e7eb; border-top: 0; padding: 20px 22px; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 14px 0;">Dear {{ $parent->name ?: 'Parent/Guardian' }},</p>

        <p style="margin: 0 0 14px 0;">
            Please find attached the attendance report for {{ $student->name ?: 'your child' }}
            from {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}.
        </p>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 14px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; width: 38%; font-weight: bold;">Present</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $summary['present'] ?? 0 }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Late</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $summary['late'] ?? 0 }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Absent</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $summary['absent'] ?? 0 }}</td>
            </tr>
        </table>

        <p style="margin: 0;">Thank you.</p>
    </div>
</div>
