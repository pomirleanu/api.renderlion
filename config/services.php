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

    'diffbot' => [
        'token' => env('DIFFBOT_API_TOKEN'),
    ],
    'openai' => [
        'assistant_id' => env('OPENAI_ASSISTANT_ID'),
        'assistant_id_type' => env('OPENAI_ASSISTANT_ID_TYPE'),
        'assistant_id_product' => env('OPENAI_ASSISTANT_ID_PRODUCT'),
        'assistant_id_products' => env('OPENAI_ASSISTANT_ID_PRODUCTS'),
        'assistant_id_list' => env('OPENAI_ASSISTANT_ID_LIST'),
        'assistant_id_article' => env('OPENAI_ASSISTANT_ID_ARTICLE'),
        'assistant_id_florin' => env('OPENAI_ASSISTANT_ID_FLORIN'),
    ],

];
