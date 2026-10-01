<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicQuiz extends Model
{
    protected $attributes = [
        'moodle_audience' => 'both',
    ];

    protected $fillable = [
        'subject_id',
        'week_unit_id',
        'unit_lesson_id',
        'title',
        'time_limit_minutes',
        'available_from',
        'available_until',
        'question_file_path',
        'question_original_name',
        'question_mime_type',
        'question_size',
        'xml_file_path',
        'moodle_cmid',
        'moodle_instance_id',
        'moodle_view_url',
        'uploaded_by',
        'moodle_audience',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function unit()
    {
        return $this->belongsTo(WeekUnit::class, 'week_unit_id');
    }

    public function lesson()
    {
        return $this->belongsTo(UnitLesson::class, 'unit_lesson_id');
    }
}
