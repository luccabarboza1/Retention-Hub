<?php

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductChange extends Model
{
    use SoftDeletes, HasAudit;

    protected $fillable = [
        'customer_id',
        'product_id',
        'change_type',
        'delta_consumption',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delta_consumption' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
