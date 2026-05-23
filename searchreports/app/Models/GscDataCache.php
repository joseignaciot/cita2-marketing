<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GscDataCache extends Model
{
    use HasFactory;

    protected $table = 'gsc_data_cache';

    protected $fillable = [
        'property_id',
        'date_range_start',
        'date_range_end',
        'dimensions',
        'metrics',
        'query_count',
        'fetched_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'date_range_start' => 'date',
            'date_range_end' => 'date',
            'dimensions' => 'array',
            'metrics' => 'array',
            'fetched_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(GscProperty::class, 'property_id');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
