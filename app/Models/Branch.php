<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Branch extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'branch_name',
        'email',
        'password',
        'branch_phone',
        'branch_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
