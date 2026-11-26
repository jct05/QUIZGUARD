<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'section_id';

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'section_name',
        'is_locked',
        'status',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    // Get the teacher assigned to manage this section.
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    // Get the subject that this section belongs to.
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    // Get all students enrolled in this class section.
    public function students()
    {
        return $this->belongsToMany(User::class, 'section_students', 'section_id', 'student_id');
    }

    // Get all enrollment records for this class section.
    public function sectionStudents()
    {
        return $this->hasMany(SectionStudent::class, 'section_id', 'section_id');
    }

    // Get all quizzes assigned to this class section.
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'section_id', 'section_id');
    }
}