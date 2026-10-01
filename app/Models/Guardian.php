<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guardian extends Model
{
    use HasFactory;

    protected $connection = 'nextgen';
    protected $table = 'guardians';

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'role',
        'country',
        'phone',
        'phone_prefix',
        'postal_code',
        'address_line1',
        'address_line2',
        'city',
        'keep_updated',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'keep_updated' => 'boolean',
    ];

    /**
     * Get the students that belong to this guardian
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'guardian_id', 'id');
    }

    /**
     * Get all conversations for this guardian
     */
    public function conversations()
    {
        return $this->hasMany(AdvisoryConversation::class, 'guardian_id', 'id');
    }

    /**
     * Get the associated user account (parent)
     */
    public function user()
    {
        return $this->hasOne(\App\User::class, 'email', 'email');
    }

    /**
     * Get available teachers/advisors
     */
    public static function getAvailableTeachers()
    {
        return \App\User::teachers()->where('status', 'active')->get();
    }

    /**
     * Get the full name of the guardian
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the full contact information
     */
    public function getFullContactAttribute()
    {
        if ($this->phone_prefix && $this->phone) {
            return "{$this->phone_prefix}{$this->phone}";
        }
        return $this->phone;
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }
}
