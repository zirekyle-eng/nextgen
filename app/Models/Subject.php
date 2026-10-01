<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Subject extends Eloquent
{
    protected $fillable = ['name', 'my_class_id', 'teacher_id', 'moodle_course_id', 'slug', 'is_activity'];

    protected $casts = [
        'is_activity' => 'boolean',
    ];

    public function scopeExcludeActivities(Builder $query): Builder
    {
        if (!Schema::hasColumn('subjects', 'is_activity')) {
            return $query;
        }

        return $query->where('is_activity', false);
    }

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function generalFiles()
    {
        return $this->hasMany(SubjectGeneralFile::class);
    }

    public function weeks()
    {
        return $this->hasMany(SubjectWeek::class)->orderBy('week_number');
    }
}
