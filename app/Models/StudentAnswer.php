<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $table = 'student_answers';
    protected $primaryKey = 'answer_id';

    protected $fillable = [
        'attempt_id',
        'question_id',
        'student_answer',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    // Get the quiz attempt for this student answer.
    public function attempt()
    {
        return $this->belongsTo(Attempt::class, 'attempt_id', 'attempt_id');
    }

    // Get the question answered by the student.
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}