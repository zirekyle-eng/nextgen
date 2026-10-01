<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

    'moodle' => [
        'url' => env('MOODLE_URL'),
        'token' => env('MOODLE_TOKEN'),
        'category_id' => env('MOODLE_DEFAULT_CATEGORY_ID', 1),
        'teacher_role_id' => env('MOODLE_TEACHER_ROLE_ID', 3),
        'student_role_id' => env('MOODLE_STUDENT_ROLE_ID', 5),
        'file_sync_enabled' => env('MOODLE_FILE_SYNC_ENABLED', false),
        'file_sync_function' => env('MOODLE_FILE_SYNC_FUNCTION', 'local_laravelbridge_create_resource'),
        'assignment_sync_function' => env('MOODLE_ASSIGNMENT_SYNC_FUNCTION', 'local_laravelbridge_create_assignment'),
        'assignment_supports_dates' => env('MOODLE_ASSIGNMENT_SUPPORTS_DATES', false),
        'quiz_sync_function' => env('MOODLE_QUIZ_SYNC_FUNCTION', 'local_laravelbridge_create_quiz'),
        'quiz_supports_dates' => env('MOODLE_QUIZ_SUPPORTS_DATES', false),
        'question_bank_sync_function' => env('MOODLE_QUESTION_BANK_SYNC_FUNCTION', 'local_laravelbridge_import_question_bank'),
        'section_sync_function' => env('MOODLE_SECTION_SYNC_FUNCTION', ''),
        'label_sync_function' => env('MOODLE_LABEL_SYNC_FUNCTION', ''),
        'module_move_function' => env('MOODLE_MODULE_MOVE_FUNCTION', 'core_course_move_module'),
        'module_delete_function' => env('MOODLE_MODULE_DELETE_FUNCTION', 'local_laravelbridge_delete_module'),
        'module_delete_fallback_function' => env('MOODLE_MODULE_DELETE_FALLBACK_FUNCTION', 'core_course_delete_modules'),
        'fallback_to_resource' => env('MOODLE_ACTIVITY_FALLBACK_TO_RESOURCE', false),
        'ssl_verify' => env('MOODLE_SSL_VERIFY', true),
        'ssl_version' => env('MOODLE_SSL_VERSION', ''),
        'ssl_ciphers' => env('MOODLE_SSL_CIPHERS', ''),
        'http_version' => env('MOODLE_HTTP_VERSION', ''),
        'ip_resolve' => env('MOODLE_IP_RESOLVE', ''),
    ],

    'sso' => [
        'shared_secret' => env('SSO_SHARED_SECRET'),
        'issue_api_key' => env('SSO_ISSUE_API_KEY'),
        'token_ttl_seconds' => env('SSO_TOKEN_TTL_SECONDS', 60),
        'clock_skew_seconds' => env('SSO_CLOCK_SKEW_SECONDS', 60),
        'default_redirect' => env('SSO_DEFAULT_REDIRECT', '/dashboard'),
    ],

    'openrouter' => [
        'model' => env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
    ],

    'poppler' => [
        'bin_path' => env('POPPLER_BIN_PATH', ''),
    ],

];
