@php
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: '[STUDENT NAME]';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Letter of Acceptance</title>
    <style>
        @page { margin: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #eef2f9;
            color: #2a3b5d;
            margin: 0;
            font-size: 9.6px;
            line-height: 1.55;
        }

        .page {
            width: 100%;
            background: #ffffff;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 260px;
            left: 0;
            width: 100%;
            text-align: center;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 92px;
            font-weight: 700;
            color: #173c70;
            opacity: 0.035;
            letter-spacing: 6px;
        }

        /* ===== HEADER ===== */
        .header-wrap {
            background: #123058;
            padding: 0;
        }
        .header-gold-bar {
            height: 5px;
            background: #c9a24b;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 20px 26px;
            vertical-align: middle;
        }
        .header-logo-cell {
            width: 100px;
            text-align: left;
        }
        .header-logo {
            display: inline-block;
            max-width: 100px;
            max-height: 50px;
        }
        .header-academy-name {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 4px;
            letter-spacing: 0.4px;
        }
        .header-tagline {
            font-size: 8px;
            color: #b9c8e2;
            margin: 0 0 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-contact {
            font-size: 7.6px;
            color: #d7e1f2;
        }
        .header-contact span {
            display: inline-block;
            margin-right: 10px;
        }
        .header-badge-cell {
            text-align: right;
            width: 150px;
        }
        .header-badge {
            display: inline-block;
            border: 1px solid #c9a24b;
            color: #f1d999;
            font-size: 7.6px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 7px 12px;
            border-radius: 3px;
        }

        /* ===== BODY ===== */
        .body {
            padding: 26px 34px 30px;
            position: relative;
        }

        .date-row {
            width: 100%;
            margin: 0 0 20px;
        }
        .date-value {
            float: right;
            font-size: 8.2px;
            font-weight: 700;
            color: #6c7b95;
            border-bottom: 1px solid #d8e0ef;
            padding-bottom: 3px;
        }
        .clear { clear: both; }

        .title-block {
            text-align: center;
            margin: 6px 0 24px;
        }
        .title-eyebrow {
            font-size: 7.8px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #c9a24b;
            font-weight: 700;
            margin: 0 0 6px;
        }
        .title-main {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 20px;
            font-weight: 700;
            color: #173c70;
            margin: 0 0 10px;
            letter-spacing: 0.3px;
        }
        .title-rule {
            font-size: 9px;
            color: #c9a24b;
            letter-spacing: 10px;
        }

        .paragraph {
            margin: 0 0 13px;
            text-align: justify;
        }
        .paragraph strong {
            font-weight: 700;
            color: #173c70;
        }

        .intro-box {
            background: #f8fbff;
            border-left: 3px solid #c9a24b;
            border-top: 1px solid #e3ebf7;
            border-right: 1px solid #e3ebf7;
            border-bottom: 1px solid #e3ebf7;
            border-radius: 0 5px 5px 0;
            padding: 12px 16px;
            margin: 4px 0 18px;
            font-style: italic;
            color: #3a4c6e;
        }

        .section-title {
            font-size: 10.5px;
            font-weight: 700;
            color: #173c70;
            margin: 22px 0 3px;
        }
        .section-underline {
            width: 46px;
            height: 2px;
            background: #c9a24b;
            margin: 0 0 12px;
            font-size: 0;
        }

        /* Details table */
        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 0 0 6px;
        }
        .details-table td {
            width: 50%;
            background: #f8fbff;
            border: 1px solid #e3ebf7;
            border-radius: 5px;
            padding: 9px 12px;
            vertical-align: top;
        }
        .details-label {
            display: block;
            font-size: 7.2px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #8493ad;
            font-weight: 700;
            margin: 0 0 3px;
        }
        .details-value {
            display: block;
            font-size: 9.4px;
            font-weight: 700;
            color: #223357;
        }

        /* ===== SIGNATURE ===== */
        .sign-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
        }
        .sign-table td {
            vertical-align: bottom;
        }
        .sign-text p {
            margin: 0 0 5px;
        }
        .sign-name {
            font-weight: 700;
            color: #173c70;
            font-size: 10.5px;
        }
        .seal-cell {
            width: 110px;
            text-align: center;
        }
        .seal {
            display: table;
            width: 82px;
            height: 82px;
            border: 2px dashed #c9a24b;
            border-radius: 50%;
            margin: 0 0 0 auto;
        }
        .seal-inner {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .seal-text-1 {
            font-size: 6.6px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #c9a24b;
            font-weight: 700;
        }
        .seal-text-2 {
            font-size: 8px;
            font-weight: 700;
            color: #173c70;
            margin-top: 2px;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e8edf7;
            font-size: 7.3px;
            text-align: center;
            color: #8493ad;
        }
        .footer strong {
            color: #6c7b95;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="watermark">{{ $academyInitiative ?? 'NEXTGEN' }}</div>

        <div class="header-wrap">
            <div class="header-gold-bar"></div>
            <table class="header-table">
                <tr>
                    <td class="header-logo-cell">
                        @if(!empty($academyLogo))
                            <img class="header-logo" src="{{ $academyLogo }}" alt="{{ $academyName }} Logo">
                        @endif
                    </td>
                    <td>
                        <p class="header-academy-name">{{ $academyName }}</p>
                        <p class="header-tagline">{{ $academyInitiative }}</p>
                        <p class="header-contact">
                            <span>{{ $academyAddress }}</span>
                            <span>{{ $academyWebsite }}</span>
                            <span>WhatsApp: {{ $academyWhatsapp }}</span>
                        </p>
                    </td>
                    <td class="header-badge-cell">
                        <span class="header-badge">Official Admission</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body">
            <div class="date-row">
                <span class="date-value">Date: {{ $generatedDate }}</span>
                <span class="clear"></span>
            </div>

            <div class="title-block">
                <p class="title-eyebrow">NextGen Admissions</p>
                <p class="title-main">Letter of Acceptance</p>
                <p class="title-rule">&#9670;</p>
            </div>

            <p class="paragraph"><strong>Dear {{ $studentName }},</strong></p>

            <p class="paragraph">
                Congratulations! Your application has been approved and you have been provisionally accepted into the
                <strong>{{ $academyName }} {{ $programmeTitle }}</strong> programme, starting on
                <strong>{{ $acceptanceStartDate }}</strong>.
            </p>

            <div class="intro-box">
                This letter confirms your admission to our online academic programme, delivered with a British curriculum supported by Islamic values and dedicated student guidance.
            </div>

            <p class="section-title">Student Programme Details</p>
            <div class="section-underline">&nbsp;</div>

            <table class="details-table">
                <tr>
                    <td>
                        <span class="details-label">Student Name</span>
                        <span class="details-value">{{ $studentName }}</span>
                    </td>
                    <td>
                        <span class="details-label">Programme</span>
                        <span class="details-value">{{ $programmeName }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="details-label">Subjects</span>
                        <span class="details-value">{{ $subjectsText }}</span>
                    </td>
                    <td>
                        <span class="details-label">Start Date</span>
                        <span class="details-value">{{ $acceptanceStartDate }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="details-label">Academic Year</span>
                        <span class="details-value">{{ $schoolYearStartDate }}</span>
                    </td>
                    <td>
                        <span class="details-label">Mode of Study</span>
                        <span class="details-value">{{ $studyMode }}</span>
                    </td>
                </tr>
            </table>

            <p class="paragraph" style="margin-top:16px;">
                Next steps: submit any remaining documents, complete the enrolment form, and confirm your payment plan. After final approval, you will receive login details for the NextGen student platform.
            </p>

            <p class="paragraph">
                For support, contact admissions at <strong>{{ $academyWebsite }}</strong> or WhatsApp at
                <strong>{{ $academyWhatsapp }}</strong>. Please confirm your acceptance within seven days to secure your place.
            </p>

            <table class="sign-table">
                <tr>
                    <td class="sign-text">
                        <p><strong>Yours sincerely,</strong></p>
                        <p class="sign-name">{{ $academyName }}</p>
                        <p>Admissions Office<br>{{ $academyCompany }}</p>
                    </td>
                    <td class="seal-cell">
                        <div class="seal">
                            <div class="seal-inner">
                                <div class="seal-text-1">Approved</div>
                                <div class="seal-text-2">{{ $academyInitiative ?? 'NextGen' }}</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="footer">
                <strong>{{ $academyName }}</strong> &nbsp;|&nbsp; {{ $academyInitiative }} &nbsp;|&nbsp; UKPRN: 10099428
            </div>
        </div>
    </div>
</body>
</html>