<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Jobs\FetchGscDataJob;
use App\Jobs\GenerateReportJob;
use App\Models\GscDataCache;
use App\Models\Report;
use App\Models\ReportShare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $reports = $request->user()->reports()
            ->with(['property:id,site_url,display_name', 'template:id,name'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return ReportResource::collection($reports);
    }

    public function store(StoreReportRequest $request): ReportResource
    {
        $data = $request->validated();

        $report = $request->user()->reports()->create(array_merge($data, ['status' => 'pending']));

        $property = $report->property;
        $hasCachedData = GscDataCache::where('property_id', $property->id)
            ->where('date_range_start', $data['date_from'])
            ->where('date_range_end', $data['date_to'])
            ->valid()
            ->exists();

        if (!$hasCachedData) {
            FetchGscDataJob::dispatch($property, [
                'startDate' => $data['date_from'],
                'endDate' => $data['date_to'],
                'dimensions' => ['query', 'page'],
            ])->onQueue('gsc-fetch');
        }

        GenerateReportJob::dispatch($report)->onQueue('reports');

        return new ReportResource($report->load(['property', 'template']));
    }

    public function show(Report $report): ReportResource
    {
        $this->authorize('view', $report);

        return new ReportResource($report->load(['property', 'template', 'share']));
    }

    public function destroy(Report $report): JsonResponse
    {
        $this->authorize('delete', $report);

        if ($report->file_path) {
            Storage::disk(config('gsc.reports_disk'))->delete($report->file_path);
        }

        $report->delete();

        return response()->json(null, 204);
    }

    public function download(Report $report)
    {
        $this->authorize('view', $report);

        abort_unless($report->isReady(), 422, 'Report is not ready yet');
        abort_unless(Storage::disk(config('gsc.reports_disk'))->exists($report->file_path), 404, 'Report file not found');

        $mimeTypes = ['pdf' => 'application/pdf', 'html' => 'text/html', 'json' => 'application/json'];

        return Storage::disk(config('gsc.reports_disk'))->download(
            $report->file_path,
            Str::slug($report->name) . '.' . $report->output_format,
            ['Content-Type' => $mimeTypes[$report->output_format] ?? 'application/octet-stream']
        );
    }

    public function share(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        $share = $report->share()->updateOrCreate(
            ['report_id' => $report->id],
            [
                'share_token' => Str::random(40),
                'expires_at' => now()->addDays(30),
                'view_count' => 0,
            ]
        );

        return response()->json([
            'share_url' => route('reports.shared', $share->share_token),
            'token' => $share->share_token,
            'expires_at' => $share->expires_at,
        ]);
    }

    public function showShared(string $token)
    {
        $share = ReportShare::where('share_token', $token)->valid()->firstOrFail();

        $share->incrementViews();

        $report = $share->report()->with(['property', 'template'])->firstOrFail();

        return inertia('Reports/Shared', [
            'report' => new ReportResource($report),
        ]);
    }
}
