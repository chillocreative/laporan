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
        if (! $this->boolSetting('openai_enabled', false)) {
            return;
        }

        // dispatch() requires a running queue worker; dispatchAfterResponse()
        // runs in the same PHP process after the HTTP response is flushed, so
        // shared hosting without a worker still analyzes new reports.
        if ($this->boolSetting('openai_queue_enabled', false)) {
            AnalyzeReportWithAI::dispatch($event->report);
        } else {
            AnalyzeReportWithAI::dispatchAfterResponse($event->report);
        }
    }

    protected function boolSetting(string $key, bool $default): bool
    {
        $value = $this->settingsRepository->get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
