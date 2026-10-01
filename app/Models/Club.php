<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Club extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'leader_id', 'is_active'
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members()
    {
        return $this->hasMany(ClubMember::class);
    }

    public function studentMembers()
    {
        return $this->hasManyThrough(
            User::class,
            ClubMember::class,
            'club_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function schedules()
    {
        return $this->hasMany(ClubSchedule::class);
    }

    public function chats()
    {
        return $this->hasMany(ClubChat::class)->orderBy('created_at', 'desc');
    }

    public function galleries()
    {
        return $this->hasMany(ClubGallery::class);
    }
}
