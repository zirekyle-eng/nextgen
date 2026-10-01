<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentDailyAbsenceAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $parent;
    public $date;

    public function __construct(User $student, User $parent, string $date)
    {
        $this->student = $student;
        $this->parent = $parent;
        $this->date = $date;
    }

    public function build()
    {
        $subject = 'Daily Absence Alert - ' . ($this->student->name ?: 'Student');
        return $this->subject($subject)
            ->view('emails.student-daily-absence-alert');
    }
}
