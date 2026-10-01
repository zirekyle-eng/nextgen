<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbbCameraMonitor extends Model
{
    use HasFactory;

    protected $table = 'bbb_camera_monitors';

    protected $fillable = [
        'meeting_id',
        'student_user_id',
        'camera_off_since',
        'alert_sent_at',
        'last_event_name',
    ];

    protected $casts = [
        'camera_off_since' => 'datetime',
        'alert_sent_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(BbgMeeting::class, 'meeting_id', 'meeting_id');
    }
}
