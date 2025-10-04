<?php

namespace App\Http\Controllers\Std;

use App\Models\Employee;
use App\Models\ExamParticipant;
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
use Illuminate\Support\Collection;

class ExamController extends Controller
{
    private function cacheService(): ExamCacheService
    {
        return app(ExamCacheService::class);
    }

    /**
     * Resolve the currently authenticated participant across supported guards.
     *
     * @return array{guard:string,id:int,name:string|null,gender:?string,class:string,user:object}
     */
    private function resolveParticipant(): array
    {
        $guards = [
            'students' => [
                'class' => Student::class,
                'name_field' => 'student_name',
                'gender_field' => 'student_gender',
            ],
            'employees' => [
                'class' => Employee::class,
                'name_field' => 'employee_name',
                'gender_field' => null,
            ],
        ];

        foreach ($guards as $guard => $meta) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                return [
                    'guard' => $guard,
                    'id' => (int) data_get($user, 'id'),
                    'name' => data_get($user, $meta['name_field']),
                    'gender' => $meta['gender_field'] ? data_get($user, $meta['gender_field']) : null,
                    'class' => $meta['class'],
                    'user' => $user,
                ];
            }
        }

        abort(403, 'Peserta belum terotentikasi');
    }

    private function participantAttemptQuery(Exam $exam, array $participant, ?int $sessionId = null)
    {
        $query = ExamAttempt::where('exam_id', $exam->id)
            ->where('participant_type', $participant['class'])
            ->where('participant_id', $participant['id']);

        if ($sessionId !== null) {
            $query->where('exam_session_id', $sessionId);
        }

        return $query;
    }

    /**
     * Cek Data Peserta dan Token
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $participant = $this->resolveParticipant();

        $sessionQuery = ExamSession::with(['exam', 'subject'])
            ->orderBy('session_start_time');

        $activeSessions = collect();
        $availableSessions = collect();

        $examIds = $this->participantExamIds($participant);

        if ($examIds->isNotEmpty()) {
            $sessionQuery->whereIn('exam_id', $examIds);
            $activeSessions = (clone $sessionQuery)->where('session_status', 'Active')->get();
            $availableSessions = $activeSessions->isNotEmpty()
                ? $activeSessions
                : $sessionQuery->get();
        }

        // Check for ongoing in_progress attempt
        $ongoingAttempt = ExamAttempt::with(['exam', 'session.subject'])
            ->where('participant_type', $participant['class'])
            ->where('participant_id', $participant['id'])
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        return view('std.confirmation', [
            'title' => 'Exam Confirmation',
            'sessions' => $availableSessions,
            'hasActiveSessions' => $activeSessions->isNotEmpty(),
            'participant' => $participant,
            'participantLabel' => $participant['guard'] === 'students' ? 'Siswa' : 'Guru/Staff',
            'ongoingAttempt' => $ongoingAttempt,
        ]);
    }

    /**
     * Konfirmasi Token dan Data Peserta
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function checkToken(Request $request)
    {
        $participant = $this->resolveParticipant();

        $validated = $request->validate([
            'confirm_participant_name' => 'required|string',
            'exam_token' => 'required|string',
            'exam_session_id' => 'required|integer|exists:exam_sessions,id',
        ]);

        $session = ExamSession::with('exam')->findOrFail($validated['exam_session_id']);
        $exam = Exam::where('exam_code', $validated['exam_token'])->first();

        if (!$exam || $exam->id !== $session->exam_id) {
            toast('Token tidak ditemukan', 'error');
            return back();
        }

        if (!$this->isParticipantRegistered($exam->id, $participant)) {
            toast('Anda belum terdaftar sebagai peserta ujian ini', 'error');
            return back();
        }

        $inputName = trim((string) $validated['confirm_participant_name']);
        $actualName = trim((string) ($participant['name'] ?? ''));
        if (strcasecmp($inputName, $actualName) !== 0) {
            toast('Nama peserta tidak sesuai', 'error');
            return back();
        }

        return redirect()->route('std.exam', [
            'token' => $exam->exam_code,
            'session' => $session->id,
        ]);
    }

    /**
     * Tampilkan Soal
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function showExam($token, Request $request)
    {
        $participant = $this->resolveParticipant();
        $exam = Exam::where('exam_code', $token)->firstOrFail();

        if (!$this->isParticipantRegistered($exam->id, $participant)) {
            toast('Anda belum terdaftar pada ujian ini', 'error');
            return redirect()->route('std.confirmation');
        }

        $requestedSessionId = (int) $request->query('session', 0);
        $session = null;

        if ($requestedSessionId > 0) {
            $session = ExamSession::with(['subject.questions.questionOptions'])
                ->where('exam_id', $exam->id)
                ->find($requestedSessionId);

            if (!$session) {
                toast('Sesi ujian tidak ditemukan untuk token ini', 'error');
                return redirect()->route('std.confirmation');
            }
        }

        if (!$session) {
            $existingAttempt = $this->participantAttemptQuery($exam, $participant)->latest('id')->first();
            if ($existingAttempt) {
                $session = ExamSession::with(['subject.questions.questionOptions'])
                    ->find($existingAttempt->exam_session_id);
            }
        }

        if (!$session) {
            $session = ExamSession::with(['subject.questions.questionOptions'])
                ->where('exam_id', $exam->id)
                ->where('session_status', 'Active')
                ->orderBy('session_start_time')
                ->first();
        }

        if (!$session) {
            $session = ExamSession::with(['subject.questions.questionOptions'])
                ->where('exam_id', $exam->id)
                ->orderBy('session_start_time')
                ->first();
        }

        if (!$session) {
            toast('Belum ada sesi untuk ujian ini', 'error');
            return redirect()->route('std.confirmation');
        }

        $session->loadMissing(['subject.questions.questionOptions']);

        $attempt = ExamAttempt::firstOrCreate(
            [
                'participant_type' => $participant['class'],
                'participant_id' => $participant['id'],
                'exam_id' => $exam->id,
                'exam_session_id' => $session->id,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );

        if ($attempt->status === 'submitted') {
            return redirect()->route('std.finished', [
                'token' => $token,
                'session' => $session->id,
            ]);
        }

        if ($attempt->questions()->count() === 0) {
            $questions = optional($session->subject)->questions ?? collect();

            if ($session->random_question === 'Y') {
                $questions = $questions->shuffle();
            }

            DB::transaction(function () use ($attempt, $questions, $session) {
                foreach ($questions as $idx => $question) {
                    $options = $question->questionOptions;
                    if ($session->random_answer === 'Y') {
                        $options = $options->shuffle();
                    }

                    ExamAttemptQuestion::create([
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'order_index' => $idx + 1,
                        'options_order' => $options->pluck('id')->values()->toArray(),
                    ]);
                }
            });
        }

        if ($attempt->questions()->count() === 0) {
            toast('Belum ada soal untuk sesi yang dipilih', 'error');
            return redirect()->route('std.confirmation');
        }

        $sessionIds = [$session->id];
        $this->cacheService()->storeAttemptSessionIds($attempt->id, $sessionIds);
        $this->cacheService()->ensureSessionQuestionsCached($exam->id, $sessionIds);

        $index = max(1, (int) $request->query('q', 1));
        $total = $attempt->questions()->count();
        if ($index > $total) {
            $index = $total;
        }

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
            'session' => $session,
            'attempt' => $attempt,
            'attemptQuestion' => $attemptQuestion,
            'index' => $index,
            'total' => $total,
            'token' => $token,
            'answeredCount' => $answeredCount,
            'questionStatuses' => $statuses,
            'participant' => $participant,
            'participantLabel' => $participant['guard'] === 'students' ? 'Siswa' : 'Guru/Staff',
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

        $participant = $this->resolveParticipant();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $sessionId = (int) $request->query('session', 0);
        if ($sessionId <= 0) {
            abort(404, 'Sesi ujian tidak ditemukan');
        }

        $attempt = $this->participantAttemptQuery($exam, $participant, $sessionId)
            ->where('status', 'in_progress')
            ->latest('id')
            ->firstOrFail();

        if ($attempt->status === 'submitted') {
            return redirect()->route('std.finished', [
                'token' => $token,
                'session' => $attempt->exam_session_id,
            ]);
        }

        $index = (int) $request->input('index');
        $total = $attempt->questions()->count();
        if ($index < 1) { $index = 1; }
        if ($index > $total) { $index = $total; }

        $attemptQuestion = $attempt->questions()
            ->where('order_index', $index)
            ->firstOrFail();

        $sessionIds = [$attempt->exam_session_id];
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
            return redirect()->route('std.exam', [
                'token' => $token,
                'session' => $attempt->exam_session_id,
                'q' => $index,
            ]);
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
        return redirect()->route('std.exam', [
            'token' => $token,
            'session' => $attempt->exam_session_id,
            'q' => $nextIndex,
        ]);
    }

    /**
     * Finalize the attempt when all questions answered
     */
    public function finish(Request $request, $token)
    {
        $participant = $this->resolveParticipant();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $sessionId = (int) $request->query('session', 0);
        if ($sessionId <= 0) {
            abort(404, 'Sesi ujian tidak ditemukan');
        }

        $attempt = $this->participantAttemptQuery($exam, $participant, $sessionId)
            ->latest('id')
            ->firstOrFail();

        if ($attempt->status === 'submitted') {
            return redirect()->route('std.finished', [
                'token' => $token,
                'session' => $attempt->exam_session_id,
            ]);
        }

        $statuses = $this->cacheService()->getStatuses($attempt);
        $firstUnanswered = collect($statuses)->firstWhere('answered', false);
        if ($firstUnanswered && !$request->boolean('force')) {
            toast('Masih ada soal belum terjawab', 'error');
            return redirect()->route('std.exam', [
                'token' => $token,
                'session' => $attempt->exam_session_id,
                'q' => $firstUnanswered['order_index'],
            ]);
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
        return redirect()->route('std.finished', [
            'token' => $token,
            'session' => $attempt->exam_session_id,
        ]);
    }

    public function finished($token, Request $request)
    {
        $participant = $this->resolveParticipant();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $sessionId = (int) $request->query('session', 0);

        $attemptQuery = $this->participantAttemptQuery($exam, $participant, $sessionId > 0 ? $sessionId : null)
            ->latest('id');

        $attempt = $attemptQuery->first();

        if (!$attempt || $attempt->status !== 'submitted') {
            if ($attempt) {
                return redirect()->route('std.exam', [
                    'token' => $token,
                    'session' => $attempt->exam_session_id,
                ]);
            }

            return redirect()->route('std.confirmation');
        }

        $attempt->loadMissing('session.subject');

        $grade = ExamGrade::where('exam_attempt_id', $attempt->id)->first();

        return view('std.finished', [
            'title' => 'Ujian Selesai',
            'exam' => $exam,
            'attempt' => $attempt,
            'grade' => $grade,
            'token' => $token,
            'sessionId' => $attempt->exam_session_id,
            'participantLabel' => $participant['guard'] === 'students' ? 'Siswa' : 'Guru/Staff',
        ]);
    }

    /**
     * AJAX: get rendered question HTML by index
     */
    public function getQuestion(Request $request, $token)
    {
        $participant = $this->resolveParticipant();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $sessionId = (int) $request->query('session', 0);
        if ($sessionId <= 0) {
            abort(404, 'Sesi ujian tidak ditemukan');
        }

        $attempt = $this->participantAttemptQuery($exam, $participant, $sessionId)
            ->latest('id')
            ->firstOrFail();

        if ($attempt->status === 'submitted') {
            return response()->json([
                'redirect' => route('std.finished', [
                    'token' => $token,
                    'session' => $attempt->exam_session_id,
                ]),
            ], 200);
        }

        $index = max(1, (int) $request->query('q', 1));
        $total = $attempt->questions()->count();
        if ($index > $total) { $index = $total; }

        $attemptQuestion = $attempt->questions()
            ->where('order_index', $index)
            ->firstOrFail();

        $sessionIds = [$attempt->exam_session_id];
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
            'sessionId' => $attempt->exam_session_id,
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
    public function getStatuses($token, Request $request)
    {
        $participant = $this->resolveParticipant();
        $exam = Exam::where('exam_code', $token)->firstOrFail();
        $sessionId = (int) $request->query('session', 0);
        if ($sessionId <= 0) {
            abort(404, 'Sesi ujian tidak ditemukan');
        }

        $attempt = $this->participantAttemptQuery($exam, $participant, $sessionId)
            ->latest('id')
            ->firstOrFail();

        return response()->json($this->cacheService()->getStatuses($attempt));
    }

    private function participantExamIds(array $participant): Collection
    {
        return ExamParticipant::where('participant_type', $participant['class'])
            ->where('participant_id', $participant['id'])
            ->pluck('exam_id');
    }

    private function isParticipantRegistered(int $examId, array $participant): bool
    {
        return ExamParticipant::where('exam_id', $examId)
            ->where('participant_type', $participant['class'])
            ->where('participant_id', $participant['id'])
            ->exists();
    }

    /**
     * Reset ongoing attempt if not submitted
     */
    public function resetAttempt($attemptId)
    {
        $participant = $this->resolveParticipant();

        $attempt = ExamAttempt::where('id', $attemptId)
            ->where('participant_type', $participant['class'])
            ->where('participant_id', $participant['id'])
            ->where('status', 'in_progress')
            ->firstOrFail();

        // Delete the attempt and its questions
        $attempt->questions()->delete();
        $attempt->delete();

        toast('Ujian berhasil direset. Anda dapat memulai ulang.', 'success');
        return redirect()->route('std.confirmation');
    }
}
