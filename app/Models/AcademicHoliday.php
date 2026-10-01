<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicHoliday extends Model
{
    protected $fillable = [
        'academic_session_id',
        'name',
        'holiday_type',
        'starts_on',
        'ends_on',
        'note',
        'created_by',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
}
