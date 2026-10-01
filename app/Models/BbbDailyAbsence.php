<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbbDailyAbsence extends Model
{
    use HasFactory;

    protected $table = 'bbb_daily_absences';

    protected $fillable = [
        'student_user_id',
        'absence_date',
        'notified_at',
    ];

    protected $casts = [
        'absence_date' => 'date',
        'notified_at' => 'datetime',
    ];
}
