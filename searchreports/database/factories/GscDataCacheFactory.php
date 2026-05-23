<?php

namespace Database\Factories;

use App\Models\GscProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

class GscDataCacheFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => GscProperty::factory(),
            'date_range_start' => now()->subDays(28)->toDateString(),
            'date_range_end' => now()->toDateString(),
            'dimensions' => ['query'],
            'metrics' => [],
            'query_count' => 0,
            'fetched_at' => now(),
            'expires_at' => now()->addHours(6),
        ];
    }
}
