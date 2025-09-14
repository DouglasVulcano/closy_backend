<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for Stripe integration.
    | You can set your Stripe keys and other settings here.
    |
    */

    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cashier Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Laravel Cashier
    |
    */

    'currency' => env('CASHIER_CURRENCY', 'brl'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'pt_BR'),
    'logger' => env('CASHIER_LOGGER'),

];