<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Foundation\Events\Dispatchable;

class CardDeleted
{
    use Dispatchable;

    public string $triggerType = 'card.deleted';
    public int $entityId;

    public function __construct(public readonly Card $card)
    {
        $this->entityId = $card->id;
    }

    public function toWebhookPayload(): array
    {
        return [
            'event'      => $this->triggerType,
            'timestamp'  => now()->toIso8601String(),
            'deleted_id' => $this->entityId,
            'data'       => [
                'id'             => $this->card->id,
                'contact_reason' => $this->card->contact_reason,
                'status'         => $this->card->status,
                'customer_id'    => $this->card->customer_id,
                'customer_name'  => $this->card->customer?->company_name,
                'deleted_at'     => now()->toIso8601String(),
            ],
        ];
    }
}
