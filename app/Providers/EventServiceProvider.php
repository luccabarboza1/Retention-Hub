<?php

namespace App\Providers;

use App\Events\CardCreated;
use App\Events\CardDeleted;
use App\Events\CardFinished;
use App\Events\CardUpdated;
use App\Events\CustomerCreated;
use App\Events\CustomerDeleted;
use App\Events\CustomerUpdated;
use App\Listeners\WebhookDispatchListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CardCreated::class      => [WebhookDispatchListener::class],
        CardUpdated::class      => [WebhookDispatchListener::class],
        CardFinished::class     => [WebhookDispatchListener::class],
        CardDeleted::class      => [WebhookDispatchListener::class],
        CustomerCreated::class  => [WebhookDispatchListener::class],
        CustomerUpdated::class  => [WebhookDispatchListener::class],
        CustomerDeleted::class  => [WebhookDispatchListener::class],
    ];

    public function boot(): void
    {
        //
    }
}
