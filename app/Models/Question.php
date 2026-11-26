<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $table = 'questions';
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'bank_id',
        'question_text',
        'question_type',
        'options',
        'correct_answers',
        'points',
        'status',
    ];

    // Get the question bank that contains this question.
    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class, 'bank_id', 'bank_id');
    }

    // Get all student answers submitted for this question.
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'question_id', 'question_id');
    }

    // Get all violation logs recorded during this question.
    public function violationLogs()
    {
        return $this->hasMany(ViolationLog::class, 'question_id', 'question_id');
    }
}