<?php

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebhookSubscription extends Model
{
    use SoftDeletes, HasAudit;

    protected $fillable = [
        'name',
        'url',
        'trigger_type',
        'secret',
        'is_active',
        'description',
        'created_by',
    ];

    protected $hidden = ['secret'];

    protected $casts = [
        'secret'    => 'encrypted',
        'is_active' => 'boolean',
    ];
}
