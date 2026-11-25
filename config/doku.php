<?php

$environment = env('DOKU_ENV', 'production');

return [
    'env' => $environment,
    'subscription_gateway' => env('SUBSCRIPTION_GATEWAY', 'doku'),
    'client_id' => env('DOKU_CLIENT_ID'),
    'secret_key' => env('DOKU_SECRET_KEY'),
    'base_url' => env('DOKU_BASE_URL', $environment === 'production' ? 'https://api.doku.com' : 'https://api-sandbox.doku.com'),
    'notification_url' => env('DOKU_NOTIFICATION_URL'),
    'signature_debug' => env('DOKU_SIGNATURE_DEBUG', false),
    'payment_due_minutes' => 60,
];
