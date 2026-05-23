<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGscPropertiesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(private User $user) {}

    public function uniqueId(): string
    {
        return 'sync-gsc-' . $this->user->id;
    }

    public function handle(GoogleSearchConsoleService $gscService): void
    {
        try {
            $gscService->syncUserProperties($this->user);
        } catch (\Exception $e) {
            Log::error("Failed to sync GSC properties for user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
