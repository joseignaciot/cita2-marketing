<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReportShareFactory extends Factory
{
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'share_token' => Str::random(40),
            'expires_at' => now()->addDays(30),
            'password_hash' => null,
            'view_count' => 0,
        ];
    }
}
