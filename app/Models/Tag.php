<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_tag');
    }

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_tag');
    }
}
