@php
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: '[STUDENT NAME]';
    $guardianName = trim(($guardian->first_name ?? '') . ' ' . ($guardian->last_name ?? ''));
@endphp

<div style="font-family: Arial, sans-serif; color: #1f2933; line-height: 1.6; max-width: 720px; margin: 0 auto; background: #ffffff;">
    <div style="text-align: center; padding: 28px 24px 18px; border-bottom: 2px solid #123f69;">
        <h1 style="margin: 0; color: #123f69; font-size: 24px; font-weight: 700;">{{ $academyName }}</h1>
        <p style="margin: 4px 0 0; color: #475569; font-size: 13px;">{{ $academyInitiative }}</p>
        <p style="margin: 8px 0 0; color: #475569; font-size: 13px;">{{ $academyAddress }}</p>
        <p style="margin: 4px 0 0; color: #475569; font-size: 13px;">{{ $academyWebsite }} | WhatsApp: {{ $academyWhatsapp }}</p>
        <p style="margin: 8px 0 0; color: #475569; font-size: 12px;">{{ $academyRegistrations }}</p>
    </div>

    <div style="padding: 28px 30px;">
        <p style="text-align: right; margin: 0 0 26px; font-size: 14px;">Date: {{ $generatedDate }}</p>

        <h2 style="margin: 0 0 24px; text-align: center; color: #123f69; font-size: 20px; letter-spacing: 0; font-weight: 700;">LETTER OF ACCEPTANCE</h2>

        <p style="margin: 0 0 16px; font-size: 15px;">Dear {{ $studentName }},</p>

        <p style="margin: 0 0 18px; font-size: 15px;">
            We are pleased to inform you that you have been formally accepted into the
            <strong>{{ $academyName }} {{ $programmeTitle }}</strong>, commencing
            <strong>{{ $acceptanceStartDate }}</strong>. This offer of acceptance is subject to the
            completion of all required enrolment formalities.
        </p>

        <div style="border: 1px solid #d7dee8; background: #f8fbff; padding: 18px 20px; margin: 20px 0;">
            <h3 style="margin: 0 0 12px; color: #123f69; font-size: 16px;">Programme Details:</h3>
            <p style="margin: 0 0 6px;"><strong>Student Name:</strong> {{ $studentName }}</p>
            <p style="margin: 0 0 6px;"><strong>Programme:</strong> {{ $programmeName }}</p>
            <p style="margin: 0 0 6px;"><strong>Subjects:</strong> {{ $subjectsText }}</p>
            <p style="margin: 0 0 6px;"><strong>Acceptance Start Date:</strong> {{ $acceptanceStartDate }}</p>
            <p style="margin: 0 0 6px;"><strong>School Year Start Date:</strong> {{ $schoolYearStartDate }}</p>
            <p style="margin: 0;"><strong>Mode of Study:</strong> {{ $studyMode }}</p>
        </div>

        <h3 style="margin: 22px 0 8px; color: #123f69; font-size: 16px;">Enrolment Confirmation:</h3>
        <p style="margin: 0 0 18px; font-size: 15px;">
            This acceptance letter also serves as confirmation of provisional enrolment at
            {{ $academyName }}, effective {{ $acceptanceStartDate }}, and valid through to the
            commencement of the school year in {{ $schoolYearStartDate }}. The student is expected
            to complete all onboarding requirements prior to the official school start date.
        </p>

        <p style="margin: 0 0 18px; font-size: 15px;">
            {{ $academyName }} operates under {{ $academyCompany }}, a fully accredited and registered
            institution in the United Kingdom (UKPRN: 10099428, Academy Licence No: 6581657,
            ICO Registration: ZB985950, OTHM: DC2510950), recognised by Ofqual and GOV.UK.
        </p>

        <p style="margin: 0 0 24px; font-size: 15px;">
            We warmly welcome the student to the NextGen Academy community and look forward to
            supporting her academic journey. Should you require any further information, please do
            not hesitate to contact us at {{ $academyWebsite }} or via WhatsApp at {{ $academyWhatsapp }}.
        </p>

        <div style="border: 1px solid #d7dee8; padding: 16px 18px; margin: 22px 0; background: #ffffff;">
            <h3 style="margin: 0 0 12px; color: #123f69; font-size: 16px;">Portal Access</h3>
            @if($guardianName)
                <p style="margin: 0 0 10px; font-size: 14px;">Guardian: {{ $guardianName }}</p>
            @endif
            <p style="margin: 0 0 8px; font-size: 14px;"><strong>Parent Login:</strong> {{ $parentUser->email }}</p>
            <p style="margin: 0 0 8px; font-size: 14px;"><strong>Student Login:</strong> {{ $studentUser->email }}</p>
            <p style="margin: 0; font-size: 14px;"><strong>Temporary Password:</strong> Password@123</p>
        </div>

        <p style="margin: 24px 0 0; font-size: 15px;">Yours sincerely,</p>
        <p style="margin: 16px 0 0; font-size: 15px;">
            <strong>{{ $academyName }}</strong><br>
            Admissions Office<br>
            {{ $academyCompany }}<br>
            {{ $academyAddress }}<br>
            {{ $academyWebsite }} | WhatsApp: {{ $academyWhatsapp }}
        </p>
    </div>

    <div style="border-top: 1px solid #d7dee8; padding: 14px 20px; text-align: center; color: #64748b; font-size: 12px;">
        {{ $academyName }} | {{ $academyInitiative }} | UKPRN: 10099428
    </div>
</div>
