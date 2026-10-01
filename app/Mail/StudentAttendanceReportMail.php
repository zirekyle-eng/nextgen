<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentAttendanceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $parent;
    public $termKey;
    public $from;
    public $to;
    public $summary;
    public $rows;

    public function __construct(User $student, User $parent, string $termKey, $from, $to, array $summary, array $rows)
    {
        $this->student = $student;
        $this->parent = $parent;
        $this->termKey = $termKey;
        $this->from = $from;
        $this->to = $to;
        $this->summary = $summary;
        $this->rows = $rows;
    }

    public function build()
    {
        $subject = 'Attendance Report - ' . ($this->student->name ?: 'Student');
        $csv = $this->buildCsv($this->rows);
        $filename = 'attendance_report_' . ($this->student->id ?: 'student') . '.csv';

        return $this->subject($subject)
            ->view('emails.student-attendance-report')
            ->attachData($csv, $filename, [
                'mime' => 'text/csv',
            ]);
    }

    private function buildCsv(array $rows): string
    {
        $lines = [];
        $lines[] = 'Date,Meeting ID,Status,Late Minutes';
        foreach ($rows as $row) {
            $lines[] = implode(',', [
                $row['date'] ?? '',
                $row['meeting_id'] ?? '',
                $row['status'] ?? '',
                $row['late_minutes'] ?? 0,
            ]);
        }

        return implode("\n", $lines);
    }
}
