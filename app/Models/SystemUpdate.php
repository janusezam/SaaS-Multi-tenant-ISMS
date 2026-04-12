<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class SystemUpdate extends Model
{
    use CentralConnection;

    protected $fillable = [
        'title',
        'summary',
        'version',
        'source',
        'is_published',
        'published_at',
        'meta',
        'created_by_super_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function tenantReads(): HasMany
    {
        return $this->hasMany(TenantSystemUpdateRead::class);
    }
}
