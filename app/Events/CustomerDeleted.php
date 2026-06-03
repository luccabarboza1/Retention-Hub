<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerDeleted
{
    use Dispatchable;

    public string $triggerType = 'customer.deleted';
    public int $entityId;

    public function __construct(public readonly Customer $customer)
    {
        $this->entityId = $customer->id;
    }

    public function toWebhookPayload(): array
    {
        return [
            'event'      => $this->triggerType,
            'timestamp'  => now()->toIso8601String(),
            'deleted_id' => $this->entityId,
            'data'       => [
                'id'           => $this->customer->id,
                'company_name' => $this->customer->company_name,
                'client_name'  => $this->customer->client_name,
                'email'        => $this->customer->email,
                'deleted_at'   => now()->toIso8601String(),
            ],
        ];
    }
}
