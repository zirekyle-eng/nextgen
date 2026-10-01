<?php

namespace App\Console\Commands;

use App\Mail\StudentLateArrivalAlertMail;
use App\Models\BbbMeetingAttendance;
use App\Services\BbbAttendanceService;
use App\Services\BbbWhatsappMessageBuilder;
use App\Services\WhatsappGateway;
use App\Services\WhatsappSettings;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBbbLateArrivalAlerts extends Command
{
    protected $signature = 'bbb:send-late-arrival-alerts';

    protected $description = 'Send parent alerts for students who joined late (near real-time)';

    public function handle(
        BbbAttendanceService $attendanceService,
        WhatsappGateway $whatsappGateway,
        BbbWhatsappMessageBuilder $messageBuilder,
        WhatsappSettings $whatsappSettings
    )
    {
        if (!config('bigbluebutton.attendance_notify_late_realtime', true)) {
            $this->line('BBB late arrival realtime alerts are disabled.');
            return self::SUCCESS;
        }

        $lateMinutes = max((int) config('bigbluebutton.attendance_late_minutes', 5), 0);
        $sent = 0;

        BbbMeetingAttendance::where('status', 'late')
            ->whereNull('notified_late_realtime_at')
            ->whereNotNull('join_at')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (&$sent, $attendanceService, $lateMinutes, $whatsappGateway, $messageBuilder, $whatsappSettings) {
                foreach ($rows as $attendance) {
                    $student = User::find($attendance->student_user_id);
                    if (!$student || $student->user_type !== 'student') {
                        continue;
                    }

                    $parent = $attendanceService->resolveParent($student);
                    if (!$parent) {
                        Log::warning('Late arrival alert skipped: missing parent', [
                            'student_user_id' => $student->id,
                            'meeting_id' => $attendance->meeting_id,
                        ]);
                        continue;
                    }

                    try {
                        $emailSent = false;
                        $whatsappSent = false;

                        if (!empty($parent->email)) {
                            Mail::to($parent->email)->send(new StudentLateArrivalAlertMail(
                                $student,
                                $parent,
                                $attendance->meeting,
                                $attendance->late_minutes,
                                $lateMinutes,
                                $attendance->join_at,
                                $attendance->scheduled_start_at
                            ));
                            $emailSent = true;
                        }

                        if ($whatsappSettings->enabled() && $whatsappSettings->notifyLateRealtime()) {
                            $parentPhone = $parent->phone ?: $parent->phone2;
                            if ($parentPhone) {
                                $whatsappSent = $whatsappGateway->sendMessage(
                                    $parentPhone,
                                    $messageBuilder->lateArrival(
                                        $student,
                                        $attendance->meeting,
                                        (int) $attendance->late_minutes,
                                        $attendance->join_at,
                                        $attendance->scheduled_start_at
                                    ),
                                    ['type' => 'late_realtime', 'student_id' => $student->id, 'meeting_id' => $attendance->meeting_id]
                                );
                            } else {
                                Log::warning('Late arrival alert skipped: parent phone missing', [
                                    'student_user_id' => $student->id,
                                    'meeting_id' => $attendance->meeting_id,
                                ]);
                            }
                        }

                        if ($emailSent || $whatsappSent) {
                            $attendance->notified_late_realtime_at = now();
                            $attendance->save();
                            $sent++;
                        }
                    } catch (\Throwable $e) {
                        Log::error('Late arrival alert failed', [
                            'student_user_id' => $student->id,
                            'meeting_id' => $attendance->meeting_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Late arrival alerts sent: {$sent}");
        return self::SUCCESS;
    }
}
