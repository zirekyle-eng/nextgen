<?php

namespace App\Mail;

use App\Models\BbgMeeting;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class StudentCameraOffAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $parent;
    public $meeting;
    public $cameraOffSince;
    public $minutes;

    /**
     * Create a new message instance.
     */
    public function __construct(User $student, User $parent, ?BbgMeeting $meeting, $cameraOffSince, int $minutes)
    {
        $this->student = $student;
        $this->parent = $parent;
        $this->meeting = $meeting;
        $this->cameraOffSince = $cameraOffSince instanceof Carbon ? $cameraOffSince : Carbon::parse($cameraOffSince);
        $this->minutes = $minutes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Camera Alert: ' . ($this->student->name ?: 'Student') . ' camera is off';

        return $this->subject($subject)
            ->view('emails.student-camera-off-alert');
    }
}
