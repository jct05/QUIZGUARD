<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{

    protected $table = 'question_banks';
    protected $primaryKey = 'bank_id';

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'bank_title',
        'source_file_name',
        'uploaded_at',
        'status',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Get the teacher who uploaded this question bank.
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    // Get the subject linked to this question bank.
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    // Get all questions stored inside this question bank.
    public function questions()
    {
        return $this->hasMany(Question::class, 'bank_id', 'bank_id');
    }

    // Get all quizzes using this question bank.
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'bank_id', 'bank_id');
    }
}