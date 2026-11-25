<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public demo access
    |--------------------------------------------------------------------------
    |
    | The demo tenant is intentionally opt-in.  Keep this disabled on a
    | production installation unless a separate, disposable demo business has
    | been provisioned with `php artisan demo:provision`.
    |
    */
    'enabled' => (bool) env('DEMO_ACCOUNT_ENABLED', false),
    'username' => env('DEMO_ACCOUNT_USERNAME', 'demo'),
    'password_hint' => env('DEMO_ACCOUNT_PASSWORD_HINT'),
];
