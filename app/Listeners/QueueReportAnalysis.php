<?php

namespace App\Listeners;

use App\Events\ReportCreated;
use App\Jobs\AnalyzeReportWithAI;
use App\Repositories\Contracts\SettingsRepositoryInterface;

class QueueReportAnalysis
{
    public function __construct(protected SettingsRepositoryInterface $settingsRepository) {}

    public function handle(ReportCreated $event): void
    {
        $enabled = (bool) $this->settingsRepository->get('openai_enabled', false);
        if (! $enabled) {
            return;
        }

        $useQueue = (bool) $this->settingsRepository->get('openai_queue_enabled', true);

        if ($useQueue) {
            AnalyzeReportWithAI::dispatch($event->report);
        } else {
            AnalyzeReportWithAI::dispatchSync($event->report);
        }
    }
}
