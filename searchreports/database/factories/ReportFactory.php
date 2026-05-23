<?php

namespace Database\Factories;

use App\Models\GscProperty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => GscProperty::factory(),
            'template_id' => null,
            'name' => 'SEO Report ' . $this->faker->date(),
            'date_from' => now()->subDays(28)->toDateString(),
            'date_to' => now()->toDateString(),
            'status' => 'ready',
            'output_format' => 'pdf',
            'file_path' => null,
            'generated_at' => now(),
            'expires_at' => now()->addDays(30),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'generated_at' => null]);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed', 'generated_at' => null]);
    }
}
