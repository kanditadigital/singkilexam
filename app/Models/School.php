<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\Student;
use App\Models\Employee;

class School extends Model
{
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
}
