<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; background: {{ $config['color_scheme']['bg'] ?? '#ffffff' }}; font-size: 12px; }
        .header { padding: 24px; border-bottom: 3px solid {{ $config['color_scheme']['primary'] ?? '#3b82f6' }}; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 22px; font-weight: 700; color: {{ $config['color_scheme']['primary'] ?? '#3b82f6' }}; }
        .meta { font-size: 11px; color: #64748b; }
        .property { font-size: 13px; color: #475569; margin-top: 4px; }
        .content { padding: 24px; }
        .widget { margin-bottom: 24px; page-break-inside: avoid; }
        .widget-title { font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }
        .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .metric-card { background: #f8fafc; border-radius: 8px; padding: 16px; text-align: center; }
        .metric-value { font-size: 24px; font-weight: 700; color: {{ $config['color_scheme']['primary'] ?? '#3b82f6' }}; }
        .metric-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        tr:hover td { background: #f8fafc; }
        .footer { text-align: center; padding: 16px; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            @if(!empty($config['logo_url']))
                <img src="{{ $config['logo_url'] }}" height="40" alt="Logo">
            @endif
            <h1>{{ $report->name }}</h1>
            <div class="property">{{ $property->display_name_or_url }}</div>
            <div class="meta">{{ $dateFrom }} — {{ $dateTo }}</div>
        </div>
        <div class="meta">Generated {{ now()->format('M d, Y') }}</div>
    </div>

    <div class="content">
        @foreach($widgets as $widget)
            <div class="widget">
                <div class="widget-title">{{ $widget['title'] ?? ucwords(str_replace('_', ' ', $widget['type'])) }}</div>

                @switch($widget['type'])
                    @case('metrics_summary')
                        <div class="metrics-grid">
                            @if(in_array('clicks', $widget['config']['metrics'] ?? []))
                                <div class="metric-card">
                                    <div class="metric-value">{{ number_format($widget['data']['clicks']) }}</div>
                                    <div class="metric-label">Clicks</div>
                                </div>
                            @endif
                            @if(in_array('impressions', $widget['config']['metrics'] ?? []))
                                <div class="metric-card">
                                    <div class="metric-value">{{ number_format($widget['data']['impressions']) }}</div>
                                    <div class="metric-label">Impressions</div>
                                </div>
                            @endif
                            @if(in_array('ctr', $widget['config']['metrics'] ?? []))
                                <div class="metric-card">
                                    <div class="metric-value">{{ $widget['data']['ctr'] }}%</div>
                                    <div class="metric-label">Avg CTR</div>
                                </div>
                            @endif
                            @if(in_array('position', $widget['config']['metrics'] ?? []))
                                <div class="metric-card">
                                    <div class="metric-value">{{ $widget['data']['position'] }}</div>
                                    <div class="metric-label">Avg Position</div>
                                </div>
                            @endif
                        </div>
                        @break

                    @case('top_queries')
                    @case('top_pages')
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ $widget['type'] === 'top_queries' ? 'Query' : 'Page' }}</th>
                                    <th>Clicks</th>
                                    <th>Impressions</th>
                                    <th>CTR</th>
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($widget['data'] as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row['keys'][0] ?? $row['page'] ?? '' }}</td>
                                        <td>{{ number_format($row['clicks']) }}</td>
                                        <td>{{ number_format($row['impressions']) }}</td>
                                        <td>{{ round($row['ctr'] * 100, 2) }}%</td>
                                        <td>{{ round($row['position'], 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @break

                    @default
                        <p style="color:#94a3b8;font-size:11px;">Data available in interactive view.</p>
                @endswitch
            </div>
        @endforeach
    </div>

    @if(!empty($config['show_branding']))
        <div class="footer">Generated by SearchReports</div>
    @endif
</body>
</html>
