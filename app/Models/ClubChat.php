<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubChat extends Model
{
    protected $table = 'club_chats';
    protected $fillable = ['club_id', 'user_id', 'message'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
