<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in(__FILE__);

use App\Models\GscDataCache;
use App\Models\GscProperty;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\User;
use App\Services\ReportTemplateEngine;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('local');
    $this->engine = new ReportTemplateEngine();
    $this->user = User::factory()->create();
    $this->property = GscProperty::factory()->create(['user_id' => $this->user->id]);
});

it('has available widget definitions', function () {
    expect(ReportTemplateEngine::$availableWidgets)->toHaveKey('metrics_summary');
    expect(ReportTemplateEngine::$availableWidgets)->toHaveKey('top_queries');
    expect(ReportTemplateEngine::$availableWidgets)->toHaveKey('top_pages');
});

it('generates a json report', function () {
    $template = ReportTemplate::factory()->create([
        'user_id' => $this->user->id,
        'config' => [
            'layout' => 'grid',
            'color_scheme' => ['primary' => '#3b82f6', 'accent' => '#8b5cf6', 'bg' => '#ffffff'],
            'widgets' => [[
                'id' => Str::uuid(),
                'type' => 'metrics_summary',
                'title' => 'Summary',
                'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2],
                'config' => ['metrics' => ['clicks', 'impressions']],
            ]],
            'show_branding' => true,
        ],
    ]);

    GscDataCache::factory()->create([
        'property_id' => $this->property->id,
        'date_range_start' => '2026-04-01',
        'date_range_end' => '2026-04-30',
        'dimensions' => ['query'],
        'metrics' => [
            ['keys' => ['laravel'], 'clicks' => 100, 'impressions' => 1000, 'ctr' => 0.1, 'position' => 3.5],
        ],
        'query_count' => 1,
        'fetched_at' => now(),
        'expires_at' => now()->addHours(6),
    ]);

    $report = Report::factory()->create([
        'user_id' => $this->user->id,
        'property_id' => $this->property->id,
        'template_id' => $template->id,
        'date_from' => '2026-04-01',
        'date_to' => '2026-04-30',
        'status' => 'pending',
        'output_format' => 'json',
    ]);

    $this->engine->generateReport($report);

    $report->refresh();
    expect($report->status)->toBe('ready');
    expect($report->file_path)->not->toBeNull();
    Storage::disk('local')->assertExists($report->file_path);
});
