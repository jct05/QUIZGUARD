<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false; // Disable automatic timestamps so missing updated_at never causes errors.

    protected $fillable = [
        'school_id_num',
        'role',
        'action',
        'description',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Get the user matching this activity school ID.
    public function user()
    {
        return $this->belongsTo(User::class, 'school_id_num', 'school_id_num');
    }

    // Save a new activity log entry to database.
    public static function record($schoolIdNum, $role, $action, $description = null)
    {
        return self::create([
            'school_id_num' => $schoolIdNum,
            'role'          => $role,
            'action'        => $action,
            'description'   => $description,
            'created_at'    => now(),
        ]);
    }
}