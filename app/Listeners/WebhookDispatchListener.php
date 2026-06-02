<?php

namespace App\Listeners;

use App\Jobs\DispatchWebhookJob;
use App\Models\WebhookSubscription;

class WebhookDispatchListener
{
    public function handle(object $event): void
    {
        if (!isset($event->triggerType, $event->entityId)) {
            return;
        }

        $payload = $event->toWebhookPayload();

        WebhookSubscription::query()
            ->where(function ($q) use ($event) {
                $q->whereJsonContains('trigger_types', $event->triggerType)
                  ->orWhereJsonContains('trigger_types', '*');
            })
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->each(function (WebhookSubscription $subscription) use ($event, $payload) {
                DispatchWebhookJob::dispatch(
                    $subscription->id,
                    $event->triggerType,
                    $event->entityId,
                    $payload,
                );
            });
    }
}
