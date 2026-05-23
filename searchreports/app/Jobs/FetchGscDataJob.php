<?php

namespace App\Jobs;

use App\Models\GscProperty;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchGscDataJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(
        private GscProperty $property,
        private array $params = []
    ) {}

    public function uniqueId(): string
    {
        return 'fetch-gsc-' . $this->property->id;
    }

    public function handle(GoogleSearchConsoleService $gscService): void
    {
        if (!$this->property->is_active) {
            return;
        }

        $defaultParams = [
            'startDate' => now()->subDays(28)->toDateString(),
            'endDate' => now()->toDateString(),
            'dimensions' => ['query', 'page'],
        ];

        $params = array_merge($defaultParams, $this->params);

        try {
            $gscService->fetchSearchAnalytics($this->property, $params);

            $this->property->update(['last_synced_at' => now()]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch GSC data for property {$this->property->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
