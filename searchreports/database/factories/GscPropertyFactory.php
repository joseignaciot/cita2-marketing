<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GscPropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_url' => 'https://' . $this->faker->domainName(),
            'site_type' => 'url',
            'permission_level' => 'siteOwner',
            'is_active' => true,
            'last_synced_at' => now(),
            'display_name' => $this->faker->domainName(),
        ];
    }
}
