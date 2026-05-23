<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GscProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_url',
        'site_type',
        'permission_level',
        'is_active',
        'last_synced_at',
        'display_name',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dataCache(): HasMany
    {
        return $this->hasMany(GscDataCache::class, 'property_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'property_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function getDisplayNameOrUrlAttribute(): string
    {
        return $this->display_name ?: $this->site_url;
    }
}
