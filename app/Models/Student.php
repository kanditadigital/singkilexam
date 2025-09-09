<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\School;

class Student extends Model
{
    protected $fillable = [
        'branch_id',
        'school_id',
        'student_name',
        'student_nisn',
        'username',
        'password',
        'pass_text',
        'student_gender',
        'student_photo',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
