<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function classes()
    {
        return $this->belongsToMany(MyClass::class, 'academic_session_my_class', 'academic_session_id', 'my_class_id')
            ->withTimestamps();
    }

    public function holidays()
    {
        return $this->hasMany(AcademicHoliday::class, 'academic_session_id');
    }
}
