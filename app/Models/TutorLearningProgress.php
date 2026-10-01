<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TutorLearningProgress extends Model
{
    protected $table = 'tutor_learning_progress';

    protected $fillable = [
        'user_id',
        'subject',
        'last_file_id',
        'last_file_name',
        'last_mode',
        'last_quiz_score',
        'last_quiz_total',
        'points',
        'completed_quizzes',
        'conversation_messages',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'last_quiz_score' => 'float',
        'last_quiz_total' => 'integer',
        'points' => 'integer',
        'completed_quizzes' => 'integer',
        'conversation_messages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file()
    {
        return $this->belongsTo(CurriculumFile::class, 'last_file_id');
    }
}
