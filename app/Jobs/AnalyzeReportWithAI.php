<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeReportWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 120;

    public function __construct(public Report $report) {}

    public function handle(OpenAIService $openAIService): void
    {
        Log::info("Starting AI analysis for report #{$this->report->id}");

        $result = $openAIService->analyzeReport($this->report);

        if ($result) {
            Log::info("AI analysis completed for report #{$this->report->id}");
        } else {
            Log::warning("AI analysis returned no result for report #{$this->report->id}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AI analysis failed for report #{$this->report->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
