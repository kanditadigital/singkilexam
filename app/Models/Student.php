<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Branch;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'branch_id',
        'school_id',
        'student_name',
        'student_nisn',
        'username',
        'password',
        'student_gender',
        'student_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function examParticipants()
    {
        return $this->hasMany(ExamParticipant::class);
    }
}
