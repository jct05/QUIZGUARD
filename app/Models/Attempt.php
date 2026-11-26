<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $table = 'attempts';
    protected $primaryKey = 'attempt_id';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'started_at',
        'submitted_at',
        'score',
        'total_points',
        'status',
        'violation_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // Get the quiz linked to this student attempt.
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    // Get the student user who made this attempt.
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    // Get all answers submitted during this quiz attempt.
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id', 'attempt_id');
    }

    // Get all violations recorded during this quiz attempt.
    public function violationLogs()
    {
        return $this->hasMany(ViolationLog::class, 'attempt_id', 'attempt_id');
    }
}