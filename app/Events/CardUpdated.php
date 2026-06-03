<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Foundation\Events\Dispatchable;

class CardUpdated
{
    use Dispatchable;

    public string $triggerType = 'card.updated';
    public int $entityId;

    public function __construct(public readonly Card $card)
    {
        $this->entityId = $card->id;
    }

    public function toWebhookPayload(): array
    {
        $card = $this->card->load(['customer', 'product', 'tagsRelation']);

        return [
            'event'     => $this->triggerType,
            'timestamp' => now()->toIso8601String(),
            'data'      => array_merge($card->toArray(), [
                'tags'     => $card->tags,
                'customer' => $card->customer?->load(['tagsRelation'])->toArray() + ['tags' => $card->customer?->tags ?? []],
            ]),
        ];
    }
}
