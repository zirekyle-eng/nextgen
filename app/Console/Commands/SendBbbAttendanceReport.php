<?php

namespace App\Console\Commands;

use App\Mail\StudentAttendanceReportMail;
use App\Models\BbbMeetingAttendance;
use App\Services\BbbAttendanceService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBbbAttendanceReport extends Command
{
    protected $signature = 'bbb:send-attendance-report {student_id : Student user id} {from : YYYY-MM-DD} {to : YYYY-MM-DD}';

    protected $description = 'Send attendance report for a single student (on-demand)';

    public function handle(BbbAttendanceService $attendanceService)
    {
        $studentId = (int) $this->argument('student_id');
        $from = Carbon::parse($this->argument('from'))->startOfDay();
        $to = Carbon::parse($this->argument('to'))->endOfDay();

        $student = User::find($studentId);
        if (!$student || $student->user_type !== 'student') {
            $this->error('Student not found.');
            return self::FAILURE;
        }

        $parent = $attendanceService->resolveParent($student);
        if (!$parent || empty($parent->email)) {
            $this->error('Parent email not found.');
            return self::FAILURE;
        }

        $rows = BbbMeetingAttendance::where('student_user_id', $studentId)
            ->whereNotNull('scheduled_start_at')
            ->whereBetween('scheduled_start_at', [$from, $to])
            ->orderBy('scheduled_start_at')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('No attendance records found for this range.');
            return self::SUCCESS;
        }

        $summary = [
            'present' => $rows->where('status', 'present')->count(),
            'late' => $rows->where('status', 'late')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
        ];

        $mapped = $rows->map(function ($row) {
            return [
                'date' => optional($row->scheduled_start_at)->format('Y-m-d'),
                'meeting_id' => $row->meeting_id,
                'status' => $row->status ?: 'absent',
                'late_minutes' => (int) $row->late_minutes,
            ];
        })->values()->all();

        Mail::to($parent->email)->send(new StudentAttendanceReportMail(
            $student,
            $parent,
            'custom',
            $from,
            $to,
            $summary,
            $mapped
        ));

        $this->info('Report sent.');
        return self::SUCCESS;
    }
}
