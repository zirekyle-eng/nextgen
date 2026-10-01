<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; max-width: 640px; margin: 0 auto;">
    <div style="background: #1d4ed8; color: #ffffff; padding: 18px 22px; border-radius: 8px 8px 0 0;">
        <h2 style="margin: 0; font-size: 20px;">Attendance Summary</h2>
        <p style="margin: 6px 0 0 0; font-size: 13px; opacity: 0.9;">After-class notification</p>
    </div>

    <div style="border: 1px solid #e5e7eb; border-top: 0; padding: 20px 22px; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 14px 0;">Dear {{ $parent->name ?: 'Parent/Guardian' }},</p>

        <p style="margin: 0 0 14px 0;">
            Attendance status for {{ $student->name ?: 'your child' }}:
            <strong style="text-transform: uppercase;">{{ $status }}</strong>.
        </p>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 14px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; width: 38%; font-weight: bold;">Student</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $student->name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Meeting</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ optional($meeting)->meeting_name ?: optional($meeting)->meeting_id ?: 'Class meeting' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Scheduled start</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ optional($scheduledStartAt)->format('Y-m-d H:i') ?: '-' }}</td>
            </tr>
            @if($status === 'late')
                <tr>
                    <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Late minutes</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $lateMinutes }}</td>
                </tr>
            @endif
        </table>

        <p style="margin: 0;">Thank you.</p>
    </div>
</div>
