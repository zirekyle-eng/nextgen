<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Agreement</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; font-size: 10.5px; line-height: 1.4; margin: 0; background: #ffffff; }
        .page { border: 1px solid #d6deea; padding: 16px; background: #ffffff; }
        .head { border-bottom: 2px solid #0f3f70; padding-bottom: 8px; margin-bottom: 10px; }
        .hero-wrap { text-align: center; margin-bottom: 7px; }
        .hero-image { max-width: 100%; max-height: 78px; }
        .logo-wrap { text-align: center; margin-bottom: 7px; }
        .logo-image { max-width: 140px; max-height: 50px; }
        .school { font-size: 18px; color: #0f3f70; font-weight: 700; margin: 0; }
        .muted { color: #4b5563; font-size: 9.5px; margin: 1px 0 0; }
        .title { text-align: center; font-size: 15px; color: #0f3f70; font-weight: 700; text-transform: uppercase; margin: 6px 0 10px; }
        .section-title { font-size: 11px; color: #0f3f70; font-weight: 700; margin: 9px 0 4px; background: #f3f7fd; border-left: 3px solid #0f3f70; padding: 3px 6px; }
        .paragraph { margin: 0 0 6px; text-align: left; }
        .party-box { border: 1px solid #d8e0ea; background: #f8fbff; padding: 7px 8px; margin-bottom: 6px; }
        .table { width: 100%; border-collapse: collapse; margin: 6px 0 10px; table-layout: fixed; page-break-inside: avoid; }
        .table th, .table td { border: 1px solid #d8e0ea; padding: 6px 7px; text-align: left; vertical-align: top; word-wrap: break-word; }
        .table th { background: #0f3f70; color: #ffffff; font-weight: 700; }
        .table .total-row td { background: #f3f7fd; font-weight: 700; color: #0f3f70; }
        .col-no { width: 8%; text-align: center; }
        .list { margin: 4px 0 6px 14px; padding: 0; }
        .list li { margin-bottom: 2px; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 14px; page-break-inside: avoid; }
        .sign-table td { width: 50%; vertical-align: top; padding-top: 14px; }
        .line { margin-top: 14px; border-top: 1px solid #1f2937; width: 90%; }
        .small-gap { height: 6px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="head">
            @if(!empty($schoolHeaderImage))
                <div class="hero-wrap">
                    <img class="hero-image" src="{{ $schoolHeaderImage }}" alt="School Header">
                </div>
            @endif
            @if(!empty($schoolLogo))
                <div class="logo-wrap">
                    <img class="logo-image" src="{{ $schoolLogo }}" alt="School Logo">
                </div>
            @endif
            <p class="school">{{ $schoolName }}</p>
            <p class="muted">{{ $schoolAddress ?: '[School Address]' }}</p>
            <p class="muted">{{ $schoolContactInfo ?: '[School Contact Information]' }}</p>
        </div>

        <p class="title">Financial Agreement</p>
        <p class="paragraph"><strong>Date:</strong> {{ $generatedDate }}</p>

        <div class="party-box">
            <p class="paragraph"><strong>{{ $schoolName }}</strong></p>
            <p class="paragraph">{{ $schoolAddress ?: '[School Address]' }}</p>
            <p class="paragraph">{{ $schoolContactInfo ?: '[School Contact Information]' }}</p>
        </div>

        <p class="paragraph"><strong>AND</strong></p>

        <div class="party-box">
            <p class="paragraph"><strong>{{ $parentName }} </strong>(hereinafter referred to as "the Parent(s)")</p>
            <p class="paragraph">{{ $parentAddress ?: '[Parent/Guardian Address]' }}</p>
            <p class="paragraph">{{ $parentContactInfo ?: '[Parent/Guardian Contact Information]' }}</p>
        </div>

        <p class="section-title">WHEREAS:</p>
        <p class="paragraph">A. The Parent(s) desire to enroll their child, <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>, in the School for the academic year <strong>{{ $currentSession }}</strong>.</p>
        <p class="paragraph">B. The School has offered a place to the Student, and the Parent(s) wish to accept this offer subject to the terms and conditions herein.</p>

        <p class="paragraph"><strong>NOW, THEREFORE</strong>, in consideration of the mutual covenants and promises herein contained, the parties agree as follows:</p>

        <p class="section-title">1. Tuition Fees</p>
        <p class="paragraph">The annual tuition fee for the academic year <strong>{{ $currentSession }}</strong> is <strong>{{ $annualTuitionFeeDisplay }}</strong>. This fee includes core digital learning resources, learning content access, and access to the school's online platform.</p>

        <p class="section-title">2. Payment Schedule</p>
        <p class="paragraph">Tuition fees are payable in <strong>4 installments</strong> as follows:</p>
        <table class="table">
            <tr>
                <th class="col-no">#</th>
                <th>Installment</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Notes</th>
            </tr>
            @if(!empty($installments))
                @php($i = 1)
                @foreach($installments as $item)
                    <tr>
                        <td class="col-no">{{ $i++ }}</td>
                        <td>{{ $item['label'] }}</td>
                        <td>{{ $item['amount_display'] }}</td>
                        <td>{{ $item['due_date'] }}</td>
                        <td>{{ $item['note'] ?: '-' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="col-no">-</td>
                    <td>Total Annual Tuition Fee</td>
                    <td>{{ $annualTuitionFeeDisplay }}</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            @else
                <tr>
                    <td class="col-no">1</td>
                    <td>First Installment</td>
                    <td>[Amount] GBP</td>
                    <td>[Date]</td>
                    <td>(upon acceptance of offer)</td>
                </tr>
                <tr>
                    <td class="col-no">2</td>
                    <td>Second Installment</td>
                    <td>[Amount] GBP</td>
                    <td>[Date]</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td class="col-no">3</td>
                    <td>Third Installment</td>
                    <td>[Amount] GBP</td>
                    <td>[Date]</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td class="col-no">4</td>
                    <td>Fourth Installment</td>
                    <td>[Amount] GBP</td>
                    <td>[Date]</td>
                    <td>-</td>
                </tr>
            @endif
        </table>

        <p class="section-title">3. Additional Charges</p>
        <p class="paragraph">Additional charges may apply for extracurricular activities, external examination fees, and special educational support. These charges will be communicated to the Parent(s) in advance and will be payable separately.</p>

        <p class="section-title">4. Payment Methods</p>
        <p class="paragraph">Payments can be made via bank transfer or credit/debit card. Bank details for transfers are as follows:</p>
        <p class="paragraph">Bank Name: [Bank Name]</p>
        <p class="paragraph">Account Name: [Account Name]</p>
        <p class="paragraph">Account Number: [Account Number]</p>
        <p class="paragraph">Sort Code: [Sort Code]</p>
        <p class="paragraph">SWIFT/BIC Code: [SWIFT/BIC Code]</p>

        <p class="section-title">7. Parent(s) Responsibilities</p>
        <p class="paragraph">- Ensure timely payment of all fees and charges.</p>
        <p class="paragraph">- Provide accurate and up-to-date contact and financial information.</p>
        <p class="paragraph">- Adhere to all School policies and procedures.</p>

        <p class="section-title">9. Entire Agreement</p>
        <p class="paragraph">This Agreement constitutes the entire agreement between the parties and supersedes all prior discussions, negotiations, and agreements, whether oral or written.</p>

        <p class="paragraph"><strong>IN WITNESS WHEREOF</strong>, the parties have executed this Agreement as of the date first written above.</p>

        <table class="sign-table">
            <tr>
                <td>
                    For {{ $schoolName }}:
                    <div class="line"></div>
                    <p class="paragraph">[Authorized Signatory Name]</p>
                    <p class="paragraph">[Title]</p>
                </td>
                <td>
                    For the Parent(s):
                    <div class="line"></div>
                    <p class="paragraph">{{ $parentName }}</p>
                    <div class="line"></div>
                    <p class="paragraph">[Parent/Guardian Name (if applicable)]</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
