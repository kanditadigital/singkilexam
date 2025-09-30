<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Student;
use App\Models\Employee;
use App\Models\ExamParticipant;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Authenticatable
{
    use HasFactory, Notifiable;
    
    protected $fillable = [
        'branch_id',
        'school_npsn',
        'school_name',
        'email',
        'password',
        'school_phone',
        'school_address',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function examParticipants()
    {
        return $this->hasMany(ExamParticipant::class);
    }
}
