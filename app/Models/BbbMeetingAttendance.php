<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbbMeetingAttendance extends Model
{
    use HasFactory;

    protected $table = 'bbb_meeting_attendances';

    protected $fillable = [
        'meeting_id',
        'student_user_id',
        'join_at',
        'left_at',
        'camera_on_count',
        'first_camera_on_at',
        'last_camera_on_at',
        'scheduled_start_at',
        'scheduled_end_at',
        'late_minutes',
        'status',
        'notified_late_realtime_at',
        'notified_present_after_class_at',
        'notified_late_after_class_at',
        'notified_absent_after_class_at',
    ];

    protected $casts = [
        'join_at' => 'datetime',
        'left_at' => 'datetime',
        'first_camera_on_at' => 'datetime',
        'last_camera_on_at' => 'datetime',
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'notified_late_realtime_at' => 'datetime',
        'notified_present_after_class_at' => 'datetime',
        'notified_late_after_class_at' => 'datetime',
        'notified_absent_after_class_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(BbgMeeting::class, 'meeting_id', 'meeting_id');
    }
}
