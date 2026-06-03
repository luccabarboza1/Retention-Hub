<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Foundation\Events\Dispatchable;

class CardCreated
{
    use Dispatchable;

    public string $triggerType = 'card.created';
    public int $entityId;

    public function __construct(public readonly Card $card)
    {
        $this->entityId = $card->id;
    }

    public function toWebhookPayload(): array
    {
        return [
            'event'     => $this->triggerType,
            'timestamp' => now()->toIso8601String(),
            'data'      => $this->card->load('customer', 'product')->toArray(),
        ];
    }
}
