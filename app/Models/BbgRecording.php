<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbgRecording extends Model
{
    use HasFactory;

    protected $table = 'bbg_recordings';

    protected $fillable = [
        'meeting_id',
        'recording_id',
        'name',
        'published',
        'download_url',
        'playback_url',
        'duration',
    ];

    public function meeting()
    {
        return $this->belongsTo(BbgMeeting::class, 'meeting_id');
    }

    public function isPublished()
    {
        return $this->published === 'true';
    }
}
