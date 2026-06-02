<?php

namespace App\Jobs;

use App\Models\WebhookDispatchLog;
use App\Models\WebhookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 40;

    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly int    $subscriptionId,
        private readonly string $eventType,
        private readonly int    $eventEntityId,
        private readonly array  $payload,
        private readonly int    $attempt = 1,
    ) {
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        $subscription = WebhookSubscription::find($this->subscriptionId);

        if (!$subscription || !$subscription->is_active || $subscription->deleted_at !== null) {
            return;
        }

        $log = WebhookDispatchLog::create([
            'subscription_id' => $this->subscriptionId,
            'event_type'      => $this->eventType,
            'event_entity_id' => $this->eventEntityId,
            'attempt_number'  => $this->attempt,
            'max_attempts'    => self::MAX_ATTEMPTS,
            'status'          => 'pending',
            'payload'         => $this->payload,
            'target_url'      => $subscription->url,
            'dispatched_at'   => now(),
        ]);

        $payloadJson = json_encode($this->payload);
        $signature   = hash_hmac('sha256', $payloadJson, $subscription->secret);

        try {
            $response = Http::timeout(config('app.webhook_http_timeout', 25))
                ->withHeaders([
                    'X-Umbler-Signature' => $signature,
                    'X-Umbler-Event'     => $this->eventType,
                    'Content-Type'       => 'application/json',
                ])
                ->send('POST', $subscription->url, ['body' => $payloadJson]);

            $log->update([
                'status'        => $response->successful() ? 'success' : 'failed',
                'http_status'   => $response->status(),
                'response_body' => substr($response->body(), 0, 4000),
                'responded_at'  => now(),
            ]);

            if (!$response->successful()) {
                $this->scheduleRetry($log);
            }
        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => substr($e->getMessage(), 0, 1000),
                'responded_at'  => now(),
            ]);

            $this->scheduleRetry($log);
        }
    }

    private function scheduleRetry(WebhookDispatchLog $log): void
    {
        if ($this->attempt >= self::MAX_ATTEMPTS) {
            $log->update(['status' => 'permanently_failed']);

            Log::error('Webhook permanently failed after max attempts', [
                'subscription_id' => $this->subscriptionId,
                'event_type'      => $this->eventType,
                'event_entity_id' => $this->eventEntityId,
                'log_id'          => $log->id,
            ]);

            return;
        }

        $delaySeconds = min(300, 30 * (2 ** ($this->attempt - 1)));

        $log->update(['next_retry_at' => now()->addSeconds($delaySeconds)]);

        self::dispatch(
            $this->subscriptionId,
            $this->eventType,
            $this->eventEntityId,
            $this->payload,
            $this->attempt + 1,
        )->delay(now()->addSeconds($delaySeconds));
    }
}
