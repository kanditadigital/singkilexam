<?php 

namespace App\Services;

use App\Models\Question;
use App\Models\Question_option;

class QuestionService
{
    public function createQuestion($request)
    {
        $question = Question::create($request->all());
        return $question;
    }

    public function createQuestionOption($request)
    {
        $questionOption = Question_option::create($request->all());
        return $questionOption;
    }
}