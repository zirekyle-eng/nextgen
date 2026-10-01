<?php

namespace App\Mail;

use App\Models\Student;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Subject;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class StudentApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $guardian;
    public $parentUser;
    public $studentUser;
    public $myClass;
    public $section;

    /**
     * Create a new message instance.
     */
    public function __construct(Student $student, User $parentUser, User $studentUser, MyClass $myClass, Section $section)
    {
        $this->student = $student;
        $this->guardian = $student->guardian;
        $this->parentUser = $parentUser;
        $this->studentUser = $studentUser;
        $this->myClass = $myClass;
        $this->section = $section;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        \Log::debug('Building approval email', [
            'guardian_email' => $this->parentUser->email ?? 'NULL',
            'student_name' => $this->student->full_name,
            'class_name' => $this->myClass->name ?? 'UNKNOWN',
            'section_name' => $this->section->name ?? 'UNKNOWN'
        ]);

        $mail = $this->subject('Letter of Acceptance - NextGen Online Academy of Britain')
            ->view('emails.student-approval')
            ->with($this->acceptanceViewData())
            ->withSwiftMessage(function ($message) {
            $message->getHeaders()->addTextHeader(
                'X-Priority',
                '3'
            );
            $message->getHeaders()->addTextHeader(
                'X-Mailer',
                'NextGen School Management System'
            );
        });

        $attachment = $this->buildAcceptancePdfAttachment();
        if ($attachment) {
            $mail->attachData($attachment['binary'], $attachment['name'], [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    private function buildAcceptancePdfAttachment()
    {
        $safeStudentName = $this->safeFileName($this->student->full_name ?: ($this->student->first_name . '_' . $this->student->last_name));
        $data = $this->acceptancePdfData();

        $acceptancePdf = $this->renderPdf('pdfs.student-acceptance-letter', $data);
        if (!$acceptancePdf) {
            $acceptancePdf = $this->buildStyledPdf(
                'Letter of Acceptance',
                (string) $data['academyName'],
                $this->acceptanceFallbackLines($data),
                []
            );
        }

        if (!$acceptancePdf) {
            \Log::warning('Acceptance PDF attachment could not be generated', [
                'student_id' => $this->student->id ?? null,
            ]);

            return null;
        }

        return [
            'name' => 'Letter_of_Acceptance_' . $safeStudentName . '.pdf',
            'binary' => $acceptancePdf,
        ];
    }

    private function acceptancePdfData()
    {
        $settings = Setting::whereIn('type', ['system_name', 'address', 'phone', 'system_email', 'current_session', 'term_begins', 'logo', 'pdf_header_image'])
            ->pluck('description', 'type');

        $schoolEmail = (string) $settings->get('system_email', '');
        $schoolPhone = (string) $settings->get('phone', '');
        $schoolWebsite = (string) config('app.url', '');
        $defaultLogo = public_path('global_assets/images/logo.png');
        $schoolLogo = trim((string) $settings->get('logo', ''));

        $logoPath = $schoolLogo;
        if ($logoPath === '') {
            $logoPath = $defaultLogo;
        }

        if ($logoPath !== '' && !preg_match('#^https?://#i', $logoPath)) {
            if (!file_exists($logoPath)) {
                $candidate = public_path(ltrim($logoPath, '/\\'));
                if (file_exists($candidate)) {
                    $logoPath = $candidate;
                }
            }

            if (file_exists($logoPath)) {
                $logoPath = 'file://' . str_replace('\\', '/', realpath($logoPath));
            }
        }

        return [
            'student' => $this->student,
            'guardian' => $this->guardian,
            'parentUser' => $this->parentUser,
            'studentUser' => $this->studentUser,
            'myClass' => $this->myClass,
            'section' => $this->section,
            'schoolName' => $settings->get('system_name', 'NextGen Digital School - Britain'),
            'schoolAddress' => $settings->get('address', ''),
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
            'currentSession' => $settings->get('current_session', date('Y') . '-' . (date('Y') + 1)),
            'termBegins' => $settings->get('term_begins', ''),
            'schoolWebsite' => $schoolWebsite,
            'schoolLogo' => $logoPath,
            'academyLogo' => $logoPath,
            'schoolHeaderImage' => '',
            'admissionsContact' => implode(' / ', array_filter([$schoolEmail, $schoolPhone])),
            'schoolContactInfo' => implode(' | ', array_filter([$settings->get('address', ''), $schoolPhone, $schoolEmail])),
            'generatedDate' => now()->format('d M Y'),
        ] + $this->acceptanceViewData();
    }

    private function buildPdfAttachments()
    {
        $safeStudentName = $this->safeFileName($this->student->full_name ?: ($this->student->first_name . '_' . $this->student->last_name));
        $settings = Setting::whereIn('type', ['system_name', 'address', 'phone', 'system_email', 'current_session', 'term_begins', 'logo', 'pdf_header_image'])
            ->pluck('description', 'type');

        $schoolEmail = (string) $settings->get('system_email', '');
        $schoolPhone = (string) $settings->get('phone', '');
        $schoolWebsite = (string) config('app.url', '');
        $schoolLogo = (string) $settings->get('logo', '');
        $schoolHeaderImage = (string) $settings->get('pdf_header_image', '/pulic/global_assets/images/logo.png');
        $contactBits = array_filter([$schoolEmail, $schoolPhone]);

        $sharedData = [
            'student' => $this->student,
            'guardian' => $this->guardian,
            'parentUser' => $this->parentUser,
            'studentUser' => $this->studentUser,
            'myClass' => $this->myClass,
            'section' => $this->section,
            'schoolName' => $settings->get('system_name', 'NextGen Digital School - Britain'),
            'schoolAddress' => $settings->get('address', ''),
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
            'currentSession' => $settings->get('current_session', date('Y') . '-' . (date('Y') + 1)),
            'termBegins' => $settings->get('term_begins', ''),
            'schoolWebsite' => $schoolWebsite,
            'schoolLogo' => $schoolLogo,
            'schoolHeaderImage' => $schoolHeaderImage,
            'admissionsContact' => implode(' / ', $contactBits),
            'schoolContactInfo' => implode(' | ', array_filter([$settings->get('address', ''), $schoolPhone, $schoolEmail])),
            'generatedDate' => now()->format('d M Y'),
        ] + $this->acceptanceViewData();

        $attachments = [];

        $acceptancePdf = $this->buildStyledPdf(
            'Acceptance Letter',
            (string) $sharedData['schoolName'],
            $this->acceptanceFallbackLines($sharedData),
            [
                'NextGen Digital School - Britain is committed to high-quality online British education with Islamic values.',
                'Contact Admissions at ' . ($sharedData['admissionsContact'] ?: 'N/A'),
            ]
        );
        if (!$acceptancePdf) {
            $acceptancePdf = $this->renderPdf('pdfs.student-acceptance-letter', $sharedData);
        }
        if ($acceptancePdf) {
            $attachments[] = [
                'name' => 'Acceptance_Letter_' . $safeStudentName . '.pdf',
                'binary' => $acceptancePdf,
            ];
        } else {
            \Log::warning('Acceptance PDF attachment could not be generated', [
                'student_id' => $this->student->id ?? null,
            ]);
        }

        $financialData = $sharedData;
        $financialData['nextTermFee'] = $this->getNextTermFee();
        $financialData['annualTuitionFeeRaw'] = $financialData['nextTermFee'];
        $financialData['annualTuitionFee'] = $this->parseCurrencyAmount($financialData['annualTuitionFeeRaw']);
        $financialData['annualTuitionFeeDisplay'] = $financialData['annualTuitionFee'] !== null
            ? $this->formatGbp($financialData['annualTuitionFee'])
            : ((string) $financialData['annualTuitionFeeRaw'] ?: 'N/A');
        $financialData['installments'] = $this->buildQuarterlyInstallments($financialData['annualTuitionFee']);
        $financialData['parentName'] = trim(($this->guardian->first_name ?? '') . ' ' . ($this->guardian->last_name ?? '')) ?: 'Parent/Guardian Name';
        $financialData['parentAddress'] = $this->getGuardianAddress();
        $financialData['parentContactInfo'] = $this->getGuardianContactInfo();
        $financialPdf = $this->buildStyledPdf(
            'Financial Agreement',
            (string) $financialData['schoolName'],
            $this->financialFallbackLines($financialData),
            [
                'This agreement is issued electronically.',
                'Payments must be completed through official channels.',
            ]
        );
        if (!$financialPdf) {
            $financialPdf = $this->renderPdf('pdfs.student-financial-agreement', $financialData);
        }
        if ($financialPdf) {
            $attachments[] = [
                'name' => 'Financial_Agreement_' . $safeStudentName . '.pdf',
                'binary' => $financialPdf,
            ];
        } else {
            \Log::warning('Financial PDF attachment could not be generated', [
                'student_id' => $this->student->id ?? null,
            ]);
        }

        return $attachments;
    }

    private function getNextTermFee()
    {
        $classTypeCode = optional($this->myClass->class_type)->code;
        if (!$classTypeCode) {
            $this->myClass->loadMissing('class_type');
            $classTypeCode = optional($this->myClass->class_type)->code;
        }

        if (!$classTypeCode) {
            return 'N/A';
        }

        $settingKey = 'next_term_fees_' . strtolower($classTypeCode);
        return Setting::where('type', $settingKey)->value('description') ?: 'N/A';
    }

    private function renderPdf($view, array $data)
    {
        $previousErrorReporting = error_reporting();
        $pdfWarningHandler = function ($severity, $message) {
            // Dompdf on older versions can trigger noisy deprecations on modern PHP.
            // Swallow these warnings so PDF generation can continue and avoid leaking handlers.
            if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
                return true;
            }

            if (is_string($message) && strpos($message, 'file_get_contents(): Passing null to parameter #2') !== false) {
                return true;
            }

            return false;
        };
        set_error_handler($pdfWarningHandler);

        try {
            // Dompdf 0.8.x is not fully compatible with newer PHP deprecation notices.
            // Suppress deprecations only during PDF rendering to avoid fatal interruption.
            error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
            $pdf = \PDF::setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ])->loadView($view, $data);

            return $pdf->output();
        } catch (\Throwable $e) {
            // Dompdf may throw before restoring its internal error handler.
            $this->restoreDompdfErrorHandlers();

            $errorMessage = (string) $e->getMessage();
            $isKnownDompdfCompatibilityIssue = stripos($errorMessage, 'file_get_contents(): Passing null to parameter #2') !== false;

            if ($isKnownDompdfCompatibilityIssue) {
                \Log::warning('Dompdf compatibility warning while generating student approval attachment PDF. Fallback PDF will be used.', [
                    'view' => $view,
                    'student_id' => $this->student->id ?? null,
                    'error' => $errorMessage,
                ]);
            } else {
                \Log::error('Failed to generate student approval attachment PDF', [
                    'view' => $view,
                    'student_id' => $this->student->id ?? null,
                    'error' => $errorMessage,
                ]);
            }

            return null;
        } finally {
            // Restore our temporary handler if still active.
            @restore_error_handler();
            error_reporting($previousErrorReporting);
        }
    }

    private function restoreDompdfErrorHandlers()
    {
        // Attempt to unwind any Dompdf-installed handlers without tearing down
        // the application's normal handler chain.
        for ($i = 0; $i < 6; $i++) {
            $probe = set_error_handler(function () {
                return false;
            });

            if ($probe === null) {
                restore_error_handler();
                break;
            }

            restore_error_handler();

            $handlerName = is_array($probe)
                ? ((is_object($probe[0]) ? get_class($probe[0]) : (string) $probe[0]) . '::' . ($probe[1] ?? ''))
                : (is_string($probe) ? $probe : '');

            if (stripos($handlerName, 'Dompdf\\Helpers::record_warnings') !== false) {
                @restore_error_handler();
                continue;
            }

            break;
        }
    }

    private function safeFileName($name)
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $name);
        return trim($safe, '_') ?: 'student';
    }

    private function acceptanceFallbackLines(array $data)
    {
        $studentName = trim($data['student']->first_name . ' ' . $data['student']->last_name);
        $programme = $data['programmeName'] ?? ($data['myClass']->name ?? 'Year 1 (Key Stage 1)');
        $subjects = $data['subjectsText'] ?? 'English, Mathematics, Science, Geography, History, PE, Art, Music';
        $acceptanceStartDate = $data['acceptanceStartDate'] ?? '20 July 2026';
        $schoolYearStartDate = $data['schoolYearStartDate'] ?? 'September 2026';

        return [
            'NextGen Online Academy of Britain',
            'An Initiative of The Academy of eLearning-Britain Limited',
            '12 Universal Square, Manchester, M12 6JH, United Kingdom',
            'www.nextgene.org.uk | WhatsApp: +44 7593 072201',
            'UKPRN: 10099428 | ICO Reg: ZB985950 | Academy Licence No: 6581657 | OTHM: DC2510950',
            '',
            'Date: ' . $data['generatedDate'],
            '',
            'LETTER OF ACCEPTANCE',
            '',
            'Dear ' . ($studentName ?: '[STUDENT NAME]') . ',',
            '',
            'We are pleased to inform you that you have been formally accepted into the NextGen Online Academy of Britain A Level Programme, commencing ' . $acceptanceStartDate . '. This offer of acceptance is subject to the completion of all required enrolment formalities.',
            '',
            'Programme Details:',
            'Student Name: ' . ($studentName ?: '[STUDENT NAME]'),
            'Programme: ' . $programme,
            'Subjects: ' . $subjects,
            'Acceptance Start Date: ' . $acceptanceStartDate,
            'School Year Start Date: ' . $schoolYearStartDate,
            'Mode of Study: Online / NextGen Academy Platform',
            '',
            'Enrolment Confirmation:',
            'This acceptance letter also serves as confirmation of provisional enrolment at NextGen Online Academy of Britain, effective ' . $acceptanceStartDate . ', and valid through to the commencement of the school year in ' . $schoolYearStartDate . '. The student is expected to complete all onboarding requirements prior to the official school start date.',
            '',
            'NextGen Online Academy of Britain operates under The Academy of eLearning-Britain Limited, a fully accredited and registered institution in the United Kingdom (UKPRN: 10099428, Academy Licence No: 6581657, ICO Registration: ZB985950, OTHM: DC2510950), recognised by Ofqual and GOV.UK.',
            '',
            'We warmly welcome the student to the NextGen Academy community and look forward to supporting her academic journey. Should you require any further information, please do not hesitate to contact us at www.nextgene.org.uk or via WhatsApp at +44 7593 072201.',
            '',
            'Yours sincerely,',
            'NextGen Online Academy of Britain',
            'Admissions Office',
            'The Academy of eLearning-Britain Limited',
        ];
    }

    private function acceptanceViewData()
    {
        return [
            'academyName' => 'NextGen Online Academy of Britain',
            'academyInitiative' => 'An Initiative of The Academy of eLearning-Britain Limited',
            'academyCompany' => 'The Academy of eLearning-Britain Limited',
            'academyAddress' => '12 Universal Square, Manchester, M12 6JH, United Kingdom',
            'academyWebsite' => 'www.nextgene.org.uk',
            'academyWhatsapp' => '+44 7593 072201',
            'academyRegistrations' => 'UKPRN: 10099428 | ICO Reg: ZB985950 | Academy Licence No: 6581657 | OTHM: DC2510950',
            'programmeTitle' => 'A Level Programme',
            'programmeName' => $this->myClass->name ?: 'Year 1 (Key Stage 1)',
            'subjectsText' => $this->subjectsText(),
            'acceptanceStartDate' => '20 July 2026',
            'schoolYearStartDate' => 'September 2026',
            'studyMode' => 'Online / NextGen Academy Platform',
            'generatedDate' => now()->format('d M Y'),
        ];
    }

    private function subjectsText()
    {
        try {
            $subjects = Subject::query()
                ->where('my_class_id', $this->myClass->id)
                ->excludeActivities()
                ->orderBy('name')
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            if (!empty($subjects)) {
                return implode(', ', $subjects);
            }
        } catch (\Throwable $e) {
            \Log::warning('Could not load subjects for student acceptance letter', [
                'class_id' => $this->myClass->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return 'English, Mathematics, Science, Geography, History, PE, Art, Music';
    }

    private function financialFallbackLines(array $data)
    {
        $studentName = trim($data['student']->first_name . ' ' . $data['student']->last_name);
        $installmentCount = !empty($data['installments']) ? count($data['installments']) : 4;
        $installmentLines = [];
        if (!empty($data['installments'])) {
            $installmentLines[] = 'Tuition fees are payable in ' . $installmentCount . ' installments as follows:';
            foreach ($data['installments'] as $item) {
                $installmentLines[] = $item['label'] . ': ' . $item['amount_display'] . ' due by ' . $item['due_date'] . ($item['note'] ? ' ' . $item['note'] : '');
            }
        } else {
            $installmentLines[] = 'Tuition fees are payable in [number] installments as follows:';
            $installmentLines[] = 'First Installment: [Amount] GBP due by [Date] (upon acceptance of offer)';
            $installmentLines[] = 'Second Installment: [Amount] GBP due by [Date]';
            $installmentLines[] = 'Third Installment: [Amount] GBP due by [Date]';
            $installmentLines[] = '[Add more installments if necessary]';
        }

        $lines = [
            'Financial Agreement',
            $data['schoolName'],
            ($data['schoolAddress'] ?: '[School Address]'),
            ($data['schoolContactInfo'] ?: '[School Contact Information]'),
            '',
            'AND',
            '',
            $data['parentName'] . ' (hereinafter referred to as "the Parent(s)")',
            ($data['parentAddress'] ?: '[Parent/Guardian Address]'),
            ($data['parentContactInfo'] ?: '[Parent/Guardian Contact Information]'),
            '',
            'WHEREAS:',
            '',
            'A. The Parent(s) desire to enroll their child, ' . $studentName . ', in the School for the academic year ' . $data['currentSession'] . '.',
            'B. The School has offered a place to the Student, and the Parent(s) wish to accept this offer subject to the terms and conditions herein.',
            '',
            'NOW, THEREFORE, in consideration of the mutual covenants and promises herein contained, the parties agree as follows:',
            '',
            '1. Tuition Fees',
            'The annual tuition fee for the academic year ' . $data['currentSession'] . ' is ' . $data['annualTuitionFeeDisplay'] . '. This fee includes core digital learning resources, textbooks, and access to the online learning platform.',
            '',
            '2. Payment Schedule',
        ];

        $lines = array_merge($lines, $installmentLines, [
            '',
            '3. Additional Charges',
            'Additional charges may apply for extracurricular activities, external examination fees, and special educational needs support. These charges will be communicated to the Parent(s) in advance and will be payable separately.',
            '',
            '4. Payment Methods',
            'Payments can be made via bank transfer or credit/debit card. Bank details for transfers are as follows:',
            '[Bank Name]',
            '[Account Name]',
            '[Account Number]',
            '[Sort Code]',
            '[SWIFT/BIC Code]',
            '',
            '7. Parent(s) Responsibilities',
            'The Parent(s) agree to:',
            '- Ensure timely payment of all fees and charges.',
            '- Provide accurate and up-to-date contact and financial information.',
            '- Adhere to all School policies and procedures.',
            '',
            '9. Entire Agreement',
            'This Agreement constitutes the entire agreement between the parties and supersedes all prior discussions, negotiations, and agreements, whether oral or written.',
            '',
            'IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written above.',
            '',
            'For NextGen Digital School - Britain:',
            '[Authorized Signatory Name]',
            '[Title]',
            '',
            'For the Parent(s):',
            $data['parentName'],
            '[Parent/Guardian Name (if applicable)]',
        ]);

        return $lines;
    }

    private function parseCurrencyAmount($value)
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d,.\-]/', '', $raw);
        if ($normalized === '') {
            return null;
        }

        if (strpos($normalized, ',') !== false && strpos($normalized, '.') !== false) {
            $normalized = str_replace(',', '', $normalized);
        } elseif (strpos($normalized, ',') !== false) {
            $normalized = str_replace(',', '.', $normalized);
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    private function formatGbp($amount)
    {
        return number_format((float) $amount, 2, '.', ',') . ' GBP';
    }

    private function buildQuarterlyInstallments($annualFee)
    {
        if ($annualFee === null || $annualFee <= 0) {
            return [];
        }

        $firstThree = round($annualFee / 4, 2);
        $fourth = round($annualFee - ($firstThree * 3), 2);
        $baseDate = now()->startOfDay();

        $rows = [
            ['label' => 'First Installment', 'amount' => $firstThree, 'due_date' => $baseDate->format('d M Y'), 'note' => '(upon acceptance of offer)'],
            ['label' => 'Second Installment', 'amount' => $firstThree, 'due_date' => $baseDate->copy()->addMonthsNoOverflow(1)->format('d M Y'), 'note' => ''],
            ['label' => 'Third Installment', 'amount' => $firstThree, 'due_date' => $baseDate->copy()->addMonthsNoOverflow(2)->format('d M Y'), 'note' => ''],
            ['label' => 'Fourth Installment', 'amount' => $fourth, 'due_date' => $baseDate->copy()->addMonthsNoOverflow(3)->format('d M Y'), 'note' => ''],
        ];

        foreach ($rows as &$row) {
            $row['amount_display'] = $this->formatGbp($row['amount']);
        }

        return $rows;
    }

    private function getGuardianAddress()
    {
        if (!$this->guardian) {
            return 'Parent/Guardian Address';
        }

        $parts = array_filter([
            $this->guardian->address_line1 ?? null,
            $this->guardian->address_line2 ?? null,
            $this->guardian->city ?? null,
            $this->guardian->postal_code ?? null,
            $this->guardian->country ?? null,
        ]);

        return $parts ? implode(', ', $parts) : 'Parent/Guardian Address';
    }

    private function getGuardianContactInfo()
    {
        $phone = trim((string) (($this->guardian->phone_prefix ?? '') . ($this->guardian->phone ?? '')));
        $email = trim((string) ($this->guardian->email ?? ''));
        $fallbackEmail = trim((string) ($this->parentUser->email ?? ''));

        $contact = array_filter([$phone, $email ?: $fallbackEmail]);

        return $contact ? implode(' | ', $contact) : 'Parent/Guardian Contact Information';
    }

    private function buildStyledPdf($title, $schoolName, array $lines, array $notes = [])
    {
        try {
            $isFinancialAgreement = stripos((string) $title, 'financial') !== false;
            $installmentData = $this->extractInstallmentRows($lines);
            $bodyLines = $installmentData['lines'];
            $installmentRows = $isFinancialAgreement ? $installmentData['rows'] : [];

            $streamLines = [];
            $streamLines[] = '0.90 0.92 0.95 rg';
            $streamLines[] = '0 0 612 792 re f';

            $streamLines[] = '0.82 0.86 0.91 RG';
            $streamLines[] = '1 w';
            $streamLines[] = '18 18 576 756 re S';

            $streamLines[] = '0.05 0.19 0.35 rg';
            $streamLines[] = '18 716 576 58 re f';
            $streamLines[] = '0.79 0.88 1 rg';
            $streamLines[] = '18 716 6 58 re f';
            $streamLines[] = 'BT /F2 16 Tf 1 1 1 rg 34 750 Td (' . $this->escapePdfText($title) . ') Tj ET';
            $streamLines[] = 'BT /F1 10 Tf 0.9 0.95 1 rg 34 734 Td (' . $this->escapePdfText($schoolName) . ') Tj ET';
            $streamLines[] = 'BT /F1 9 Tf 0.9 0.95 1 rg 452 734 Td (Issued: ' . $this->escapePdfText(now()->format('d M Y')) . ') Tj ET';

            $streamLines[] = '1 1 1 rg';
            $streamLines[] = '30 192 552 508 re f';
            $streamLines[] = '0.82 0.86 0.91 RG';
            $streamLines[] = '1 w';
            $streamLines[] = '30 192 552 508 re S';
            $streamLines[] = 'BT /F2 11 Tf 0.1 0.26 0.45 rg 42 686 Td (Official Communication) Tj ET';

            $y = 664;
            foreach ($bodyLines as $line) {
                $wrapped = $this->wrapPdfText((string) $line, 82);
                foreach ($wrapped as $segment) {
                    if ($y < ($isFinancialAgreement ? 254 : 212)) {
                        break 2;
                    }
                    $font = $isFinancialAgreement ? '9.8' : '10.5';
                    $step = $isFinancialAgreement ? 12 : 14;
                    $streamLines[] = 'BT /F1 ' . $font . ' Tf 0.18 0.2 0.23 rg 42 ' . $y . ' Td (' . $this->escapePdfText($segment) . ') Tj ET';
                    $y -= $step;
                }
            }

            if (!empty($installmentRows)) {
                $streamLines[] = '0.95 0.97 1 rg';
                $streamLines[] = '36 88 540 136 re f';
                $streamLines[] = '0.82 0.86 0.91 RG';
                $streamLines[] = '36 88 540 136 re S';

                $streamLines[] = 'BT /F2 11 Tf 0.1 0.26 0.45 rg 44 208 Td (Payment Schedule - 4 Installments) Tj ET';

                $streamLines[] = '0.10 0.26 0.45 rg';
                $streamLines[] = '42 184 528 20 re f';
                $streamLines[] = 'BT /F2 9 Tf 1 1 1 rg 48 190 Td (Installment) Tj ET';
                $streamLines[] = 'BT /F2 9 Tf 1 1 1 rg 242 190 Td (Amount) Tj ET';
                $streamLines[] = 'BT /F2 9 Tf 1 1 1 rg 358 190 Td (Due Date) Tj ET';
                $streamLines[] = 'BT /F2 9 Tf 1 1 1 rg 470 190 Td (Note) Tj ET';

                $rowY = 168;
                foreach ($installmentRows as $row) {
                    if ($rowY < 102) {
                        break;
                    }

                    $streamLines[] = '0.87 0.90 0.95 RG';
                    $streamLines[] = '42 ' . ($rowY - 4) . ' 528 18 re S';
                    $streamLines[] = 'BT /F1 8.8 Tf 0.18 0.2 0.23 rg 48 ' . $rowY . ' Td (' . $this->escapePdfText($row['label']) . ') Tj ET';
                    $streamLines[] = 'BT /F1 8.8 Tf 0.18 0.2 0.23 rg 242 ' . $rowY . ' Td (' . $this->escapePdfText($row['amount']) . ') Tj ET';
                    $streamLines[] = 'BT /F1 8.8 Tf 0.18 0.2 0.23 rg 358 ' . $rowY . ' Td (' . $this->escapePdfText($row['due']) . ') Tj ET';
                    $streamLines[] = 'BT /F1 8.8 Tf 0.18 0.2 0.23 rg 470 ' . $rowY . ' Td (' . $this->escapePdfText($row['note']) . ') Tj ET';
                    $rowY -= 19;
                }
            }

            // 'Important Notes' block removed (no notes displayed in fallback PDF).

            $streamLines[] = 'BT /F1 8.5 Tf 0.35 0.38 0.42 rg 42 36 Td (Generated by NextGen School Management System on ' . $this->escapePdfText(now()->format('Y-m-d H:i')) . ') Tj ET';

            $stream = implode("\n", $streamLines) . "\n";

            $objects = [
                '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
                '2 0 obj << /Type /Pages /Count 1 /Kids [3 0 R] >> endobj',
                '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >> endobj',
                '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
                '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj',
                '6 0 obj << /Length ' . strlen($stream) . ' >> stream' . "\n" . $stream . 'endstream endobj',
            ];

            $pdf = "%PDF-1.4\n";
            $offsets = [0];
            foreach ($objects as $object) {
                $offsets[] = strlen($pdf);
                $pdf .= $object . "\n";
            }

            $xrefPos = strlen($pdf);
            $pdf .= "xref\n0 7\n";
            $pdf .= "0000000000 65535 f \n";
            for ($i = 1; $i <= 6; $i++) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            }

            $pdf .= "trailer << /Size 7 /Root 1 0 R >>\n";
            $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

            return $pdf;
        } catch (\Throwable $e) {
            \Log::error('Failed to build plain fallback PDF', [
                'title' => $title,
                'student_id' => $this->student->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function extractInstallmentRows(array $lines)
    {
        $rows = [];
        $cleaned = [];

        foreach ($lines as $line) {
            $value = trim((string) $line);
            if ($value === '') {
                $cleaned[] = $line;
                continue;
            }

            if (preg_match('/^Payment Schedule\s*\(/i', $value)) {
                continue;
            }

            if (preg_match('/^(First|Second|Third|Fourth)\s+Installment:\s*(.+?)\s+due by\s+(.+)$/i', $value, $matches)) {
                $label = trim($matches[1] . ' Installment');
                $amount = trim($matches[2]);
                $dueWithNote = trim($matches[3]);
                $note = '-';
                $due = $dueWithNote;

                if (preg_match('/^(.+?)\s+\((.+)\)$/', $dueWithNote, $parts)) {
                    $due = trim($parts[1]);
                    $note = '(' . trim($parts[2]) . ')';
                }

                $rows[] = [
                    'label' => $label,
                    'amount' => $amount,
                    'due' => $due,
                    'note' => $note,
                ];
                continue;
            }

            $cleaned[] = $line;
        }

        return ['rows' => $rows, 'lines' => $cleaned];
    }

    private function escapePdfText($text)
    {
        $value = is_scalar($text) ? (string) $text : '';
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = $ascii !== false ? $ascii : $value;
        $value = preg_replace('/[\x00-\x1F\x7F]/', ' ', $value);
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $value);
    }

    private function wrapPdfText($text, $maxChars = 88)
    {
        $plain = trim((string) $text);
        if ($plain === '') {
            return [''];
        }

        $wrapped = wordwrap($plain, (int) $maxChars, "\n", true);
        return explode("\n", $wrapped);
    }
}
