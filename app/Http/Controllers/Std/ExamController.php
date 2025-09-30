<?php

namespace App\Http\Controllers\Std;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\Question;
use App\Models\ExamGrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ExamCacheService;

class ExamController extends Controller
{
    private function cacheService(): ExamCacheService
    {
        return app(ExamCacheService::class);
    }

    /**
     * Cek Data Peserta dan Token
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ExamSession::with(['exam', 'subject'])->first();
        return view('std.confirmation', [
            'title' => 'Exam Confirmation',
            'data' => $data
        ]);
    }

    /**
     * Konfirmasi Token dan Data Peserta
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function checkToken(Request $request)
    {
        $request->validate([
            'confirm_student_name' => 'required',
            'exam_token'           => 'required'
        ]);

        $cekStudent = Student::where('student_name', $request->confirm_student_name)->first();
        $cekToken = Exam::where('exam_code', $request->exam_token)->first();

        if ($cekStudent) {
            if ($cekToken) {
                return redirect()->route('std.exam', $cekToken->exam_code);
            } else {
                toast('Token tidak ditemukan')->error();
                return back();
            }
        } else {
            toast('Nama peserta tidak sesuai')->error();
            return back();
        }
    }

    /**
     * Tampilkan Soal
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function showExam($token)
    {
        // Get logged in student
        $student = Auth::guard('students')->user();

        $exam = Exam::where('exam_code', $token)->firstOrFail();

        // Collect active sessions (fallback to all sessions if none active)
        $sessions = ExamSession::with(['subject' => function ($q) {
                $q->with('questions.questionOptions');
            }])
            ->where('exam_id', $exam->id)
            ->where('session_status', 'Active')
            ->get();

        if ($sessions->isEmpty()) {
            $sessions = ExamSession::with(['subject' => function ($q) {
                    $q->with('questions.questionOptions');
                }])
                ->where('exam_id', $exam->id)
                ->get();
        }

        if ($sessions->isEmpty()) {
            toast('Belum ada sesi untuk ujian ini', 'error');
            return redirect()->back();
        }

        // Use first session as primary for attempt metadata
        $primarySession = $sessions->first();

        // Ensure attempt exists
        $attempt = ExamAttempt::firstOrCreate(
            [
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'exam_session_id' => $primarySession->id,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );

        if ($attempt->status === 'submitted') {
            return redirect()->route('std.finished', $token);
        }

        // Seed attempt questions if none
        if ($attempt->questions()->count() === 0) {
            // Gather all questions from the collected sessions' subjects
            $allQuestions = collect();
            foreach ($sessions as $sess) {
                if ($sess->subject) {
                    $allQuestions = $allQuestions->merge($sess->subject->questions);
                }
            }
            // Remove duplicates by question id
            $allQuestions = $allQuestions->unique('id')->values();

            // Randomize questions if any session sets random_question to Y
            $randomQuestion = $sessions->contains(function ($s) { return $s->random_question === 'Y'; });
            if ($randomQuestion) {
                $allQuestions = $allQuestions->shuffle();
            }

            DB::transaction(function () use ($attempt, $allQuestions, $sessions) {
                $randomAnswer = $sessions->contains(function ($s) { return $s->random_answer === 'Y'; });
                foreach ($allQuestions as $idx => $q) {
                    $options = $q->questionOptions()->get();
                    if ($randomAnswer) {
                        $options = $options->shuffle();
                    }
                    ExamAttemptQuestion::create([
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $q->id,
                        'order_index' => $idx + 1,
                        'options_order' => $options->pluck('id')->values()->toArray(),
                    ]);
                }
            });
        }

        $sessionIds = $sessions->pluck('id')->map('intval')->unique()->values()->all();
        $this->cacheService()->storeAttemptSessionIds($attempt->id, $sessionIds);
        $this->cacheService()->ensureSessionQuestionsCached($exam->id, $sessionIds);

        // Current index
        $index = max(1, (int) request()->query('q', 1));
        $total = $attempt->questions()->count();
        if ($index > $total) { $index = $total; }

        $attemptQuestion = $attempt->questions()
            ->where('order_index', $index)
            ->firstOrFail();

        $this->cacheService()->hydrateQuestion($attempt, $attemptQuestion, $sessionIds);
        $this->cacheService()->applyCachedAnswer($attemptQuestion);

        $answeredCount = $this->cacheService()->getAnsweredCount($attempt);
        $statuses = $this->cacheService()->getStatuses($attempt);

        return view('std.exam', [
            'title' => 'Exam',
            'exam' => $exam,
            'session' => $primarySession,
            'attempt' => $attempt,
            'attemptQuestion' => $attemptQuestion,
            'index' => $index,
            'total' => $total,
            'token' => $token,
            'answeredCount' => $answeredCount,
            'questionStatuses' => $statuses,
        ]);
    }

    /**
     * Save answer and navigate
     */
    public function saveAnswer(Request $request, $token)
    {
        $request->validate([
            'index' => 'required|integer|min:1',
            'action' => 'required|in:next,prev,flag,save,save-multi',
        ]);

        $student = Auth::guard('students')->user();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $attempt = ExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->firstOrFail();

        $index = (int) $request->input('index');
        $total = $attempt->questions()->count();
        if ($index < 1) { $index = 1; }
        if ($index > $total) { $index = $total; }

        $attemptQuestion = $attempt->questions()
            ->where('order_index', $index)
            ->firstOrFail();

        $sessionIds = $this->cacheService()->getAttemptSessionIds($attempt->id);
        if (empty($sessionIds) && $attempt->exam_session_id) {
            $sessionIds = [$attempt->exam_session_id];
        }

        $this->cacheService()->ensureSessionQuestionsCached($exam->id, $sessionIds);
        $this->cacheService()->hydrateQuestion($attempt, $attemptQuestion, $sessionIds);
        $this->cacheService()->applyCachedAnswer($attemptQuestion);

        $isFlagAction = $request->input('action') === 'flag';

        $currentFlag = (bool) $attemptQuestion->flagged;
        $flagged = $currentFlag;
        $answerJson = $attemptQuestion->answer;
        $answeredAt = $attemptQuestion->answered_at;
        $markDirty = false;

        $questionType = $attemptQuestion->question->question_type;
        if ($questionType === 'matching') {
            $map = $request->input('matching', []); // e.g., ['L1' => 'R3', 'L2' => 'R1']
            if (is_array($map)) {
                $filtered = array_filter($map, static fn($value) => $value !== null && $value !== '');
                if (!empty($filtered)) {
                    $answerJson = json_encode($filtered);
                    $answeredAt = now();
                    $markDirty = true;
                } elseif ($answerJson !== null) {
                    $answerJson = null;
                    $answeredAt = null;
                    $markDirty = true;
                }
            }
        } elseif ($questionType === 'true_false' && $request->has('tf')) {
            // True/False grid answers: map of statement option_id => 'true'|'false'
            $map = $request->input('tf', []);
            if (is_array($map) && !empty($map)) {
                $answerJson = json_encode($map);
                $answeredAt = now();
                $markDirty = true;
            }
        } else {
            $answer = $request->input('answer');
            if ($answer !== null) {
                $answerPayload = is_array($answer) ? array_values($answer) : [$answer];
                $answerJson = json_encode($answerPayload);
                $answeredAt = now();
                $markDirty = true;
            } elseif ($questionType === 'multiple_response' && $answerJson !== null) {
                $answerJson = null;
                $answeredAt = null;
                $markDirty = true;
            }
        }

        if ($isFlagAction) {
            $flagged = !$currentFlag;
            $markDirty = true;
        }

        $this->cacheService()->storeAnswer($attemptQuestion, $answerJson, $flagged, $answeredAt, $markDirty);
        if ($markDirty) {
            $this->cacheService()->scheduleFlush($attempt);
        }

        if ($isFlagAction) {
            if ($request->ajax()) {
                return response()->json(['ok' => true, 'index' => $index]);
            }
            return redirect()->route('std.exam', [$token, 'q' => $index]);
        }

        // Navigate
        $nextIndex = $index;
        if ($request->input('action') === 'next') {
            $nextIndex = min($total, $index + 1);
        } elseif ($request->input('action') === 'prev') {
            $nextIndex = max(1, $index - 1);
        }

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'index' => $nextIndex]);
        }
        return redirect()->route('std.exam', [$token, 'q' => $nextIndex]);
    }

    /**
     * Finalize the attempt when all questions answered
     */
    public function finish(Request $request, $token)
    {
        $student = Auth::guard('students')->user();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $attempt = ExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->latest('id')
            ->firstOrFail();

        $statuses = $this->cacheService()->getStatuses($attempt);
        $firstUnanswered = collect($statuses)->firstWhere('answered', false);
        if ($firstUnanswered && !$request->boolean('force')) {
            toast('Masih ada soal belum terjawab', 'error');
            return redirect()->route('std.exam', [$token, 'q' => $firstUnanswered['order_index']]);
        }

        // Ensure cached answers are persisted before grading
        $this->cacheService()->flushAnswers($attempt);
        $this->cacheService()->clearScheduledFlush($attempt->id);

        // Compute grade
        $attemptQuestions = $attempt->questions()->with('question.questionOptions')->orderBy('order_index')->get();
        $totalQuestions = $attemptQuestions->count();
        $answeredQuestions = $attemptQuestions->whereNotNull('answer')->count();

        $sumPoints = 0.0;
        $sumMaxPoints = 0.0;
        $correctQuestions = 0;
        $details = [];

        foreach ($attemptQuestions as $aq) {
            $q = $aq->question;
            $type = $q->question_type;
            $points = 0.0;
            $maxP = 1.0; // normalize each question to 1 unless tkp

            // Decode answer
            $ans = $aq->answer ? json_decode($aq->answer, true) : null;

            $optionIdMap = $q->questionOptions
                ->mapWithKeys(fn($opt) => [(string) $opt->id => (string) $opt->id]);
            $labelToId = $q->questionOptions
                ->mapWithKeys(fn($opt) => [strtolower((string) $opt->option_label) => (string) $opt->id]);
            $keyToId = $q->questionOptions
                ->mapWithKeys(fn($opt) => [strtolower((string) ($opt->option_key ?? '')) => (string) $opt->id]);

            $normalizeSelections = function ($selected) use ($optionIdMap, $labelToId, $keyToId) {
                return collect(is_array($selected) ? $selected : [$selected])
                    ->flatMap(fn($value) => is_array($value) ? $value : [$value])
                    ->filter(function ($value) {
                        if ($value === null) {
                            return false;
                        }

                        if (is_string($value)) {
                            return trim($value) !== '';
                        }

                        return $value !== '' && $value !== [];
                    })
                    ->map(function ($value) use ($optionIdMap, $labelToId, $keyToId) {
                        $valueStr = (string) $value;

                        if (isset($optionIdMap[$valueStr])) {
                            return $optionIdMap[$valueStr];
                        }

                        $lower = strtolower($valueStr);
                        if (isset($labelToId[$lower])) {
                            return $labelToId[$lower];
                        }

                        if ($lower !== '' && isset($keyToId[$lower])) {
                            return $keyToId[$lower];
                        }

                        return $valueStr;
                    })
                    ->unique()
                    ->values()
                    ->all();
            };

            if ($type === 'multiple_choice') {
                $correctSet = $q->questionOptions
                    ->filter(fn($opt) => (bool) $opt->is_correct)
                    ->pluck('id')
                    ->map('strval')
                    ->values()
                    ->toArray();
                $selSet = array_values($normalizeSelections($ans));
                sort($correctSet);
                sort($selSet);
                $isCorrect = ($correctSet === $selSet);
                $points = $isCorrect ? 1.0 : 0.0;
                if ($isCorrect) { $correctQuestions++; }
            } elseif ($type === 'true_false') {
                // Support composite TF: answer is map of statement option_id => 'true'|'false'
                $isAssoc = is_array($ans) && (array_keys($ans) !== range(0, count((array)$ans) - 1));
                if ($isAssoc) {
                    $totalRows = max(1, $q->questionOptions->count());
                    $correctMatches = 0;
                    foreach ($q->questionOptions as $opt) {
                        $selRaw = $ans[$opt->id] ?? $ans[(string)$opt->id] ?? null;
                        if ($selRaw === null) { continue; }
                        $selBool = in_array($selRaw, ['true','1',1,true], true);
                        $expected = (bool)$opt->is_correct; // TRUE means statement is true
                        if ($selBool === $expected) { $correctMatches++; }
                    }
                    $points = $correctMatches / $totalRows;
                    if ($points == 1.0) { $correctQuestions++; }
                } else {
                    // Legacy TF with two options
                    $correctSet = $q->questionOptions
                        ->filter(fn($opt) => (bool) $opt->is_correct)
                        ->pluck('id')
                        ->map('strval')
                        ->values()
                        ->toArray();
                    $selSet = array_values($normalizeSelections($ans));
                    sort($correctSet);
                    sort($selSet);
                    $isCorrect = ($correctSet === $selSet);
                    $points = $isCorrect ? 1.0 : 0.0;
                    if ($isCorrect) { $correctQuestions++; }
                }
            } elseif ($type === 'multiple_response') {
                $correctSet = $q->questionOptions
                    ->filter(fn($opt) => (bool) $opt->is_correct)
                    ->pluck('id')
                    ->map('strval')
                    ->values()
                    ->toArray();
                $selSet = array_values($normalizeSelections($ans));
                sort($correctSet);
                sort($selSet);
                $isCorrect = ($correctSet === $selSet);
                $points = $isCorrect ? 1.0 : 0.0;
                if ($isCorrect) { $correctQuestions++; }
            } elseif ($type === 'matching') {
                // Left items hold expected mapping in option_key (e.g., R2)
                $leftItems = $q->questionOptions->filter(fn($o) => str_starts_with($o->option_label, 'L'));
                $totalLeft = max(1, $leftItems->count());
                $correctMatches = 0;
                $ansMap = is_array($ans) ? $ans : [];
                foreach ($leftItems as $left) {
                    $expected = $left->option_key; // e.g., R2
                    $given = $ansMap[$left->option_label] ?? null;
                    $givenStr = is_string($given) ? trim($given) : (string) $given;
                    if ($expected && $givenStr !== '' && (string) $expected === (string) $givenStr) {
                        $correctMatches++;
                    }
                }
                // Normalize to [0,1]
                $points = $correctMatches / $totalLeft;
                if ($points == 1.0) { $correctQuestions++; }
            } elseif ($type === 'tkp') {
                // Use max score among selected options, normalized by 100
                $selIds = collect((array)$ans)->map('intval')->values();
                $selOptions = $q->questionOptions->whereIn('id', $selIds);
                $maxScore = $selOptions->max('score') ?? 0;
                $points = max(0, min(100, (int)$maxScore)) / 100.0; // normalize
                if ($points > 0) { $correctQuestions++; }
            } else {
                // Unknown type: no points
                $points = 0.0;
            }

            $sumPoints += $points;
            $sumMaxPoints += $maxP;
            $details[] = [
                'question_id' => $q->id,
                'type' => $type,
                'points' => round($points, 4),
                'max' => $maxP,
            ];
        }

        $scorePercent = $sumMaxPoints > 0 ? round(($sumPoints / $sumMaxPoints) * 100, 2) : 0;

        // Calculate elapsed duration in seconds (clamped to [0, session_duration*60])
        $durationSeconds = 0;
        if ($attempt->started_at) {
            $durationSeconds = $attempt->started_at->diffInSeconds(now()); // always non-negative
        }
        $sessionCap = optional($attempt->session)->session_duration;
        if (is_numeric($sessionCap)) {
            $capSeconds = max(0, (int)$sessionCap) * 60;
            $durationSeconds = min($durationSeconds, $capSeconds);
        }
        $durationSeconds = max(0, (int)$durationSeconds);

        ExamGrade::updateOrCreate(
            ['exam_attempt_id' => $attempt->id],
            [
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'correct_questions' => $correctQuestions,
                'score' => $scorePercent,
                'duration_seconds' => $durationSeconds,
                'details' => $details,
            ]
        );

        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        toast('Ujian berhasil diselesaikan', 'success');
        return redirect()->route('std.finished', $token);
    }

    public function finished($token)
    {
        $student = Auth::guard('students')->user();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $attempt = ExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->latest('id')
            ->first();

        if (!$attempt || $attempt->status !== 'submitted') {
            return redirect()->route('std.exam', $token);
        }

        $grade = ExamGrade::where('exam_attempt_id', $attempt->id)->first();

        return view('std.finished', [
            'title' => 'Ujian Selesai',
            'exam' => $exam,
            'attempt' => $attempt,
            'grade' => $grade,
            'token' => $token,
        ]);
    }

    /**
     * AJAX: get rendered question HTML by index
     */
    public function getQuestion(Request $request, $token)
    {
        $student = Auth::guard('students')->user();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $attempt = ExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->latest('id')
            ->firstOrFail();
        if ($attempt->status === 'submitted') {
            return response()->json(['redirect' => route('std.finished', $token)], 200);
        }

        $index = max(1, (int) $request->query('q', 1));
        $total = $attempt->questions()->count();
        if ($index > $total) { $index = $total; }

        $attemptQuestion = $attempt->questions()
            ->where('order_index', $index)
            ->firstOrFail();

        $sessionIds = $this->cacheService()->getAttemptSessionIds($attempt->id);
        if (empty($sessionIds) && $attempt->exam_session_id) {
            $sessionIds = [$attempt->exam_session_id];
        }

        $this->cacheService()->ensureSessionQuestionsCached($exam->id, $sessionIds);
        $this->cacheService()->hydrateQuestion($attempt, $attemptQuestion, $sessionIds);
        $this->cacheService()->applyCachedAnswer($attemptQuestion);

        $answeredCount = $this->cacheService()->getAnsweredCount($attempt);

        $html = view('std.question', [
            'attempt' => $attempt,
            'attemptQuestion' => $attemptQuestion,
            'index' => $index,
            'total' => $total,
            'token' => $token,
            'answeredCount' => $answeredCount,
        ])->render();

        return response()->json([
            'html' => $html,
            'index' => $index,
            'total' => $total,
            'answeredCount' => $answeredCount,
        ]);
    }

    /**
     * AJAX: get question statuses (answered/flagged) for modal list
     */
    public function getStatuses($token)
    {
        $student = Auth::guard('students')->user();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $attempt = ExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->latest('id')
            ->firstOrFail();

        return response()->json($this->cacheService()->getStatuses($attempt));
    }
}
