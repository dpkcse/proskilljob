<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Global brand defaults
    |--------------------------------------------------------------------------
    |
    | The administrator may change APP_NAME through the existing general
    | settings screen. These values provide safe global defaults for fresh
    | installations and non-HTTP contexts such as mail and PWA generation.
    |
    */
    'product_name' => env('PRODUCT_NAME', env('APP_NAME', 'NAXAS')),
    'company_name' => env('COMPANY_NAME', 'NAXAS AI'),
    'product_attribution' => env('PRODUCT_ATTRIBUTION', 'A Product of NAXAS AI'),
];
