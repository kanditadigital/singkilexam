<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\School;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'branch_id',
        'school_id',
        'employee_name',
        'email',
        'employee_phone',
        'username',
        'password',
        'pass_text',
        'employee_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the unique identifier for the user.
     * This should return the primary key for session storage.
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getKeyName()};
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
