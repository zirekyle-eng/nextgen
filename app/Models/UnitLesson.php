<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitLesson extends Model
{
    protected $fillable = [
        'week_unit_id',
        'lesson_number',
        'title',
    ];

    public function unit()
    {
        return $this->belongsTo(WeekUnit::class, 'week_unit_id');
    }

    public function files()
    {
        return $this->hasMany(LessonFile::class);
    }

    public function quizzes()
    {
        return $this->hasMany(AcademicQuiz::class, 'unit_lesson_id');
    }

    public function questionBanks()
    {
        return $this->hasMany(LessonQuestionBank::class, 'unit_lesson_id')->latest('id');
    }
}
