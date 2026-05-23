<?php

namespace App\Console\Commands;

use App\Jobs\FetchGscDataJob;
use App\Models\GscProperty;
use Illuminate\Console\Command;

class FetchGscPropertyCommand extends Command
{
    protected $signature = 'gsc:fetch {property_id : The ID of the GSC property}';
    protected $description = 'Manually trigger a GSC data fetch for a specific property';

    public function handle(): int
    {
        $property = GscProperty::find($this->argument('property_id'));

        if (!$property) {
            $this->error("Property {$this->argument('property_id')} not found.");
            return self::FAILURE;
        }

        FetchGscDataJob::dispatch($property)->onQueue('gsc-fetch');

        $this->info("Fetch job dispatched for property: {$property->display_name_or_url}");

        return self::SUCCESS;
    }
}
