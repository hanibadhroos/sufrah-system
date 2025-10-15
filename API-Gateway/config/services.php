<?php

return [

    'auth_service' => env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001'),
    'order_service' => env('ORDER_SERVICE_URL', 'http://127.0.0.1:8002'),
    'payment_service' => env('PAYMENT_SERVICE_URL', 'http://127.0.0.1:8003'),
    'tenant_service' => env('TENANT_SERVICE_URL', 'http://127.0.0.1:8004'),
    'menu_service' => env('MENU_SERVICE_URL', 'http://127.0.0.1:8005'),
    'inventory_service' => env('INVENTORY_SERVICE_URL', 'http://127.0.0.1:8006'),
    'reservation_service' => env('RESERVATION_SERVICE_URL', 'http://127.0.0.1:8007'),


    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
