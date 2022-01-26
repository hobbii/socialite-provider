<?php

use Illuminate\Support\Str;

return [
    'settings' => [
        'host' => env('HOBBII_LOGIN_SERVICE'),
        'client_id' => env('HOBBII_CLIENT_ID'),
        'client_secret' => env('HOBBII_CLIENT_SECRET'),
        'redirect' => Str::finish(env('APP_URL', ''), '/') . 'auth/callback',
        'logout' => Str::finish(env('HOBBII_LOGIN_SERVICE', ''), '/') . 'logout',
    ],
];
