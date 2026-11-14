<?php

/*
 * Barnaby the worm, Bookworm's reading assistant.
 *
 * Keys live only in .env and are only ever used on the server.
 * Providers are tried in order: if the first is out of free quota or down,
 * the next one answers.
 */
return [
    'providers' => array_values(array_filter(array_map('trim', explode(',', env('BARNABY_PROVIDERS', 'gemini,groq'))))),

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
        'url' => env('GEMINI_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'url' => env('GROQ_URL', 'https://api.groq.com/openai/v1'),
    ],

    // Free tiers are small: keep any one reader from using them all up.
    'per_minute' => (int) env('BARNABY_PER_MINUTE', 6),
    'per_day' => (int) env('BARNABY_PER_DAY', 40),

    'timeout' => (int) env('BARNABY_TIMEOUT', 25),
];
