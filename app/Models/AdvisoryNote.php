<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvisoryNote extends Model
{
    use HasFactory;

    protected $table = 'advisory_notes';

    protected $fillable = [
        'conversation_id',
        'advisor_id',
        'note_type',
        'title',
        'content',
        'is_visible_to_guardian',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_visible_to_guardian' => 'boolean',
    ];

    /**
     * Get the conversation this note belongs to
     */
    public function conversation()
    {
        return $this->belongsTo(AdvisoryConversation::class, 'conversation_id', 'id');
    }

    /**
     * Get the advisor who created this note
     */
    public function advisor()
    {
        return $this->belongsTo(\App\User::class, 'advisor_id', 'id');
    }
}
