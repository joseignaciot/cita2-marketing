<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GscPropertyResource;
use App\Jobs\SyncGscPropertiesJob;
use App\Models\GscProperty;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $properties = $request->user()->gscProperties()->active()->get();

        return GscPropertyResource::collection($properties);
    }

    public function sync(Request $request): JsonResponse
    {
        SyncGscPropertiesJob::dispatch($request->user())->onQueue('gsc-fetch');

        return response()->json(['message' => 'Sync queued']);
    }

    public function metrics(Request $request, GscProperty $property, GoogleSearchConsoleService $gscService): JsonResponse
    {
        $this->authorize('view', $property);

        $params = [
            'startDate' => $request->input('start_date', now()->subDays(28)->toDateString()),
            'endDate' => $request->input('end_date', now()->toDateString()),
            'dimensions' => ['date'],
        ];

        $data = $gscService->fetchSearchAnalytics($property, $params);

        $summary = [
            'clicks' => collect($data)->sum('clicks'),
            'impressions' => collect($data)->sum('impressions'),
            'ctr' => round(collect($data)->avg('ctr') * 100, 2),
            'position' => round(collect($data)->avg('position'), 1),
        ];

        return response()->json(['summary' => $summary, 'series' => $data]);
    }

    public function queries(Request $request, GscProperty $property, GoogleSearchConsoleService $gscService): JsonResponse
    {
        $this->authorize('view', $property);

        $params = [
            'startDate' => $request->input('start_date', now()->subDays(28)->toDateString()),
            'endDate' => $request->input('end_date', now()->toDateString()),
            'dimensions' => ['query'],
            'rowLimit' => 1000,
        ];

        $rows = $gscService->fetchSearchAnalytics($property, $params);
        $perPage = (int) $request->input('per_page', 25);
        $page = (int) $request->input('page', 1);
        $sorted = collect($rows)->sortByDesc('clicks');

        return response()->json([
            'data' => $sorted->forPage($page, $perPage)->values(),
            'total' => $sorted->count(),
            'per_page' => $perPage,
            'current_page' => $page,
        ]);
    }

    public function pages(Request $request, GscProperty $property, GoogleSearchConsoleService $gscService): JsonResponse
    {
        $this->authorize('view', $property);

        $params = [
            'startDate' => $request->input('start_date', now()->subDays(28)->toDateString()),
            'endDate' => $request->input('end_date', now()->toDateString()),
            'dimensions' => ['page'],
            'rowLimit' => 1000,
        ];

        $rows = $gscService->fetchSearchAnalytics($property, $params);
        $perPage = (int) $request->input('per_page', 25);
        $page = (int) $request->input('page', 1);
        $sorted = collect($rows)->sortByDesc('clicks');

        return response()->json([
            'data' => $sorted->forPage($page, $perPage)->values(),
            'total' => $sorted->count(),
            'per_page' => $perPage,
            'current_page' => $page,
        ]);
    }
}
