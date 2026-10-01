<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; max-width: 640px; margin: 0 auto;">
    <div style="background: #7c2d12; color: #ffffff; padding: 18px 22px; border-radius: 8px 8px 0 0;">
        <h2 style="margin: 0; font-size: 20px;">Daily Absence Alert</h2>
        <p style="margin: 6px 0 0 0; font-size: 13px; opacity: 0.9;">Full day absence notification</p>
    </div>

    <div style="border: 1px solid #e5e7eb; border-top: 0; padding: 20px 22px; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 14px 0;">Dear {{ $parent->name ?: 'Parent/Guardian' }},</p>

        <p style="margin: 0 0 14px 0;">
            {{ $student->name ?: 'Your child' }} was absent for all classes on
            <strong>{{ $date }}</strong>.
        </p>

        <p style="margin: 0;">Please contact the school if there was a valid reason.</p>
    </div>
</div>
