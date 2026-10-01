<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectGeneralFile extends Model
{
    protected $attributes = [
        'moodle_activity_type' => 'resource',
        'moodle_audience' => 'both',
    ];

    protected $fillable = [
        'subject_id',
        'academic_session_id',
        'title',
        'general_type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
        'moodle_cmid',
        'moodle_activity_type',
        'activity_intro',
        'available_from',
        'due_at',
        'cutoff_at',
        'moodle_view_url',
        'moodle_audience',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'due_at' => 'datetime',
        'cutoff_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
}
