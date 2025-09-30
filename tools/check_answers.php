<?php
use App\Models\ExamAttemptQuestion;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$aq = ExamAttemptQuestion::with('question.questionOptions')->latest()->first();
if (!$aq) {
    echo "No attempt questions found\n";
    exit(0);
}

echo "Question Type: {$aq->question->question_type}\n";
echo "Stored Answer JSON: {$aq->answer}\n";

echo "Decoded Answer:".PHP_EOL;
var_export(json_decode($aq->answer ?? 'null', true));
echo PHP_EOL;

echo "Correct Options:".PHP_EOL;
foreach ($aq->question->questionOptions as $opt) {
    echo "- ID {$opt->id} label {$opt->option_label} is_correct=".var_export($opt->is_correct, true)."\n";
}
