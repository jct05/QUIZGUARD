<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';
    protected $primaryKey = 'quiz_id';

    protected $fillable = [
        'teacher_id',
        'bank_id',
        'subject_id',
        'section_id',
        'quiz_code',
        'title',
        'time_limit_min',
        'time_per_question_sec',
        'max_violation',
        'passing_score',
        'attempts_allowed',
        'shuffle_questions',
        'number_of_questions',
        'shuffle_answers',
        'allow_review',
        'disable_backtracking',
        'open_at',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
        'allow_review'         => 'boolean',
        'disable_backtracking' => 'boolean',
        'open_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Get the teacher who created this quiz.
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    // Get the question bank used by this quiz.
    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class, 'bank_id', 'bank_id');
    }

    // Get the subject linked to this quiz.
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    // Get the section assigned to take this quiz.
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    // Get all student attempts recorded for this quiz.
    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'quiz_id', 'quiz_id');
    }
}