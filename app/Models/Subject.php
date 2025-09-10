<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExamSession;

class Subject extends Model
{
    protected $fillable = [
        'subject_name',
        'subject_code',
    ];

    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }
}
