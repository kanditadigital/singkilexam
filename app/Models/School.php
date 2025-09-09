<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;

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
}
