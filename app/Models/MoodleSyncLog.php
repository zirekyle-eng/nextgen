<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleSyncLog extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'status',
        'moodle_course_id',
        'response_payload',
        'error_message',
    ];
}
