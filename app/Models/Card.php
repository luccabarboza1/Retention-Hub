<?php

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes, HasAudit;

    protected $fillable = [
        'customer_id',
        'product_id',
        'status',
        'started_at',
        'finished_at',
        'ticket_origin',
        'ombudsman_agent',
        'ra_claim_link',
        'rating',
        'first_response_hours',
        'ra_public_response_hours',
        'usage_time_post_ombudsman_hours',
        'contact_reason',
        'reason_details',
        'responsible_team',
        'applied_solution',
        'is_sector_recurrent',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'started_at'                      => 'datetime',
        'finished_at'                     => 'datetime',
        'rating'                          => 'integer',
        'first_response_hours'            => 'decimal:2',
        'ra_public_response_hours'        => 'decimal:2',
        'usage_time_post_ombudsman_hours'  => 'decimal:2',
        'is_sector_recurrent'             => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'ombudsman_card_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['Retido', 'Churn']);
    }
}
