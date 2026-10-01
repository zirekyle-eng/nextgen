<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TutorQuizResult extends Model
{
    protected $table = 'tutor_quiz_results';

    protected $fillable = [
        'user_id',
        'subject',
        'file_id',
        'file_name',
        'score',
        'total',
        'percentage',
        'points_awarded',
        'recommendation',
    ];

    protected $casts = [
        'score' => 'float',
        'total' => 'integer',
        'percentage' => 'integer',
        'points_awarded' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file()
    {
        return $this->belongsTo(CurriculumFile::class, 'file_id');
    }
}

