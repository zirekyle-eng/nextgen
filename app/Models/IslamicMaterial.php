<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IslamicMaterial extends Model
{
    use HasFactory;

    protected $table = 'islamic_materials';

    protected $fillable = [
        'title',
        'description',
        'content',
        'type', // lesson, activity, story, hadith, etc
        'stage', // primary, middle, secondary (or null for all)
        'my_class_id', // specific class (or null for all)
        'subject', // Quran, Hadith, Islamic History, etc
        'level', // beginner, intermediate, advanced
        'teacher', // Teacher name or assignment
        'time_table', // Time table or schedule
        'image_path',
        'video_url',
        'attachment_path',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the class associated with this material
     */
    public function class()
    {
        return $this->belongsTo(MyClass::class, 'my_class_id');
    }

    /**
     * Scope to get active materials only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNotNull('published_at');
    }

    /**
     * Scope to filter by stage
     */
    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage)->orWhereNull('stage');
    }

    /**
     * Scope to filter by class
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('my_class_id', $classId)->orWhereNull('my_class_id');
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by subject
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }
}
