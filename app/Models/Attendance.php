<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'bbb_meeting_id',
        'user_id',
        'meeting_id',
        'name',
        'moderator',
        'activity_score',
        'talk_time',
        'webcam_time',
        'messages',
        'reactions',
        'poll_votes',
        'raise_hands',
        'join_time',
        'left_time',
        'duration',
        'subject_name',
        'attendance_date',
        'file_name',
        'created_by',
    ];

    protected $casts = [
        'join_time' => 'datetime',
        'left_time' => 'datetime',
        'attendance_date' => 'date',
        'moderator' => 'boolean',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bbgMeeting()
    {
        return $this->belongsTo(BbgMeeting::class, 'bbb_meeting_id');
    }

    // الـ scopes
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject_name', $subject);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeModerators($query)
    {
        return $query->where('moderator', true);
    }

    public function scopeStudents($query)
    {
        return $query->where('moderator', false);
    }

    public function getTalkTimeInMinutes()
    {
        return round($this->talk_time / 60, 2);
    }

    public function getWebcamTimeInMinutes()
    {
        return round($this->webcam_time / 60, 2);
    }

    public function getDurationInMinutes()
    {
        return round($this->duration / 60, 2);
    }
}
