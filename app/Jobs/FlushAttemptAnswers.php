<?php

namespace App\Jobs;

use App\Models\ExamAttempt;
use App\Services\ExamCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlushAttemptAnswers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $attemptId)
    {
        $this->queue = 'default';
    }

    public function handle(ExamCacheService $cacheService): void
    {
        $attempt = ExamAttempt::find($this->attemptId);

        if (!$attempt) {
            $cacheService->clearScheduledFlush($this->attemptId);
            return;
        }

        $cacheService->flushAnswers($attempt);
        $cacheService->clearScheduledFlush($this->attemptId);
    }
}
