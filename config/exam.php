<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exam Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for exam caching, including TTL settings
    | for answers and questions in Redis or other cache stores.
    |
    */

    'cache' => [
        'answer_ttl' => env('EXAM_CACHE_ANSWER_TTL', 14400), // 4 hours in seconds
        'questions_ttl' => env('EXAM_CACHE_QUESTIONS_TTL', 14400), // 4 hours in seconds
    ],
];
