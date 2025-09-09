<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\Student;
use App\Models\Employee;

class Branch extends Model
{
    protected $fillable = [
        'branch_name',
        'email',
        'password',
        'branch_phone',
        'branch_address',
    ];

    public function schools()
    {
        return $this->hasMany(School::class);
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
