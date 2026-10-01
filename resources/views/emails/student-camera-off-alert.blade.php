<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #2d3748; max-width: 640px; margin: 0 auto;">
    <div style="background: #1f6feb; color: #ffffff; padding: 20px 24px; border-radius: 8px 8px 0 0;">
        <h2 style="margin: 0; font-size: 22px;">Student Camera Alert</h2>
        <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.9;">Automated class monitoring notification</p>
    </div>

    <div style="border: 1px solid #e5e7eb; border-top: 0; padding: 22px 24px; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 16px 0;">
            Dear {{ $parent->name ?: 'Parent/Guardian' }},
        </p>

        <p style="margin: 0 0 16px 0;">
            This is to inform you that <strong>{{ $student->name ?: 'your child' }}</strong> has kept the camera off
            for at least <strong>{{ $minutes }} minutes</strong> during a BigBlueButton class.
        </p>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 16px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; width: 38%; font-weight: bold;">Student</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $student->name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Meeting</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">
                    {{ optional($meeting)->meeting_name ?: optional($meeting)->meeting_id ?: 'Class meeting' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb; font-weight: bold;">Camera off since</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $cameraOffSince->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>

        <p style="margin: 0 0 10px 0;">
            Please follow up with your child and ensure camera policy is followed during live classes.
        </p>

        <p style="margin: 18px 0 0 0;">
            Regards,<br>
            School System
        </p>
    </div>
</div>
