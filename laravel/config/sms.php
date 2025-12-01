<?php

return [
    // 'sender' => env('SMS_SENDER'),

    'hubtel' => [
        'client_id' => env('HUBTEL_CLIENT_ID'),
        'client_secret' => env('HUBTEL_CLIENT_SECRET'),
    ],

    'africatalking' => [
        'api_key' => env('AFRICATALKING_API_KEY'),
        'username' => env('AFRICATALKING_USERNAME'),
        'sandbox' => env('AFRICAS_TALKING_SANDBOX', true),
    ],
];
