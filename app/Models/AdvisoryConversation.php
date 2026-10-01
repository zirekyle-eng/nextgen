<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvisoryConversation extends Model
{
    use HasFactory;

    protected $table = 'advisory_conversations';

    protected $fillable = [
        'parent_id',
        'student_id',
        'advisor_id',
        'subject',
        'description',
        'status',
        'priority',
        'last_message_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the parent who initiated the conversation
     */
    public function parent()
    {
        return $this->belongsTo(\App\User::class, 'parent_id', 'id');
    }

    /**
     * Get the parent who initiated the conversation
     */
    public function guardian()
    {
        return $this->belongsTo(\App\User::class, 'parent_id', 'id');
    }

    /**
     * Get the student related to this conversation
     */
    public function student()
    {
        return $this->belongsTo(\App\User::class, 'student_id', 'id');
    }

    /**
     * Get the advisor assigned to this conversation
     */
    public function advisor()
    {
        return $this->belongsTo(\App\User::class, 'advisor_id', 'id');
    }

    /**
     * Get all messages in this conversation
     */
    public function messages()
    {
        return $this->hasMany(AdvisoryMessage::class, 'conversation_id', 'id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all notes in this conversation
     */
    public function notes()
    {
        return $this->hasMany(AdvisoryNote::class, 'conversation_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest message in this conversation
     */
    public function latestMessage()
    {
        return $this->hasOne(AdvisoryMessage::class, 'conversation_id', 'id')
            ->latest('created_at');
    }
}
