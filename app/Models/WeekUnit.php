<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekUnit extends Model
{
    protected $fillable = [
        'subject_week_id',
        'unit_number',
        'title',
    ];

    public function week()
    {
        return $this->belongsTo(SubjectWeek::class, 'subject_week_id');
    }

    public function generalFiles()
    {
        return $this->hasMany(UnitGeneralFile::class);
    }

    public function lessons()
    {
        return $this->hasMany(UnitLesson::class)->orderBy('lesson_number');
    }

    public function quizzes()
    {
        return $this->hasMany(AcademicQuiz::class, 'week_unit_id');
    }
}
