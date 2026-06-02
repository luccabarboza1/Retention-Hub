<?php

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes, HasAudit;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'ombudsman_card_id',
        'started_at',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at'  => 'datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'ombudsman_card_id');
    }
}
