<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            str_contains(request()->getHost(), 'umbler.net') ||
            env('FORCE_HTTPS', false)
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        $this->app->scoped('audit.actor', function () {
            /** @var Request $request */
            $request = $this->app->make(Request::class);
            return $request->header('X-Actor', 'api');
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::apiKey('header', 'X-Api-Token')
            );
        });
    }
}
