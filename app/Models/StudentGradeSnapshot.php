<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentGradeSnapshot extends Model
{
    use HasFactory;

    protected $table = 'student_grade_snapshots';

    protected $fillable = [
        'conversation_id',
        'student_id',
        'subject_id',
        'exam_id',
        't1',
        't2',
        't3',
        't4',
        'tca',
        'exm',
        'total',
        'grade',
        'percentage',
        'snapshot_date',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'snapshot_date' => 'date',
    ];

    /**
     * Get the conversation this snapshot belongs to
     */
    public function conversation()
    {
        return $this->belongsTo(AdvisoryConversation::class, 'conversation_id', 'id');
    }

    /**
     * Get the student
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /**
     * Get the subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    /**
     * Get the exam
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'id');
    }
}
