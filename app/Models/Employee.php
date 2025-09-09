<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\School;

class Employee extends Model
{
    protected $fillable = [
        'branch_id',
        'school_id',
        'employee_name',
        'email',
        'password',
        'pass_text',
        'employee_type',
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
