<?php

namespace App\Console\Commands;

use App\Mail\StudentAttendanceReportMail;
use App\Models\BbbMeetingAttendance;
use App\Models\BbbTermReport;
use App\Models\Setting;
use App\Models\StudentRecord;
use App\Services\BbbAttendanceService;
use App\Services\BbbWhatsappMessageBuilder;
use App\Services\WhatsappGateway;
use App\Services\WhatsappSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBbbTermAttendanceReports extends Command
{
    protected $signature = 'bbb:send-term-attendance-reports';

    protected $description = 'Send term attendance reports to parents at end of each term';

    public function handle(
        BbbAttendanceService $attendanceService,
        WhatsappGateway $whatsappGateway,
        BbbWhatsappMessageBuilder $messageBuilder,
        WhatsappSettings $whatsappSettings
    )
    {
        if (!config('bigbluebutton.attendance_report_send_term_end', true)) {
            $this->line('Term attendance reports are disabled.');
            return self::SUCCESS;
        }

        $termKey = $this->resolveTermEndingToday();
        if (!$termKey) {
            return self::SUCCESS;
        }

        [$from, $to] = $this->termDateRange($termKey);
        if (!$from || !$to) {
            return self::SUCCESS;
        }

        $studentIds = StudentRecord::pluck('user_id')->filter()->unique()->values();
        foreach ($studentIds as $studentId) {
            $already = BbbTermReport::where('student_user_id', $studentId)
                ->where('term_key', $termKey)
                ->first();

            if ($already && $already->sent_at) {
                continue;
            }

            $student = User::find($studentId);
            if (!$student || $student->user_type !== 'student') {
                continue;
            }

            $parent = $attendanceService->resolveParent($student);
            if (!$parent) {
                continue;
            }

            $report = $this->buildReport($studentId, $from, $to);
            if (empty($report['rows'])) {
                continue;
            }

            try {
                $emailSent = false;
                $whatsappSent = false;

                if (!empty($parent->email)) {
                    Mail::to($parent->email)->send(new StudentAttendanceReportMail(
                        $student,
                        $parent,
                        $termKey,
                        $from,
                        $to,
                        $report['summary'],
                        $report['rows']
                    ));
                    $emailSent = true;
                }

                if ($whatsappSettings->enabled() && $whatsappSettings->notifyTermReport()) {
                    $parentPhone = $parent->phone ?: $parent->phone2;
                    if ($parentPhone) {
                        $whatsappSent = $whatsappGateway->sendMessage(
                            $parentPhone,
                            $messageBuilder->termReport($student, $termKey, $from, $to),
                            ['type' => 'term_report', 'student_id' => $student->id, 'term' => $termKey]
                        );
                    }
                }

                if ($emailSent || $whatsappSent) {
                    if (!$already) {
                        $already = new BbbTermReport([
                            'student_user_id' => $studentId,
                            'term_key' => $termKey,
                        ]);
                    }
                    $already->sent_at = now();
                    $already->save();
                }
            } catch (\Throwable $e) {
                Log::error('Term report email failed', [
                    'student_user_id' => $studentId,
                    'term' => $termKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Term attendance reports sent for {$termKey}.");
        return self::SUCCESS;
    }

    private function resolveTermEndingToday(): ?string
    {
        $today = now()->toDateString();
        $settings = Setting::whereIn('type', [
            'term1_end', 'term2_end', 'term3_end',
        ])->pluck('description', 'type');

        foreach ([1, 2, 3] as $term) {
            $key = "term{$term}_end";
            $date = trim((string) ($settings[$key] ?? ''));
            if ($date !== '' && $today === Carbon::parse($date)->toDateString()) {
                return "term{$term}";
            }
        }

        return null;
    }

    private function termDateRange(string $termKey): array
    {
        $settings = Setting::whereIn('type', [
            "{$termKey}_start",
            "{$termKey}_end",
        ])->pluck('description', 'type');

        $start = trim((string) ($settings["{$termKey}_start"] ?? ''));
        $end = trim((string) ($settings["{$termKey}_end"] ?? ''));

        if ($start === '' || $end === '') {
            return [null, null];
        }

        return [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()];
    }

    private function buildReport(int $studentId, Carbon $from, Carbon $to): array
    {
        $rows = BbbMeetingAttendance::where('student_user_id', $studentId)
            ->whereNotNull('scheduled_start_at')
            ->whereBetween('scheduled_start_at', [$from, $to])
            ->orderBy('scheduled_start_at')
            ->get();

        $summary = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
        ];

        $mapped = [];
        foreach ($rows as $row) {
            $status = $row->status ?: 'absent';
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
            $mapped[] = [
                'date' => optional($row->scheduled_start_at)->format('Y-m-d'),
                'meeting_id' => $row->meeting_id,
                'status' => $status,
                'late_minutes' => (int) $row->late_minutes,
            ];
        }

        return ['summary' => $summary, 'rows' => $mapped];
    }
}
