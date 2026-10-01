<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonQuestionBank extends Model
{
    protected $attributes = [
        'moodle_sync_status' => 'pending',
    ];

    protected $fillable = [
        'unit_lesson_id',
        'title',
        'source_file_path',
        'source_original_name',
        'source_mime_type',
        'source_size',
        'xml_file_path',
        'question_count',
        'moodle_context_id',
        'moodle_category_id',
        'moodle_category_name',
        'moodle_category_url',
        'moodle_imported_count',
        'moodle_sync_status',
        'moodle_sync_error',
        'moodle_last_synced_at',
        'uploaded_by',
    ];

    protected $casts = [
        'moodle_last_synced_at' => 'datetime',
    ];

    public function lesson()
    {
        return $this->belongsTo(UnitLesson::class, 'unit_lesson_id');
    }
}
