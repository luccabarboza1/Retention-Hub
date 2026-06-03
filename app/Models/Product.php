<?php

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasAudit;

    protected $fillable = [
        'customer_id',
        'external_id',
        'contract_identifier',
        'product_type',
        'plan_name',
        'attendants_count',
        'host_services',
        'consumption',
        'status',
        'has_chatbot',
        'has_ai',
        'has_implementation',
        'external_created_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'consumption'          => 'decimal:2',
        'attendants_count'     => 'integer',
        'host_services'        => 'array',
        'has_chatbot'          => 'boolean',
        'has_ai'               => 'boolean',
        'has_implementation'   => 'boolean',
        'external_created_at'  => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(ProductChange::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
