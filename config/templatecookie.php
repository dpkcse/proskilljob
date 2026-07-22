<?php

/*
 * This file is part of the Laravel Rave package.
 *
 * (c) templatecookie.com - Zakir Hossen <templatecookie@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    'default_language' => env('APP_DEFAULT_LANGUAGE'),
    'timezone' => env('APP_TIMEZONE'),
    'currency' => env('APP_CURRENCY', 'USD'),
    'currency_symbol' => env('APP_CURRENCY_SYMBOL', '$'),
    'currency_symbol_position' => env('APP_CURRENCY_SYMBOL_POSITION', 'left'),

    'set_ip_based_location' => false,

    'paypal_sandbox_client_id' => 'AQf5e_YjNNqTlpbn6Xtpw_PfIeQTcTnOSZF72ZpRz8xfh1vp5aMtfRuqJq-3DkE2TFWt873wGRPYBOTb',
    'paypal_sandbox_secret' => 'EPvipNvQnf03CRjnv_gljFFSIXq8RMPBcszao0JarXIiKGa0ekPkXwGHJDV6SyJ89crJSA8EnVZKHzxU',
    'paypal_live_client_id' => 'AT3P4kyT_5kQ7cekb4z4JRl9wwjhYJpDEYP_8_AX9zZ3h16h-l1pe5n5rv6WdODNOk_n4Kbn6WHz9eRB',
    'paypal_live_secret' => 'EHTOE0nCzLn_DtiNESl6onVjhpTh44BoGyCkafDDnhFVorb5XXRO8QQLBxHq8GCKRZzxCW2EP-2cleSJ',
    'paypal_mode' => 'sandbox',
    'paypal_active' => true,

    'stripe_key' => 'pk_test_51JAbnoDHsbz9CBNMjbDtUrA8pfBWkC9yvXqzFQYHeEJokRKFvpAedEruhqCxJhzqOflDi0KH1E020J5kitkMWV4q00fl2LBk6p',
    'stripe_secret' => 'sk_test_51JAbnoDHsbz9CBNM3FjZDwFH9rC3sr8q06vu9dDS0cjzY0o7a0VnC5KbcED1YUAEcryuro0xkDUKq8rKqVi1R9SX00idI7OL7i',
    'stripe_active' => true,

    'razorpay_key' => 'rzp_test_V7zKmP7nti57DU',
    'razorpay_secret' => '3cbGRKMlwixOtuLXXdaHkrfn',
    'razorpay_active' => true,

    'paystack_key' => 'sk_test_13cb7967851092da5996bab4cfe4f834e5795895',
    'paystack_secret' => 'pk_test_934e46b979e2f764fe7e0fd411cb07c1771ad200',
    'paystack_payment_url' => 'https://api.paystack.co',
    'paystack_merchant' => 'devboyarif@gmail.com',
    'paystack_active' => true,

    'ssl_id' => 'zakir60ce16f5e7c1b',
    'ssl_pass' => 'zakir60ce16f5e7c1b@ssl',
    'ssl_active' => true,
    'ssl_mode' => 'sandbox',

    'flw_public_key' => 'FLWPUBK-1c22f4e9aff975d7a2c065769eb108ec-X',
    'flw_secret' => 'FLWSECK-9980bcab0befe83e9744c2d329696ed1-191483f982fvt-X',
    'flw_secret_hash' => '9980bcab0bef82ebb2bd749a',
    'flw_active' => true,

    'im_key' => '',
    'im_secret' => '',
    'im_url' => 'https://test.instamojo.com/api/1.1/',
    'im_active' => false,

    'midtrans_merchat_id' => 'G167247402',
    'midtrans_client_key' => 'SB-Mid-client-ezmy-mRagVlyOgLy',
    'midtrans_server_key' => 'SB-Mid-server-vkrPiwxANbPTTOQ_ojLnMVc0',
    'midtrans_active' => false,
    'midtrans_live_mode' => false,

    'mollie_key' => 'test_Q9JvB3aM6e2Wkc92QjpBV3k88AF3x6',
    'mollie_active' => true,

    'google_analytics' => '',
    'google_analytics_status' => false,

    'indeed_id' => '7778623931867371',
    'indeed_limit' => '10',

    'careerjet_id' => '89e775b5aff6d7089938a04742c7d680',
    'careerjet_default_locale' => 'en_US',
    'careerjet_limit' => '50',

    'default_job_provider' => 'indeed',

    'pusher_app_id' => env('PUSHER_APP_ID'),
    'pusher_app_key' => env('PUSHER_APP_KEY'),
    'pusher_app_secret' => env('PUSHER_APP_SECRET'),
    'pusher_host' => env('PUSHER_HOST'),
    'pusher_port' => env('PUSHER_PORT'),
    'pusher_schema' => env('PUSHER_SCHEME', 'https'),
    'pusher_app_cluster' => env('PUSHER_APP_CLUSTER'),

    'Iyzipay_api_key' => 'sandbox-sQFqo1xO9AZub8xmt2CZjVqTTWVVgnbu',
    'Iyzipay_api_secret' => 'sandbox-r9zHFYjfcGPYCV5tQGC0c0glfq05a9wC',
    'Iyzipay_active' => false,
    'Iyzipay_base_url' => 'https://sandbox-api.iyzipay.com',
    'Iyzipay_live_mode' => false,

    'map_show' => true,

];
