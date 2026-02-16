<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are fallback values. Actual values are stored in the database
    | settings table and managed via the Super Admin settings page.
    |
    */

    'default_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'default_temperature' => env('OPENAI_TEMPERATURE', 0.3),
    'default_max_tokens' => env('OPENAI_MAX_TOKENS', 1000),
    'default_daily_limit' => env('OPENAI_DAILY_LIMIT', 100),
    'timeout' => env('OPENAI_TIMEOUT', 60),
];
