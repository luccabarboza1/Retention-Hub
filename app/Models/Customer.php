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
        'related_emails',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'monthly_fee'               => 'decimal:2',
        'contracted_at'             => 'date',
        'canceled_at'               => 'date',
        'instagram_followers_count' => 'integer',
        'related_emails'            => 'array',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function tagsRelation()
    {
        return $this->belongsToMany(Tag::class, 'customer_tag');
    }

    public function getTagsAttribute()
    {
        return $this->tagsRelation->pluck('name')->toArray();
    }

    public function syncTags(array $tagNames)
    {
        $tagIds = [];
        foreach ($tagNames as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $tag = Tag::firstOrCreate([
                'name' => $name,
                'type' => 'customer',
            ]);
            $tagIds[] = $tag->id;
        }
        $this->tagsRelation()->sync($tagIds);
    }
}
