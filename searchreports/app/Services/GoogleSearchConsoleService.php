<?php

namespace App\Services;

use App\Models\GscDataCache;
use App\Models\GscProperty;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    public function getClient(User $user): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessType('offline');

        $token = [
            'access_token' => $user->google_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => $user->google_token_expires_at
                ? $user->google_token_expires_at->diffInSeconds(now(), false)
                : 0,
        ];

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired() && $user->google_refresh_token) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);

            if (!isset($newToken['error'])) {
                $user->update([
                    'google_token' => $newToken['access_token'],
                    'google_token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);

                $client->setAccessToken($newToken);
            }
        }

        return $client;
    }

    public function listProperties(User $user): array
    {
        $client = $this->getClient($user);
        $service = new SearchConsole($client);

        $sites = $service->sites->listSites();

        return collect($sites->getSiteEntry() ?? [])->map(fn ($site) => [
            'site_url' => $site->getSiteUrl(),
            'permission_level' => $site->getPermissionLevel(),
        ])->toArray();
    }

    public function syncUserProperties(User $user): void
    {
        $properties = $this->listProperties($user);

        foreach ($properties as $prop) {
            $siteUrl = $prop['site_url'];
            $siteType = str_starts_with($siteUrl, 'sc-domain:') ? 'domain' : 'url';

            GscProperty::updateOrCreate(
                ['user_id' => $user->id, 'site_url' => $siteUrl],
                [
                    'site_type' => $siteType,
                    'permission_level' => $prop['permission_level'],
                    'is_active' => true,
                    'last_synced_at' => now(),
                    'display_name' => str_replace('sc-domain:', '', $siteUrl),
                ]
            );
        }

        $syncedUrls = collect($properties)->pluck('site_url');
        GscProperty::where('user_id', $user->id)
            ->whereNotIn('site_url', $syncedUrls)
            ->update(['is_active' => false]);
    }

    public function fetchSearchAnalytics(GscProperty $property, array $params): array
    {
        $cacheKey = $this->cacheKey($property->id, $params);
        $cached = GscDataCache::where('property_id', $property->id)->valid()->where('dimensions', json_encode($params['dimensions'] ?? []))->where('date_range_start', $params['startDate'])->where('date_range_end', $params['endDate'])->first();

        if ($cached) {
            return $cached->metrics;
        }

        $rows = $this->fetchFromApi($property, $params);

        GscDataCache::create([
            'property_id' => $property->id,
            'date_range_start' => $params['startDate'],
            'date_range_end' => $params['endDate'],
            'dimensions' => $params['dimensions'] ?? [],
            'metrics' => $rows,
            'query_count' => count($rows),
            'fetched_at' => now(),
            'expires_at' => now()->addHours(config('gsc.cache_hours', 6)),
        ]);

        return $rows;
    }

    private function fetchFromApi(GscProperty $property, array $params, int $attempt = 0): array
    {
        try {
            $client = $this->getClient($property->user);
            $service = new SearchConsole($client);

            $request = new SearchAnalyticsQueryRequest();
            $request->setStartDate($params['startDate']);
            $request->setEndDate($params['endDate']);
            $request->setDimensions($params['dimensions'] ?? ['query']);
            $request->setRowLimit($params['rowLimit'] ?? config('gsc.row_limit'));

            if (!empty($params['filters'])) {
                $request->setDimensionFilterGroups($params['filters']);
            }

            $response = $service->searchanalytics->query($property->site_url, $request);

            return collect($response->getRows() ?? [])->map(fn ($row) => [
                'keys' => $row->getKeys(),
                'clicks' => $row->getClicks(),
                'impressions' => $row->getImpressions(),
                'ctr' => $row->getCtr(),
                'position' => $row->getPosition(),
            ])->toArray();
        } catch (\Exception $e) {
            if ($e->getCode() === 429 && $attempt < config('gsc.max_retries')) {
                usleep(config('gsc.retry_delay_ms') * (2 ** $attempt) * 1000);
                return $this->fetchFromApi($property, $params, $attempt + 1);
            }

            if ($e->getCode() === 403) {
                $property->update(['is_active' => false]);
                Log::warning("GSC property {$property->id} marked inactive: permission denied");
            }

            Log::error("GSC fetch failed for property {$property->id}: " . $e->getMessage());

            throw $e;
        }
    }

    private function cacheKey(int $propertyId, array $params): string
    {
        return "gsc:{$propertyId}:" . md5(json_encode($params));
    }

    public function fetchSitemaps(GscProperty $property): array
    {
        $client = $this->getClient($property->user);
        $service = new SearchConsole($client);

        $sitemaps = $service->sitemaps->listSitemaps($property->site_url);

        return collect($sitemaps->getSitemap() ?? [])->map(fn ($s) => [
            'path' => $s->getPath(),
            'lastSubmitted' => $s->getLastSubmitted(),
            'isPending' => $s->getIsPending(),
            'isSitemapsIndex' => $s->getIsSitemapsIndex(),
            'warnings' => $s->getWarnings(),
            'errors' => $s->getErrors(),
        ])->toArray();
    }

    public function fetchIndexCoverage(GscProperty $property): array
    {
        $params = [
            'startDate' => now()->subDays(28)->toDateString(),
            'endDate' => now()->toDateString(),
            'dimensions' => ['page'],
            'rowLimit' => 1000,
        ];

        return $this->fetchSearchAnalytics($property, $params);
    }
}
