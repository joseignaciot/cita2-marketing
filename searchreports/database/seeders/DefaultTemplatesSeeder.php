<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@searchreports.app')->first();

        if (!$admin) return;

        $templates = [
            [
                'name' => 'Executive Summary',
                'description' => 'High-level metrics overview with top queries and click trend chart.',
                'is_public' => true,
                'config' => [
                    'layout' => 'grid',
                    'color_scheme' => ['primary' => '#3b82f6', 'accent' => '#8b5cf6', 'bg' => '#ffffff'],
                    'widgets' => [
                        ['id' => Str::uuid(), 'type' => 'metrics_summary', 'title' => 'Overview', 'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2], 'config' => ['metrics' => ['clicks', 'impressions', 'ctr', 'position']]],
                        ['id' => Str::uuid(), 'type' => 'clicks_chart', 'title' => 'Clicks Trend', 'position' => ['col' => 1, 'row' => 3, 'w' => 8, 'h' => 4], 'config' => []],
                        ['id' => Str::uuid(), 'type' => 'top_queries', 'title' => 'Top Queries', 'position' => ['col' => 1, 'row' => 7, 'w' => 6, 'h' => 6], 'config' => ['limit' => 10]],
                    ],
                    'show_branding' => true,
                ],
            ],
            [
                'name' => 'Full SEO Analysis',
                'description' => 'Comprehensive report with all available widgets and breakdowns.',
                'is_public' => true,
                'config' => [
                    'layout' => 'grid',
                    'color_scheme' => ['primary' => '#10b981', 'accent' => '#059669', 'bg' => '#ffffff'],
                    'widgets' => [
                        ['id' => Str::uuid(), 'type' => 'metrics_summary', 'title' => 'Overview', 'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2], 'config' => ['metrics' => ['clicks', 'impressions', 'ctr', 'position']]],
                        ['id' => Str::uuid(), 'type' => 'clicks_chart', 'title' => 'Clicks', 'position' => ['col' => 1, 'row' => 3, 'w' => 6, 'h' => 4], 'config' => []],
                        ['id' => Str::uuid(), 'type' => 'impressions_chart', 'title' => 'Impressions', 'position' => ['col' => 7, 'row' => 3, 'w' => 6, 'h' => 4], 'config' => []],
                        ['id' => Str::uuid(), 'type' => 'top_queries', 'title' => 'Top Queries', 'position' => ['col' => 1, 'row' => 7, 'w' => 6, 'h' => 8], 'config' => ['limit' => 20]],
                        ['id' => Str::uuid(), 'type' => 'top_pages', 'title' => 'Top Pages', 'position' => ['col' => 7, 'row' => 7, 'w' => 6, 'h' => 8], 'config' => ['limit' => 20]],
                        ['id' => Str::uuid(), 'type' => 'queries_by_device', 'title' => 'By Device', 'position' => ['col' => 1, 'row' => 15, 'w' => 4, 'h' => 4], 'config' => []],
                        ['id' => Str::uuid(), 'type' => 'clicks_by_country', 'title' => 'By Country', 'position' => ['col' => 5, 'row' => 15, 'w' => 4, 'h' => 4], 'config' => ['limit' => 10]],
                        ['id' => Str::uuid(), 'type' => 'position_distribution', 'title' => 'Position Distribution', 'position' => ['col' => 9, 'row' => 15, 'w' => 4, 'h' => 4], 'config' => []],
                    ],
                    'show_branding' => true,
                ],
            ],
            [
                'name' => 'Positioning Report',
                'description' => 'Focused on keyword positions, CTR analysis, and ranking opportunities.',
                'is_public' => true,
                'config' => [
                    'layout' => 'grid',
                    'color_scheme' => ['primary' => '#f59e0b', 'accent' => '#d97706', 'bg' => '#ffffff'],
                    'widgets' => [
                        ['id' => Str::uuid(), 'type' => 'metrics_summary', 'title' => 'Key Metrics', 'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2], 'config' => ['metrics' => ['ctr', 'position']]],
                        ['id' => Str::uuid(), 'type' => 'position_distribution', 'title' => 'Position Distribution', 'position' => ['col' => 1, 'row' => 3, 'w' => 6, 'h' => 4], 'config' => []],
                        ['id' => Str::uuid(), 'type' => 'ctr_vs_position', 'title' => 'CTR vs Position', 'position' => ['col' => 7, 'row' => 3, 'w' => 6, 'h' => 4], 'config' => []],
                        ['id' => Str::uuid(), 'type' => 'top_queries', 'title' => 'Keywords by Position', 'position' => ['col' => 1, 'row' => 7, 'w' => 12, 'h' => 8], 'config' => ['limit' => 30]],
                    ],
                    'show_branding' => true,
                ],
            ],
        ];

        foreach ($templates as $tpl) {
            ReportTemplate::firstOrCreate(
                ['name' => $tpl['name'], 'user_id' => $admin->id],
                $tpl + ['user_id' => $admin->id]
            );
        }
    }
}
