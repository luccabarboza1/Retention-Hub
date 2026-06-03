<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerUpdated
{
    use Dispatchable;

    public string $triggerType = 'customer.updated';
    public int $entityId;

    public function __construct(public readonly Customer $customer)
    {
        $this->entityId = $customer->id;
    }

    public function toWebhookPayload(): array
    {
        return [
            'event'     => $this->triggerType,
            'timestamp' => now()->toIso8601String(),
            'data'      => $this->customer->toArray(),
        ];
    }
}
