<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\School;

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
}
