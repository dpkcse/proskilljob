<?php

return [
    'secret' => env('NOCAPTCHA_SECRET', ''),
    'sitekey' => env('NOCAPTCHA_SITEKEY', ''),
    'active' => filter_var(env('NOCAPTCHA_ACTIVE', false), FILTER_VALIDATE_BOOL),
    'options' => [
        'timeout' => 30,
    ],
];
