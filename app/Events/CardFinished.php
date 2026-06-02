<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Foundation\Events\Dispatchable;

class CardFinished
{
    use Dispatchable;

    public string $triggerType = 'card.finished';
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
            'data'      => $this->card->load('customer', 'product', 'chats')->toArray(),
        ];
    }
}
