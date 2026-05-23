<?php

namespace App\Console\Commands;

use App\Models\GscDataCache;
use Illuminate\Console\Command;

class CleanExpiredCacheCommand extends Command
{
    protected $signature = 'reports:clean-expired-cache';
    protected $description = 'Remove expired GSC data cache entries';

    public function handle(): int
    {
        $deleted = GscDataCache::expired()->delete();

        $this->info("Deleted {$deleted} expired cache entries.");

        return self::SUCCESS;
    }
}
