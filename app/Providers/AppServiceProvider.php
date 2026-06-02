<?php

namespace App\Providers;

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
        // Resolve the audit actor from the incoming request header X-Actor
        $this->app->scoped('audit.actor', function () {
            /** @var Request $request */
            $request = $this->app->make(Request::class);
            return $request->header('X-Actor', 'api');
        });
    }
}
