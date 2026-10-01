<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbgMeeting extends Model
{
    use HasFactory;

    protected $table = 'bbg_meetings';

    protected $fillable = [
        'meeting_id',
        'room_id',
        'ttr_id',
        'tt_id',
        'day',
        'meeting_name',
        'description',
        'duration',
        'recording',
        'status',
        'moderator_password',
        'attendee_password',
        'created_by',
        'started_at',
        'ended_at',
        'attendance_processed_at',
    ];

    protected $casts = [
        'recording' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'attendance_processed_at' => 'datetime',
    ];

    public function timeTableRecord()
    {
        return $this->belongsTo(TimeTableRecord::class, 'ttr_id');
    }

    public function recordings()
    {
        return $this->hasMany(BbgRecording::class, 'meeting_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isStarted()
    {
        return $this->status === 'started';
    }

    public function isEnded()
    {
        return $this->status === 'ended';
    }
}
