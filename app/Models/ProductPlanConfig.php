<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPlanConfig extends Model
{
    protected $fillable = ['product_type', 'plan_name', 'price_per_unit', 'unit_label'];

    protected $casts = ['price_per_unit' => 'decimal:2'];
}
