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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'recaptcha' => [
        'secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY'),
        'score_threshold' => env('GOOGLE_RECAPTCHA_SCORE_THRESHOLD', 0.5),
        'verify_url' => env('GOOGLE_RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),
        'timeout' => env('GOOGLE_RECAPTCHA_TIMEOUT', 5),

        // 各呼叫端使用的 reCAPTCHA action 名稱登記表（非機密、不隨環境變動，故不走 env()）。
        // 新增其他表單（如前台玩家登入）時，於此新增一筆，呼叫端以 config('services.recaptcha.actions.xxx') 取用。
        'actions' => [
            'admin_login' => 'admin_login',
        ],
    ],
];
