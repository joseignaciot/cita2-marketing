<?php

namespace App\Services;

use App\Models\GscDataCache;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportTemplateEngine
{
    public static array $availableWidgets = [
        'metrics_summary' => [
            'label' => 'Metrics Summary',
            'description' => 'Total clicks, impressions, avg CTR and position',
            'defaultConfig' => ['metrics' => ['clicks', 'impressions', 'ctr', 'position']],
            'defaultSize' => ['w' => 12, 'h' => 2],
        ],
        'clicks_chart' => [
            'label' => 'Clicks Over Time',
            'description' => 'Line chart of clicks over the selected period',
            'defaultConfig' => ['color' => '#3b82f6'],
            'defaultSize' => ['w' => 6, 'h' => 4],
        ],
        'impressions_chart' => [
            'label' => 'Impressions Over Time',
            'description' => 'Line chart of impressions over the selected period',
            'defaultConfig' => ['color' => '#8b5cf6'],
            'defaultSize' => ['w' => 6, 'h' => 4],
        ],
        'top_queries' => [
            'label' => 'Top Queries',
            'description' => 'Table of top queries with all metrics',
            'defaultConfig' => ['limit' => 20],
            'defaultSize' => ['w' => 6, 'h' => 6],
        ],
        'top_pages' => [
            'label' => 'Top Pages',
            'description' => 'Table of top pages with all metrics',
            'defaultConfig' => ['limit' => 20],
            'defaultSize' => ['w' => 6, 'h' => 6],
        ],
        'queries_by_device' => [
            'label' => 'Queries by Device',
            'description' => 'Breakdown of clicks/impressions by device type',
            'defaultConfig' => [],
            'defaultSize' => ['w' => 4, 'h' => 4],
        ],
        'clicks_by_country' => [
            'label' => 'Clicks by Country',
            'description' => 'Table of clicks and impressions by country',
            'defaultConfig' => ['limit' => 10],
            'defaultSize' => ['w' => 4, 'h' => 4],
        ],
        'position_distribution' => [
            'label' => 'Position Distribution',
            'description' => 'Histogram of keyword positions (1-10, 11-20, 21+)',
            'defaultConfig' => [],
            'defaultSize' => ['w' => 4, 'h' => 4],
        ],
        'ctr_vs_position' => [
            'label' => 'CTR vs Position',
            'description' => 'Scatter plot comparing CTR and average position',
            'defaultConfig' => [],
            'defaultSize' => ['w' => 6, 'h' => 4],
        ],
        'date_comparison' => [
            'label' => 'Period Comparison',
            'description' => 'Metrics compared to previous equivalent period',
            'defaultConfig' => [],
            'defaultSize' => ['w' => 12, 'h' => 3],
        ],
    ];

    public function generateReport(Report $report): void
    {
        $report->update(['status' => 'generating']);

        try {
            $templateConfig = $report->template?->config ?? $this->defaultConfig();
            $data = $this->buildReportData($report, $templateConfig);

            $content = match ($report->output_format) {
                'pdf' => $this->generatePdf($report, $data, $templateConfig),
                'html' => $this->generateHtml($report, $data, $templateConfig),
                'json' => $this->generateJson($report, $data),
                default => throw new \InvalidArgumentException("Unknown format: {$report->output_format}"),
            };

            $path = $this->storePath($report);
            Storage::disk(config('gsc.reports_disk'))->put($path, $content);

            $report->update([
                'status' => 'ready',
                'file_path' => $path,
                'generated_at' => now(),
                'expires_at' => now()->addDays(config('gsc.report_expires_days')),
            ]);
        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function buildReportData(Report $report, array $config): array
    {
        $cache = GscDataCache::where('property_id', $report->property_id)
            ->where('date_range_start', $report->date_from)
            ->where('date_range_end', $report->date_to)
            ->valid()
            ->first();

        $rows = $cache?->metrics ?? [];

        return [
            'property' => $report->property,
            'dateFrom' => $report->date_from->toDateString(),
            'dateTo' => $report->date_to->toDateString(),
            'widgets' => collect($config['widgets'] ?? [])->map(
                fn ($widget) => $this->transformWidgetData($widget, $rows)
            )->toArray(),
            'config' => $config,
        ];
    }

    private function transformWidgetData(array $widget, array $rows): array
    {
        $type = $widget['type'];
        $cfg = $widget['config'] ?? [];

        $transformed = match ($type) {
            'metrics_summary' => $this->metricsSummary($rows, $cfg),
            'top_queries' => $this->topQueries($rows, $cfg),
            'top_pages' => $this->topPages($rows, $cfg),
            'clicks_chart' => $this->clicksChart($rows, $cfg),
            'impressions_chart' => $this->impressionsChart($rows, $cfg),
            'queries_by_device' => $this->byDevice($rows),
            'clicks_by_country' => $this->byCountry($rows, $cfg),
            'position_distribution' => $this->positionDistribution($rows),
            default => [],
        };

        return array_merge($widget, ['data' => $transformed]);
    }

    private function metricsSummary(array $rows, array $cfg): array
    {
        return [
            'clicks' => (int) collect($rows)->sum('clicks'),
            'impressions' => (int) collect($rows)->sum('impressions'),
            'ctr' => round(collect($rows)->avg('ctr') * 100, 2),
            'position' => round((float) collect($rows)->avg('position'), 1),
        ];
    }

    private function topQueries(array $rows, array $cfg): array
    {
        return collect($rows)
            ->sortByDesc('clicks')
            ->take($cfg['limit'] ?? 20)
            ->values()
            ->toArray();
    }

    private function topPages(array $rows, array $cfg): array
    {
        return collect($rows)
            ->groupBy(fn ($r) => $r['keys'][1] ?? $r['keys'][0] ?? '')
            ->map(fn ($group) => [
                'page' => $group->first()['keys'][1] ?? $group->first()['keys'][0] ?? '',
                'clicks' => $group->sum('clicks'),
                'impressions' => $group->sum('impressions'),
                'ctr' => round($group->avg('ctr') * 100, 2),
                'position' => round($group->avg('position'), 1),
            ])
            ->sortByDesc('clicks')
            ->take($cfg['limit'] ?? 20)
            ->values()
            ->toArray();
    }

    private function clicksChart(array $rows, array $cfg): array
    {
        return ['values' => collect($rows)->pluck('clicks')->toArray()];
    }

    private function impressionsChart(array $rows, array $cfg): array
    {
        return ['values' => collect($rows)->pluck('impressions')->toArray()];
    }

    private function byDevice(array $rows): array
    {
        return collect($rows)
            ->groupBy(fn ($r) => $r['keys'][2] ?? 'unknown')
            ->map(fn ($group, $device) => [
                'device' => $device,
                'clicks' => $group->sum('clicks'),
                'impressions' => $group->sum('impressions'),
            ])
            ->values()
            ->toArray();
    }

    private function byCountry(array $rows, array $cfg): array
    {
        return collect($rows)
            ->groupBy(fn ($r) => $r['keys'][2] ?? 'unknown')
            ->map(fn ($group, $country) => [
                'country' => $country,
                'clicks' => $group->sum('clicks'),
                'impressions' => $group->sum('impressions'),
            ])
            ->sortByDesc('clicks')
            ->take($cfg['limit'] ?? 10)
            ->values()
            ->toArray();
    }

    private function positionDistribution(array $rows): array
    {
        $buckets = ['1-3' => 0, '4-10' => 0, '11-20' => 0, '21-50' => 0, '51+' => 0];

        foreach ($rows as $row) {
            $pos = (float) $row['position'];
            if ($pos <= 3) $buckets['1-3']++;
            elseif ($pos <= 10) $buckets['4-10']++;
            elseif ($pos <= 20) $buckets['11-20']++;
            elseif ($pos <= 50) $buckets['21-50']++;
            else $buckets['51+']++;
        }

        return $buckets;
    }

    private function generatePdf(Report $report, array $data, array $config): string
    {
        $pdf = Pdf::loadView('reports.pdf', array_merge($data, ['report' => $report]));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    private function generateHtml(Report $report, array $data, array $config): string
    {
        return view('reports.html', array_merge($data, ['report' => $report]))->render();
    }

    private function generateJson(Report $report, array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    private function storePath(Report $report): string
    {
        $ext = $report->output_format;
        return config('gsc.reports_path') . "/{$report->user_id}/{$report->id}.{$ext}";
    }

    private function defaultConfig(): array
    {
        return [
            'layout' => 'grid',
            'color_scheme' => ['primary' => '#3b82f6', 'accent' => '#8b5cf6', 'bg' => '#ffffff'],
            'logo_url' => null,
            'date_range' => ['type' => 'last_28', 'days' => 28],
            'widgets' => [
                [
                    'id' => Str::uuid(),
                    'type' => 'metrics_summary',
                    'title' => 'Overview',
                    'position' => ['col' => 1, 'row' => 1, 'w' => 12, 'h' => 2],
                    'config' => ['metrics' => ['clicks', 'impressions', 'ctr', 'position']],
                ],
                [
                    'id' => Str::uuid(),
                    'type' => 'top_queries',
                    'title' => 'Top Queries',
                    'position' => ['col' => 1, 'row' => 3, 'w' => 6, 'h' => 6],
                    'config' => ['limit' => 20],
                ],
            ],
            'filters' => ['country' => null, 'device' => null, 'query_regex' => null],
            'show_branding' => true,
        ];
    }
}
