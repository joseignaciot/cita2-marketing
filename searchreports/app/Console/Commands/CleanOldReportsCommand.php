<?php

namespace App\Console\Commands;

use App\Models\Report;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldReportsCommand extends Command
{
    protected $signature = 'reports:clean';
    protected $description = 'Remove expired and old report files';

    public function handle(): int
    {
        $reports = Report::expired()->get();

        $deleted = 0;
        foreach ($reports as $report) {
            if ($report->file_path && Storage::disk(config('gsc.reports_disk'))->exists($report->file_path)) {
                Storage::disk(config('gsc.reports_disk'))->delete($report->file_path);
            }
            $report->delete();
            $deleted++;
        }

        $this->info("Cleaned {$deleted} expired reports.");

        return self::SUCCESS;
    }
}
