<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'role',
        'school_id_num',
        'first_name',
        'middle_name',
        'last_name',
        'dwcc_email',
        'password_hash',
        'department',
        'status',
        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
    ];

    protected $hidden = [
        'password_hash',
        'otp_hash',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    // Return the custom password hash column for user authentication.
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // makes a new 6-digit code, saves it hashed and emails it
    public function sendOtp()
    {
        $otp = random_int(100000, 999999);   // random_int is secure

        $this->update([
            'otp_hash'       => Hash::make((string) $otp),
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts'   => 0,
        ]);

        // plain text email
        Mail::raw("Your QuizGuard verification code is $otp. It expires in 10 minutes.", function ($m) {
            $m->to($this->dwcc_email)->subject('QuizGuard verification code');
        });
    }

    // Get all subjects created by this teacher user.
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'teacher_id', 'user_id');
    }

    // Get all sections handled by this teacher user.
    public function sections()
    {
        return $this->hasMany(Section::class, 'teacher_id', 'user_id');
    }

    // Get all sections where this student user is enrolled.
    public function enrolledSections()
    {
        return $this->belongsToMany(Section::class, 'section_students', 'student_id', 'section_id');
    }

    // Get all question banks uploaded by this teacher.
    public function questionBanks()
    {
        return $this->hasMany(QuestionBank::class, 'teacher_id', 'user_id');
    }

    // Get all quizzes created by this teacher user.
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'teacher_id', 'user_id');
    }

    // Get all quiz attempts made by this student.
    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'student_id', 'user_id');
    }
}