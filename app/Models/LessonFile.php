<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonFile extends Model
{
    protected $attributes = [
        'lesson_type' => 'digital_resources',
        'moodle_activity_type' => 'resource',
        'moodle_audience' => 'both',
    ];

    protected $fillable = [
        'unit_lesson_id',
        'title',
        'lesson_type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'content_text',
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

    public function lesson()
    {
        return $this->belongsTo(UnitLesson::class, 'unit_lesson_id');
    }
}
