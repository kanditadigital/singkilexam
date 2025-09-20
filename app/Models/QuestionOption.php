<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_label',
        'option_key',
        'option_text',
        'option_image',
        'is_correct',
        'score',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
