<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'firebase' => [
        'database_url' => env('FIREBASE_DATABASE_URL', 'https://eventintel-72be0-default-rtdb.firebaseio.com'),
        'api_key' => env('FIREBASE_API_KEY', ''),
        'project_id' => env('FIREBASE_PROJECT_ID', 'eventintel-72be0'),
    ],

    'gcash' => [
        'enabled' => env('GCASH_ENABLED', false),
        'api_key' => env('GCASH_API_KEY', ''),
        'merchant_id' => env('GCASH_MERCHANT_ID', ''),
        'base_url' => env('GCASH_BASE_URL', 'https://api.xendit.co'),
        'webhook_secret' => env('GCASH_WEBHOOK_SECRET', ''),
    ],

    'paymongo' => [
        'enabled' => env('PAYMONGO_ENABLED', false),
        'secret_key' => env('PAYMONGO_SECRET_KEY', ''),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET', ''),
    ],

];
