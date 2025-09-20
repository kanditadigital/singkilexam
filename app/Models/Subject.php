<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExamSession;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'subject_name',
        'subject_code',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'subject_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
