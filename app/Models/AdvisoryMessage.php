<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvisoryMessage extends Model
{
    use HasFactory;

    protected $table = 'advisory_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation()
    {
        return $this->belongsTo(AdvisoryConversation::class, 'conversation_id', 'id');
    }

    /**
     * Get the sender of this message
     */
    public function sender()
    {
        if ($this->sender_type === 'guardian') {
            return $this->belongsTo(Guardian::class, 'sender_id', 'id');
        } else {
            return $this->belongsTo(\App\User::class, 'sender_id', 'id');
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
