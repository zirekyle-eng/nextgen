<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorLessonProgress extends Model
{
    protected $table = 'tutor_lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'unit_id',
        'subject',
        'status',
        'last_mode',
        'started_at',
        'completed_at',
        'last_activity_at',
        'conversation_messages',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'conversation_messages' => 'array',
    ];
}

