<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubSchedule extends Model
{
    protected $table = 'club_schedules';
    protected $fillable = ['club_id', 'title', 'description', 'start_time', 'end_time', 'location', 'is_recurring', 'recurrence_type', 'day_of_week', 'schedule_time', 'recurrence_end_date'];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurrence_end_date' => 'date'
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // دالة لإرجاع الاجتماعات القادمة (للجداول المتكررة)
    public function getUpcomingInstances($limit = 10)
    {
        $instances = [];
        
        if (!$this->is_recurring) {
            return [$this];
        }

        $currentDate = now();
        $endDate = $this->recurrence_end_date ?? $currentDate->clone()->addMonths(3);
        
        while ($currentDate <= $endDate && count($instances) < $limit) {
            $dayName = $currentDate->englishDayOfWeek;
            
            if ($this->recurrence_type === 'weekly' && $dayName === $this->day_of_week) {
                $instance = clone $this;
                $instance->start_time = $currentDate->clone()->setTimeFromTimeString($this->schedule_time);
                $instances[] = $instance;
            } elseif ($this->recurrence_type === 'daily') {
                $instance = clone $this;
                $instance->start_time = $currentDate->clone()->setTimeFromTimeString($this->schedule_time);
                $instances[] = $instance;
            }
            
            $currentDate->addDay();
        }
        
        return $instances;
    }

    // دالة تنسيق عرض الجدول المتكرر
    public function getRecurrenceDisplay()
    {
        if (!$this->is_recurring) {
            return $this->start_time->format('Y-m-d H:i');
        }

        $displays = [
            'daily' => 'Daily at ' . $this->schedule_time,
            'weekly' => 'Every ' . $this->day_of_week . ' at ' . $this->schedule_time,
            'monthly' => 'Monthly at ' . $this->schedule_time
        ];

        return $displays[$this->recurrence_type] ?? '';
    }

    // ترجمة اسم اليوم إلى العربية
    public function getDayNameArabic()
    {
        $days = [
            'Saturday' => 'السبت',
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة'
        ];

        return $days[$this->day_of_week] ?? $this->day_of_week;
    }
}

