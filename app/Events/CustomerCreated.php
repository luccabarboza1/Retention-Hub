<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerCreated
{
    use Dispatchable;

    public string $triggerType = 'customer.created';
    public int $entityId;

    public function __construct(public readonly Customer $customer)
    {
        $this->entityId = $customer->id;
    }

    public function toWebhookPayload(): array
    {
        $customer = $this->customer->load(['products', 'tagsRelation']);

        return [
            'event'     => $this->triggerType,
            'timestamp' => now()->toIso8601String(),
            'data'      => array_merge($customer->toArray(), [
                'tags'     => $customer->tags,
                'products' => $customer->products->toArray(),
            ]),
        ];
    }
}
