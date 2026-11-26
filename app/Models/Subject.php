<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';

    protected $fillable = [
        'teacher_id',
        'subject_code',
        'subject_name',
        'status',
    ];

    // Get the teacher who owns this subject record.
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    // Get all sections belonging to this subject.
    public function sections()
    {
        return $this->hasMany(Section::class, 'subject_id', 'subject_id');
    }

    // Get all question banks belonging to this subject.
    public function questionBanks()
    {
        return $this->hasMany(QuestionBank::class, 'subject_id', 'subject_id');
    }

    // Get all quizzes created under this subject.
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'subject_id', 'subject_id');
    }
}