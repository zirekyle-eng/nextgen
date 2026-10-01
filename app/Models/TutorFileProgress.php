<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorFileProgress extends Model
{
    protected $table = 'tutor_file_progress';

    protected $fillable = [
        'user_id',
        'file_id',
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
