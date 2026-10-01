<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CurriculumFile extends Model
{
    protected $fillable = [
        'name',              // ← هذا كان ناقص
        'original_name',
        'path',
        'year',
        'subject',
        'extension',
        'size',
        'mime_type',
        'content_text',
        'user_id'
    ];
    
    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
    
    public function scopeOfYear($query, $year)
    {
        return $query->where('year', $year);
    }
    
    public function scopeOfSubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }
}
