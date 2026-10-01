<?php

namespace App\Mail;

use App\Models\BbgMeeting;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentAttendanceAfterClassMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $parent;
    public $meeting;
    public $status;
    public $lateMinutes;
    public $scheduledStartAt;

    public function __construct(User $student, User $parent, ?BbgMeeting $meeting, string $status, int $lateMinutes, $scheduledStartAt)
    {
        $this->student = $student;
        $this->parent = $parent;
        $this->meeting = $meeting;
        $this->status = $status;
        $this->lateMinutes = $lateMinutes;
        $this->scheduledStartAt = $scheduledStartAt;
    }

    public function build()
    {
        $subject = 'Attendance Update - ' . ($this->student->name ?: 'Student');
        return $this->subject($subject)
            ->view('emails.student-attendance-after-class');
    }
}
