<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Default guard and password broker for your application.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),       // Default guard: tenant users
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'), // Default password broker: tenant
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        // Tenant Users (default web session)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Tenant Users API (JWT)
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'hash' => false,
        ],

        // Central Users (Admins)
        'central' => [
            'driver' => 'jwt',
            'provider' => 'central_users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        // Tenant Users
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Central Users
        'central_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Central\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        // Tenant Users
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        // Central Users
        'central_users' => [
            'provider' => 'central_users',
            'table' => 'central_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
