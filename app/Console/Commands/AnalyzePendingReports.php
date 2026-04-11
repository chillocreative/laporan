<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Services\OpenAIService;
use Illuminate\Console\Command;

class AnalyzePendingReports extends Command
{
    protected $signature = 'reports:analyze-pending
                            {--limit= : Maximum number of reports to process in this run}
                            {--sleep=1 : Seconds to wait between API calls}';

    protected $description = 'Run AI analysis on all reports that have not been analyzed yet';

    public function handle(OpenAIService $openAIService): int
    {
        if (! $openAIService->isEnabled()) {
            $this->error('OpenAI integration is disabled. Enable it in Tetapan first.');

            return self::FAILURE;
        }

        $pending = Report::whereNull('ai_analyzed_at')->count();

        if ($pending === 0) {
            $this->info('No pending reports to analyze.');

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $sleep = max(0, (int) $this->option('sleep'));

        $query = Report::whereNull('ai_analyzed_at')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
            $this->info("Found {$pending} pending reports. Processing up to {$limit}.");
        } else {
            $this->info("Found {$pending} pending reports. Processing all.");
        }

        $reports = $query->get();
        @set_time_limit(0);

        $bar = $this->output->createProgressBar($reports->count());
        $bar->start();

        $succeeded = 0;
        $failed = 0;

        foreach ($reports as $report) {
            try {
                $result = $openAIService->analyzeReport($report);
                if ($result) {
                    $succeeded++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("Report #{$report->id}: {$e->getMessage()}");
            }

            $bar->advance();

            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Succeeded: {$succeeded}, Failed: {$failed}");

        $remaining = Report::whereNull('ai_analyzed_at')->count();
        if ($remaining > 0) {
            $this->warn("{$remaining} reports still pending. Run again to continue.");
        }

        return self::SUCCESS;
    }
}
