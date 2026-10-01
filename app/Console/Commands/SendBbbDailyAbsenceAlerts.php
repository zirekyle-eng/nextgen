<?php

namespace App\Console\Commands;

use App\Mail\StudentDailyAbsenceAlertMail;
use App\Models\BbbDailyAbsence;
use App\Models\BbbMeetingAttendance;
use App\Models\BbgMeeting;
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

class SendBbbDailyAbsenceAlerts extends Command
{
    protected $signature = 'bbb:send-daily-absence-alerts {--date= : YYYY-MM-DD override}';

    protected $description = 'Send parent alerts for students absent for a full day';

    public function handle(
        BbbAttendanceService $attendanceService,
        WhatsappGateway $whatsappGateway,
        BbbWhatsappMessageBuilder $messageBuilder,
        WhatsappSettings $whatsappSettings
    )
    {
        if (!config('bigbluebutton.attendance_notify_daily_absent', true)) {
            $this->line('Daily absence alerts are disabled.');
            return self::SUCCESS;
        }

        $dateInput = $this->option('date');
        $date = $dateInput ? Carbon::parse($dateInput)->toDateString() : now()->toDateString();

        $meetings = BbgMeeting::whereDate('created_at', $date)->get();
        if ($meetings->isEmpty()) {
            $this->line('No meetings found for date ' . $date);
            return self::SUCCESS;
        }

        $classIds = $meetings->pluck('ttr_id')->filter()->unique()->map(function ($ttrId) {
            return optional(\App\Models\TimeTableRecord::find($ttrId))->my_class_id;
        })->filter()->unique()->values();

        foreach ($classIds as $classId) {
            $students = StudentRecord::where('my_class_id', $classId)
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values();

            foreach ($students as $studentId) {
                $already = BbbDailyAbsence::where('student_user_id', $studentId)
                    ->where('absence_date', $date)
                    ->first();

                if ($already && $already->notified_at) {
                    continue;
                }

                $attended = BbbMeetingAttendance::where('student_user_id', $studentId)
                    ->whereDate('join_at', $date)
                    ->exists();

                if ($attended) {
                    continue;
                }

                $student = User::find($studentId);
                if (!$student || $student->user_type !== 'student') {
                    continue;
                }

                $parent = $attendanceService->resolveParent($student);
                if (!$parent) {
                    Log::warning('Daily absence skipped: parent missing', [
                        'student_user_id' => $studentId,
                        'date' => $date,
                    ]);
                    continue;
                }

                try {
                    $emailSent = false;
                    $whatsappSent = false;

                    if (!empty($parent->email)) {
                        Mail::to($parent->email)->send(new StudentDailyAbsenceAlertMail(
                            $student,
                            $parent,
                            $date
                        ));
                        $emailSent = true;
                    }

                    if ($whatsappSettings->enabled() && $whatsappSettings->notifyDailyAbsent()) {
                        $parentPhone = $parent->phone ?: $parent->phone2;
                        if ($parentPhone) {
                            $whatsappSent = $whatsappGateway->sendMessage(
                                $parentPhone,
                                $messageBuilder->dailyAbsence($student, $date),
                                ['type' => 'daily_absent', 'student_id' => $student->id, 'date' => $date]
                            );
                        } else {
                            Log::warning('Daily absence skipped: parent phone missing', [
                                'student_user_id' => $studentId,
                                'date' => $date,
                            ]);
                        }
                    }

                    if ($emailSent || $whatsappSent) {
                        if (!$already) {
                            $already = new BbbDailyAbsence([
                                'student_user_id' => $studentId,
                                'absence_date' => $date,
                            ]);
                        }
                        $already->notified_at = now();
                        $already->save();
                    }
                } catch (\Throwable $e) {
                    Log::error('Daily absence email failed', [
                        'student_user_id' => $studentId,
                        'date' => $date,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info('Daily absence alerts processed for ' . $date);
        return self::SUCCESS;
    }
}
