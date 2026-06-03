<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDispatchLog extends Model
{
    protected $fillable = [
        'subscription_id',
        'event_type',
        'event_entity_id',
        'attempt_number',
        'max_attempts',
        'status',
        'payload',
        'target_url',
        'http_status',
        'response_body',
        'error_message',
        'dispatched_at',
        'responded_at',
        'next_retry_at',
    ];

    protected $casts = [
        'payload'       => 'array',
        'dispatched_at' => 'datetime',
        'responded_at'  => 'datetime',
        'next_retry_at' => 'datetime',
        'attempt_number' => 'integer',
        'max_attempts'   => 'integer',
        'http_status'    => 'integer',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class);
    }
}
