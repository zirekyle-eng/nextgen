<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubGallery extends Model
{
    protected $table = 'club_galleries';
    protected $fillable = ['club_id', 'user_id', 'file_path', 'file_type', 'title', 'description'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
