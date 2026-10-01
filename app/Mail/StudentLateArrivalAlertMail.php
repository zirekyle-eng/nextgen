<?php

namespace App\Mail;

use App\Models\BbgMeeting;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentLateArrivalAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $parent;
    public $meeting;
    public $lateMinutes;
    public $thresholdMinutes;
    public $joinAt;
    public $scheduledStartAt;

    public function __construct(User $student, User $parent, ?BbgMeeting $meeting, int $lateMinutes, int $thresholdMinutes, $joinAt, $scheduledStartAt)
    {
        $this->student = $student;
        $this->parent = $parent;
        $this->meeting = $meeting;
        $this->lateMinutes = $lateMinutes;
        $this->thresholdMinutes = $thresholdMinutes;
        $this->joinAt = $joinAt;
        $this->scheduledStartAt = $scheduledStartAt;
    }

    public function build()
    {
        $subject = 'Late Arrival Alert - ' . ($this->student->name ?: 'Student');
        return $this->subject($subject)
            ->view('emails.student-late-arrival-alert');
    }
}
