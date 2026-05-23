<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportTemplateEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(private Report $report) {}

    public function handle(ReportTemplateEngine $engine): void
    {
        try {
            $engine->generateReport($this->report);
        } catch (\Exception $e) {
            Log::error("Report generation failed for report {$this->report->id}: " . $e->getMessage());
            $this->report->update(['status' => 'failed']);
            throw $e;
        }
    }
}
