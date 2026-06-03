<?php

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes, HasAudit;

    protected $fillable = [
        'client_name',
        'company_name',
        'segment',
        'company_size',
        'instagram_followers_count',
        'email',
        'monthly_fee',
        'contracted_at',
        'canceled_at',
        'tier',
        'channel_type',
        'plan_name',
        'has_chatbot',
        'has_ai',
        'has_implementation',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'monthly_fee'               => 'decimal:2',
        'contracted_at'             => 'date',
        'canceled_at'               => 'date',
        'has_chatbot'               => 'boolean',
        'has_ai'                    => 'boolean',
        'has_implementation'        => 'boolean',
        'instagram_followers_count' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
