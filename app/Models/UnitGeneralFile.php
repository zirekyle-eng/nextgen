<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitGeneralFile extends Model
{
    protected $attributes = [
        'moodle_activity_type' => 'resource',
        'moodle_audience' => 'both',
    ];

    protected $fillable = [
        'week_unit_id',
        'title',
        'general_type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
        'moodle_cmid',
        'moodle_activity_type',
        'activity_intro',
        'available_from',
        'due_at',
        'cutoff_at',
        'moodle_view_url',
        'moodle_audience',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'due_at' => 'datetime',
        'cutoff_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(WeekUnit::class, 'week_unit_id');
    }
}
