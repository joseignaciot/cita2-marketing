<?php

namespace App\Console\Commands;

use App\Jobs\SyncGscPropertiesJob;
use App\Models\User;
use Illuminate\Console\Command;

class SyncAllGscPropertiesCommand extends Command
{
    protected $signature = 'gsc:sync-all';
    protected $description = 'Dispatch sync jobs for all users with active GSC properties';

    public function handle(): int
    {
        $users = User::whereHas('gscProperties', fn ($q) => $q->where('is_active', true))->get();

        $this->info("Dispatching sync for {$users->count()} users...");

        $users->each(fn ($user) => SyncGscPropertiesJob::dispatch($user)->onQueue('gsc-fetch'));

        $this->info('Done.');

        return self::SUCCESS;
    }
}
