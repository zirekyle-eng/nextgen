<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbbTermReport extends Model
{
    use HasFactory;

    protected $table = 'bbb_term_reports';

    protected $fillable = [
        'student_user_id',
        'term_key',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
