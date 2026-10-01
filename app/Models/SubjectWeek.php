<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectWeek extends Model
{
    protected $fillable = [
        'subject_id',
        'academic_session_id',
        'week_number',
        'title',
        'start_date',
        'end_date',
        'is_blocked',
        'block_note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_blocked' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function units()
    {
        return $this->hasMany(WeekUnit::class)->orderBy('unit_number');
    }
}
