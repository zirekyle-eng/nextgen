<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $connection = 'nextgen';
    protected $table = 'students';

    protected $fillable = [
        'guardian_id',
        'first_name',
        'last_name',
        'dob',
        'country',
        'stage',
        'preferred_start_date',
        'status',
        'religion_status',
    ];

    protected $casts = [
        'dob' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the guardian that owns this student
     */
    public function guardian()
    {
        return $this->belongsTo(Guardian::class, 'guardian_id', 'id');
    }

    /**
     * Get all conversations for this student
     */
    public function conversations()
    {
        return $this->hasMany(AdvisoryConversation::class, 'student_id', 'id');
    }

    /**
     * Get all marks for this student
     */
    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'id');
    }

    /**
     * Get the full name of the student
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get student age based on date of birth
     */
    public function getAgeAttribute()
    {
        if ($this->dob) {
            return \Carbon\Carbon::parse($this->dob)->age;
        }
        return null;
    }

    /**
     * Scope to filter by guardian
     */
    public function scopeByGuardian($query, $guardianId)
    {
        return $query->where('guardian_id', $guardianId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by stage
     */
    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }
}
