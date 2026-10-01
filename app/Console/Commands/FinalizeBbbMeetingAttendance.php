<?php

namespace App\Console\Commands;

use App\Mail\StudentAttendanceAfterClassMail;
use App\Models\BbbMeetingAttendance;
use App\Models\BbgMeeting;
use App\Models\StudentRecord;
use App\Services\BbbAttendanceService;
use App\Services\BbbWhatsappMessageBuilder;
use App\Services\WhatsappGateway;
use App\Services\WhatsappSettings;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FinalizeBbbMeetingAttendance extends Command
{
    protected $signature = 'bbb:finalize-meeting-attendance';

    protected $description = 'Finalize attendance for ended meetings and send after-class notifications';

    public function handle(
        BbbAttendanceService $attendanceService,
        WhatsappGateway $whatsappGateway,
        BbbWhatsappMessageBuilder $messageBuilder,
        WhatsappSettings $whatsappSettings
    )
    {
        $notifyPresent = (bool) config('bigbluebutton.attendance_notify_present_after_class', true);
        $notifyLate = (bool) config('bigbluebutton.attendance_notify_late_after_class', true);
        $notifyAbsent = (bool) config('bigbluebutton.attendance_notify_absent_after_class', true);

        if (!$notifyPresent && !$notifyLate && !$notifyAbsent) {
            $this->line('After-class attendance notifications are disabled.');
            return self::SUCCESS;
        }

        $lateMinutes = max((int) config('bigbluebutton.attendance_late_minutes', 5), 0);
        $delayMinutes = max((int) config('bigbluebutton.attendance_after_class_delay_minutes', 5), 0);
        $threshold = now()->subMinutes($delayMinutes);

        $meetings = BbgMeeting::whereNull('attendance_processed_at')
            ->where(function ($query) use ($threshold) {
                $query->whereNotNull('ended_at')->where('ended_at', '<=', $threshold)
                    ->orWhere(function ($q) use ($threshold) {
                        $q->whereNull('ended_at')->where('created_at', '<=', $threshold->copy()->subHour());
                    });
            })
            ->orderBy('id')
            ->get();

        $processed = 0;

        foreach ($meetings as $meeting) {
            $ttrId = $meeting->ttr_id;
            if (!$ttrId) {
                $meeting->attendance_processed_at = now();
                $meeting->save();
                continue;
            }

            $classId = optional($meeting->timeTableRecord)->my_class_id;
            if (!$classId) {
                $meeting->attendance_processed_at = now();
                $meeting->save();
                continue;
            }

            $students = StudentRecord::where('my_class_id', $classId)
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values();

            foreach ($students as $studentId) {
                $attendance = BbbMeetingAttendance::firstOrNew([
                    'meeting_id' => $meeting->meeting_id,
                    'student_user_id' => $studentId,
                ]);

                if (!$attendance->scheduled_start_at || !$attendance->scheduled_end_at) {
                    [$scheduledStart, $scheduledEnd] = $attendanceService->resolveMeetingSchedule($meeting);
                    $attendance->scheduled_start_at = $scheduledStart;
                    $attendance->scheduled_end_at = $scheduledEnd;
                }

                $status = 'absent';
                if ($attendance->join_at) {
                    $late = 0;
                    if ($attendance->scheduled_start_at) {
                        $late = max((int) $attendance->join_at->diffInMinutes($attendance->scheduled_start_at, false), 0);
                    }
                    $status = $late >= $lateMinutes ? 'late' : 'present';
                    $attendance->late_minutes = $late;
                }

                $attendance->status = $status;
                $attendance->save();

                $student = User::find($studentId);
                if (!$student || $student->user_type !== 'student') {
                    continue;
                }

                $parent = $attendanceService->resolveParent($student);
                if (!$parent) {
                    Log::warning('After-class attendance skipped: parent missing', [
                        'student_user_id' => $studentId,
                        'meeting_id' => $meeting->meeting_id,
                    ]);
                    continue;
                }

                if ($status === 'present' && !$notifyPresent) {
                    continue;
                }
                if ($status === 'late' && !$notifyLate) {
                    continue;
                }
                if ($status === 'absent' && !$notifyAbsent) {
                    continue;
                }

                $alreadyNotified = ($status === 'present' && $attendance->notified_present_after_class_at)
                    || ($status === 'late' && $attendance->notified_late_after_class_at)
                    || ($status === 'absent' && $attendance->notified_absent_after_class_at);

                if ($alreadyNotified) {
                    continue;
                }

                try {
                    $emailSent = false;
                    $whatsappSent = false;

                    if (!empty($parent->email)) {
                        Mail::to($parent->email)->send(new StudentAttendanceAfterClassMail(
                            $student,
                            $parent,
                            $meeting,
                            $status,
                            $attendance->late_minutes,
                            $attendance->scheduled_start_at
                        ));
                        $emailSent = true;
                    }

                    if ($whatsappSettings->enabled()) {
                        $shouldSend = ($status === 'present' && $whatsappSettings->notifyAfterClassPresent())
                            || ($status === 'late' && $whatsappSettings->notifyAfterClassLate())
                            || ($status === 'absent' && $whatsappSettings->notifyAfterClassAbsent());

                        if ($shouldSend) {
                            $parentPhone = $parent->phone ?: $parent->phone2;
                            if ($parentPhone) {
                                $whatsappSent = $whatsappGateway->sendMessage(
                                    $parentPhone,
                                    $messageBuilder->afterClass(
                                        $student,
                                        $meeting,
                                        $status,
                                        (int) $attendance->late_minutes,
                                        $attendance->scheduled_start_at
                                    ),
                                    ['type' => 'after_class', 'student_id' => $student->id, 'meeting_id' => $meeting->meeting_id, 'status' => $status]
                                );
                            } else {
                                Log::warning('After-class attendance skipped: parent phone missing', [
                                    'student_user_id' => $studentId,
                                    'meeting_id' => $meeting->meeting_id,
                                ]);
                            }
                        }
                    }

                    if ($emailSent || $whatsappSent) {
                        if ($status === 'present') {
                            $attendance->notified_present_after_class_at = now();
                        } elseif ($status === 'late') {
                            $attendance->notified_late_after_class_at = now();
                        } else {
                            $attendance->notified_absent_after_class_at = now();
                        }
                        $attendance->save();
                    }
                } catch (\Throwable $e) {
                    Log::error('After-class attendance email failed', [
                        'student_user_id' => $studentId,
                        'meeting_id' => $meeting->meeting_id,
                        'status' => $status,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $meeting->attendance_processed_at = now();
            $meeting->save();
            $processed++;
        }

        $this->info("Meetings processed: {$processed}");
        return self::SUCCESS;
    }
}
