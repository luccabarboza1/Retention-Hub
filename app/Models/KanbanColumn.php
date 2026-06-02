<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    protected $fillable = ['name', 'order', 'color'];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'status', 'name');
    }
}
