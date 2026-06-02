<?php

return [
    'name'     => env('APP_NAME', 'Retention Hub'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool) env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Sao_Paulo',
    'locale'   => 'pt_BR',
    'fallback_locale' => 'en',
    'faker_locale'    => 'pt_BR',
    'key'    => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'api_access_token'    => env('API_ACCESS_TOKEN'),
    'customer_lookup_url' => env('CUSTOMER_LOOKUP_URL'),

    'providers' => Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        Dedoc\Scramble\ScrambleServiceProvider::class,
    ])->toArray(),

    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->toArray(),
];
