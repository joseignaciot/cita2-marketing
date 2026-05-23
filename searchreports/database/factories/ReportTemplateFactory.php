<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReportTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_public' => false,
            'config' => [
                'layout' => 'grid',
                'color_scheme' => ['primary' => '#3b82f6', 'accent' => '#8b5cf6', 'bg' => '#ffffff'],
                'widgets' => [
                    [
                        'id' => Str::uuid(),
                        'type' => 'metrics_summary',
                        'title' => 'Summary',
                        'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2],
                        'config' => ['metrics' => ['clicks', 'impressions', 'ctr', 'position']],
                    ],
                ],
                'filters' => ['country' => null, 'device' => null, 'query_regex' => null],
                'show_branding' => true,
            ],
        ];
    }

    public function public(): static
    {
        return $this->state(['is_public' => true]);
    }
}
