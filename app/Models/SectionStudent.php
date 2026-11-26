<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionStudent extends Model
{
    protected $table = 'section_students';
    protected $primaryKey = 'id';

    protected $fillable = [
        'section_id',
        'student_id',
    ];

    // Get the class section for this enrollment record.
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    // Get the student user for this enrollment record.
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}