<?php

namespace App\Services;

use App\Jobs\FlushAttemptAnswers;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Redis\Connection as RedisConnection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ExamCacheService
{
    private CacheRepository $cache;
    private string $redisConnection;
    private int $answerTtl;
    private int $questionTtl;
    /** @var array<string,array<int,array>> */
    private array $runtimeQuestionCache = [];
    private string $prefix;
    /**
     * @var array<int,array{
     *     ordered: array<int,array{id:int,order_index:int,flagged:bool,answer:?string}>,
     *     map: array<int,array{id:int,order_index:int,flagged:bool,answer:?string}>
     * }>
     */
    private array $attemptQuestionMetaCache = [];

    public function __construct()
    {
        $storeName = config('cache.default', 'redis');
        $this->cache = Cache::store($storeName);

        $this->redisConnection = config("cache.stores.{$storeName}.connection")
            ?? config('cache.stores.redis.connection', 'default');

        $this->answerTtl = (int) config('exam.cache.answer_ttl', 7200); // 2 jam
        $this->questionTtl = (int) config('exam.cache.questions_ttl', 1800); // 30 menit

        $prefix = config('database.redis.options.prefix');
        if ($prefix === null) {
            $prefix = Str::slug((string) config('app.name', 'laravel')).'_database_';
        }
        $this->prefix = (string) $prefix;
    }

    public function hydrateQuestion(
        ExamAttempt $attempt,
        ExamAttemptQuestion $attemptQuestion,
        array $sessionIds = []
    ): ExamAttemptQuestion {
        if (empty($sessionIds)) {
            $sessionIds = $this->getAttemptSessionIds($attempt->id);
        }

        if (empty($sessionIds) && $attempt->exam_session_id) {
            $sessionIds = [$attempt->exam_session_id];
        }

        $map = $this->getSessionQuestionMap($attempt->exam_id, $sessionIds);
        $data = $map[$attemptQuestion->question_id] ?? null;

        if ($data === null) {
            $map = $this->primeQuestionsCache($attempt->exam_id, $sessionIds);
            $data = $map[$attemptQuestion->question_id] ?? null;
            if ($data === null) {
                $attemptQuestion->load('question.questionOptions');
                return $attemptQuestion;
            }
        }

        $questionModel = new Question();
        $questionModel->forceFill($data['question']);
        $questionModel->exists = true;

        $options = collect($data['options'] ?? [])->map(function (array $attrs) {
            $option = new QuestionOption();
            $option->forceFill($attrs);
            $option->exists = true;
            return $option;
        })->values();

        $questionModel->setRelation('questionOptions', $options);
        $attemptQuestion->setRelation('question', $questionModel);

        return $attemptQuestion;
    }

    public function applyCachedAnswer(ExamAttemptQuestion $attemptQuestion): ExamAttemptQuestion
    {
        $data = $this->getCachedAnswerData($attemptQuestion->exam_attempt_id, $attemptQuestion->id);

        if ($data === null) {
            if ($attemptQuestion->answer !== null) {
                $this->storeAnswerSnapshot($attemptQuestion, false);
            }
            return $attemptQuestion;
        }

        $attemptQuestion->answer = $data['answer'] ?? $attemptQuestion->answer;
        $attemptQuestion->flagged = (bool) ($data['flagged'] ?? $attemptQuestion->flagged);
        $attemptQuestion->answered_at = !empty($data['answered_at'])
            ? Carbon::parse($data['answered_at'])
            : null;

        return $attemptQuestion;
    }

    public function storeAnswer(
        ExamAttemptQuestion $attemptQuestion,
        ?string $answerJson,
        bool $flagged,
        ?Carbon $answeredAt,
        bool $markDirty = true
    ): void {
        $attemptId = $attemptQuestion->exam_attempt_id;
        $questionId = $attemptQuestion->id;

        $current = $this->getCachedAnswerData($attemptId, $questionId);
        $shouldPersist = $markDirty || (!empty($current['dirty']));

        if (!$shouldPersist && $current !== null) {
            $currentAnswer = $current['answer'] ?? null;
            $currentFlagged = (bool) ($current['flagged'] ?? $attemptQuestion->flagged);
            $currentAnsweredAt = $current['answered_at'] ?? null;
            $targetAnsweredAt = $answeredAt?->toIso8601String();

            if ($currentAnswer === $answerJson && $currentFlagged === $flagged && $currentAnsweredAt === $targetAnsweredAt) {
                return;
            }
        }

        $payload = [
            'answer' => $answerJson,
            'flagged' => $flagged,
            'answered_at' => $answeredAt?->toIso8601String(),
            'dirty' => $shouldPersist,
            'updated_at' => now()->toIso8601String(),
        ];

        $this->writeAnswerPayload($attemptId, $questionId, $payload);

        $attemptQuestion->answer = $answerJson;
        $attemptQuestion->flagged = $flagged;
        $attemptQuestion->answered_at = $answeredAt;

        $this->updateAttemptMetaCache($attemptId, $questionId, [
            'flagged' => $flagged,
            'answer' => $answerJson,
        ]);
    }

    public function storeAttemptSessionIds(int $attemptId, array $sessionIds): void
    {
        $sessionIds = array_values(array_unique(array_map('intval', $sessionIds)));
        sort($sessionIds);

        $key = $this->attemptSessionsKey($attemptId);
        $payload = json_encode($sessionIds);
        $ttl = max($this->answerTtl, $this->questionTtl);

        $this->redis()->setex($key, $ttl, $payload);
    }

    public function getAttemptSessionIds(int $attemptId): array
    {
        $raw = $this->redis()->get($this->attemptSessionsKey($attemptId));
        $decoded = $this->decodePayload($raw);
        return is_array($decoded) ? array_map('intval', $decoded) : [];
    }

    public function ensureSessionQuestionsCached(int $examId, array $sessionIds): void
    {
        $this->getSessionQuestionMap($examId, $sessionIds);
    }

    public function getStatuses(ExamAttempt $attempt): array
    {
        $meta = $this->getAttemptMeta($attempt->id);

        return array_map(function (array $item) {
            return [
                'order_index' => $item['order_index'],
                'flagged' => (bool) $item['flagged'],
                'answered' => $this->isAnswered($item['answer']),
            ];
        }, $meta['ordered']);
    }

    public function getAnsweredCount(ExamAttempt $attempt): int
    {
        $meta = $this->getAttemptMeta($attempt->id);

        $count = 0;
        foreach ($meta['ordered'] as $item) {
            if ($this->isAnswered($item['answer'])) {
                $count++;
            }
        }

        return $count;
    }

    public function flushAnswers(ExamAttempt $attempt): void
    {
        $lock = $this->cache->lock($this->flushLockKey($attempt->id), 10);

        if (!$lock->get()) {
            return;
        }

        try {
            $payloads = $this->getAllCachedAnswerData($attempt->id);
            if (empty($payloads)) {
                return;
            }

            $dirtyIds = array_keys(array_filter($payloads, fn($d) => !empty($d['dirty'])));
            if (empty($dirtyIds)) {
                return;
            }

            $questions = $attempt->questions()->whereIn('id', $dirtyIds)->get();

            foreach ($questions as $question) {
                $data = $payloads[$question->id] ?? null;
                if ($data === null) {
                    continue;
                }

                $attributes = [
                    'flagged' => $data['flagged'] ?? $question->flagged,
                    'answer' => $data['answer'] ?? $question->answer,
                    'answered_at' => !empty($data['answered_at'])
                        ? Carbon::parse($data['answered_at'])
                        : null,
                ];

                $question->forceFill($attributes);
                $question->save();

                $data['dirty'] = false;
                $data['updated_at'] = now()->toIso8601String();
                $this->writeAnswerPayload($attempt->id, $question->id, $data);
                $this->updateAttemptMetaCache($attempt->id, $question->id, [
                    'flagged' => (bool) $attributes['flagged'],
                    'answer' => $attributes['answer'],
                ]);
            }
        } finally {
            $lock->release();
        }
    }

    public function scheduleFlush(ExamAttempt $attempt): void
    {
        $key = $this->flushScheduleKey($attempt->id);
        $redis = $this->redis();

        if ($redis->setnx($key, now()->toIso8601String())) {
            $redis->expire($key, 30);
            FlushAttemptAnswers::dispatch($attempt->id)->delay(now()->addSeconds(15));
        }
    }

    public function clearScheduledFlush(int $attemptId): void
    {
        $this->redis()->del($this->flushScheduleKey($attemptId));
    }

    public function storeAnswerSnapshot(ExamAttemptQuestion $attemptQuestion, bool $markDirty = false): void
    {
        $payload = [
            'answer' => $attemptQuestion->answer,
            'flagged' => (bool) $attemptQuestion->flagged,
            'answered_at' => optional($attemptQuestion->answered_at)?->toIso8601String(),
            'dirty' => $markDirty,
            'updated_at' => now()->toIso8601String(),
        ];

        $this->writeAnswerPayload($attemptQuestion->exam_attempt_id, $attemptQuestion->id, $payload);
    }

    public function getCachedAnswerData(int $attemptId, int $attemptQuestionId): ?array
    {
        $payload = $this->redis()->hget($this->answersKey($attemptId), (string) $attemptQuestionId);
        return $this->decodePayload($payload);
    }

    private function getSessionQuestionMap(int $examId, array $sessionIds): array
    {
        $runtimeKey = $this->runtimeCacheKey($examId, $sessionIds);
        if (isset($this->runtimeQuestionCache[$runtimeKey])) {
            return $this->runtimeQuestionCache[$runtimeKey];
        }

        $key = $this->questionsKey($examId, $sessionIds);
        $payload = $this->redis()->get($key);

        if ($payload !== null) {
            $decoded = $this->decodePayload($payload);
            if (is_array($decoded)) {
                return $this->runtimeQuestionCache[$runtimeKey] = $decoded;
            }
        }

        return $this->runtimeQuestionCache[$runtimeKey] = $this->primeQuestionsCache($examId, $sessionIds);
    }

    private function primeQuestionsCache(int $examId, array $sessionIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $sessionIds)));
        sort($ids);

        $query = ExamSession::with(['subject.questions.questionOptions' => function ($q) {
            $q->orderBy('id');
        }])->where('exam_id', $examId);

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $sessions = $query->get();
        if ($sessions->isEmpty()) {
            return [];
        }

        $map = [];
        foreach ($sessions as $session) {
            $subject = $session->subject;
            if (!$subject) {
                continue;
            }

            foreach ($subject->questions as $question) {
                if (!isset($map[$question->id])) {
                    $map[$question->id] = [
                        'question' => $this->normalizeQuestion($question),
                        'options' => [],
                    ];
                }

                if (empty($map[$question->id]['options'])) {
                    $map[$question->id]['options'] = $question->questionOptions->map(function ($option) {
                        return $this->normalizeOption($option);
                    })->values()->all();
                }
            }
        }

        $key = $this->questionsKey($examId, $sessionIds);
        $this->redis()->setex($key, $this->questionTtl, json_encode($map, JSON_UNESCAPED_UNICODE));

        $runtimeKey = $this->runtimeCacheKey($examId, $sessionIds);
        $this->runtimeQuestionCache[$runtimeKey] = $map;

        return $map;
    }

    private function normalizeQuestion(Question $question): array
    {
        return Arr::except($question->getAttributes(), ['created_at', 'updated_at', 'deleted_at']);
    }

    private function normalizeOption(QuestionOption $option): array
    {
        return Arr::except($option->getAttributes(), ['created_at', 'updated_at', 'deleted_at']);
    }

    private function getAllCachedAnswerData(int $attemptId): array
    {
        $raw = $this->redis()->hgetall($this->answersKey($attemptId));
        $map = [];
        foreach ($raw as $field => $payload) {
            $decoded = $this->decodePayload($payload);
            if ($decoded !== null) {
                $map[(int) $field] = $decoded;
            }
        }

        return $map;
    }

    private function writeAnswerPayload(int $attemptId, int $attemptQuestionId, array $payload): void
    {
        $key = $this->answersKey($attemptId);
        $this->redis()->hset($key, (string) $attemptQuestionId, json_encode($payload));
        $this->redis()->expire($key, $this->answerTtl);
    }

    private function decodePayload(mixed $payload): ?array
    {
        if ($payload === null || $payload === false) {
            return null;
        }

        try {
            $decoded = json_decode((string) $payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function isAnswered(?string $value): bool
    {
        if ($value === null || $value === '' || trim($value) === '[]' || trim($value) === '{}') {
            return false;
        }
        return true;
    }

    private function questionsKey(int $examId, array $sessionIds): string
    {
        $sessionIds = array_values(array_unique(array_map('intval', $sessionIds)));
        sort($sessionIds);
        $sessionPart = empty($sessionIds) ? 'all' : implode('-', $sessionIds);

        return "{$this->prefix}exam:{$examId}:sessions:{$sessionPart}:questions";
    }

    private function runtimeCacheKey(int $examId, array $sessionIds): string
    {
        $sessionIds = array_values(array_unique(array_map('intval', $sessionIds)));
        sort($sessionIds);
        $sessionPart = empty($sessionIds) ? 'all' : implode('-', $sessionIds);

        return $examId.'|'.$sessionPart;
    }

    private function getAttemptMeta(int $attemptId): array
    {
        if (!isset($this->attemptQuestionMetaCache[$attemptId])) {
            $rows = ExamAttemptQuestion::query()
                ->where('exam_attempt_id', $attemptId)
                ->orderBy('order_index')
                ->get(['id', 'order_index', 'flagged', 'answer']);

            $ordered = [];
            $map = [];
            foreach ($rows as $row) {
                $ordered[] = [
                    'id' => (int) $row->id,
                    'order_index' => (int) $row->order_index,
                    'flagged' => (bool) $row->flagged,
                    'answer' => $row->answer,
                ];
                $index = array_key_last($ordered);
                $map[$row->id] = &$ordered[$index];
            }

            // Overlay latest data from Redis cache
            $cacheMap = $this->getAllCachedAnswerData($attemptId);
            foreach ($cacheMap as $id => $payload) {
                if (!isset($map[$id])) {
                    continue;
                }
                if (array_key_exists('flagged', $payload)) {
                    $map[$id]['flagged'] = (bool) $payload['flagged'];
                }
                if (array_key_exists('answer', $payload)) {
                    $map[$id]['answer'] = $payload['answer'];
                }
            }

            $this->attemptQuestionMetaCache[$attemptId] = [
                'ordered' => $ordered,
                'map' => $map,
            ];
        }

        return $this->attemptQuestionMetaCache[$attemptId];
    }

    private function updateAttemptMetaCache(int $attemptId, int $questionId, array $attributes): void
    {
        if (!isset($this->attemptQuestionMetaCache[$attemptId]['map'][$questionId])) {
            return;
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'flagged') {
                $value = (bool) $value;
            }
            $this->attemptQuestionMetaCache[$attemptId]['map'][$questionId][$key] = $value;
        }
    }

    private function attemptSessionsKey(int $attemptId): string
    {
        return "{$this->prefix}exam:attempt:{$attemptId}:session-ids";
    }

    private function answersKey(int $attemptId): string
    {
        return "{$this->prefix}exam:attempt:{$attemptId}:answers";
    }

    private function flushLockKey(int $attemptId): string
    {
        return "{$this->prefix}exam:attempt:{$attemptId}:flush-lock";
    }

    private function flushScheduleKey(int $attemptId): string
    {
        return "{$this->prefix}exam:attempt:{$attemptId}:flush-scheduled";
    }

    private function redis(): RedisConnection
    {
        return Redis::connection($this->redisConnection);
    }
}
