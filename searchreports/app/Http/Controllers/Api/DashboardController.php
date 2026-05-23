<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GscDataCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $properties = $user->gscProperties()->active()->get();

        $totalClicks = 0;
        $totalImpressions = 0;
        $propertyCount = $properties->count();

        foreach ($properties as $property) {
            $cache = GscDataCache::where('property_id', $property->id)
                ->where('date_range_start', '>=', now()->subDays(28)->toDateString())
                ->valid()
                ->first();

            if ($cache) {
                $rows = $cache->metrics;
                $totalClicks += collect($rows)->sum('clicks');
                $totalImpressions += collect($rows)->sum('impressions');
            }
        }

        return response()->json([
            'total_clicks' => $totalClicks,
            'total_impressions' => $totalImpressions,
            'properties_count' => $propertyCount,
            'reports_count' => $user->reports()->count(),
            'ready_reports' => $user->reports()->ready()->count(),
            'properties' => $properties->map(fn ($p) => [
                'id' => $p->id,
                'display_name' => $p->display_name_or_url,
                'site_url' => $p->site_url,
                'last_synced_at' => $p->last_synced_at,
            ]),
        ]);
    }
}
