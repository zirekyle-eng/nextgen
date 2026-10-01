<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClubMember extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'club_id', 'user_id', 'joined_at'
    ];

    protected $dates = ['joined_at'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
