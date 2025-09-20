<?php 

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;

class QuestionService
{
    public function createQuestion($request)
    {
        $question = Question::create($request->all());
        return $question;
    }

    public function createQuestionOption($request)
    {
        $questionOption = QuestionOption::create($request->all());
        return $questionOption;
    }
}
