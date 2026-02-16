<?php

namespace App\Services;

use App\Models\Report;
use App\Repositories\Contracts\OpenAILogRepositoryInterface;
use App\Repositories\Contracts\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    public function __construct(
        protected SettingsRepositoryInterface $settingsRepository,
        protected OpenAILogRepositoryInterface $openaiLogRepository,
    ) {}

    public function analyzeReport(Report $report): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $apiKey = $this->settingsRepository->get('openai_api_key');
        if (empty($apiKey)) {
            Log::warning('OpenAI API key not configured');

            return null;
        }

        $model = $this->settingsRepository->get('openai_model', 'gpt-4o-mini');
        $temperature = (float) $this->settingsRepository->get('openai_temperature', '0.3');
        $maxTokens = (int) $this->settingsRepository->get('openai_max_tokens', '1000');

        $startTime = microtime(true);
        $logData = [
            'user_id' => $report->user_id,
            'report_id' => $report->id,
            'model' => $model,
        ];

        try {
            if ($this->isRateLimited($report->user_id)) {
                Log::info("OpenAI rate limit reached for user {$report->user_id}");

                return null;
            }

            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->getSystemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildUserPayload($report),
                        ],
                    ],
                ]);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                $this->logUsage(array_merge($logData, [
                    'response_time_ms' => $responseTime,
                    'status' => 'failed',
                    'error_message' => $response->body(),
                ]));

                Log::error('OpenAI API call failed', ['response' => $response->body()]);

                return null;
            }

            $body = $response->json();
            $usage = $body['usage'] ?? [];

            $this->logUsage(array_merge($logData, [
                'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
                'completion_tokens' => $usage['completion_tokens'] ?? 0,
                'total_tokens' => $usage['total_tokens'] ?? 0,
                'cost_estimate' => $this->estimateCost($model, $usage),
                'response_time_ms' => $responseTime,
                'status' => 'success',
                'raw_response' => $body,
            ]));

            $content = $body['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                return null;
            }

            $parsed = json_decode($content, true);
            $this->storeAnalysis($report, $parsed, $body);

            return $parsed;
        } catch (\Exception $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->logUsage(array_merge($logData, [
                'response_time_ms' => $responseTime,
                'status' => $e instanceof \Illuminate\Http\Client\ConnectionException ? 'timeout' : 'failed',
                'error_message' => $e->getMessage(),
            ]));

            Log::error('OpenAI analysis failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settingsRepository->get('openai_enabled', false);
    }

    protected function getSystemPrompt(): string
    {
        return 'You are a public issue analysis assistant specialized in Malaysian local governance, community reports, infrastructure issues, safety, and viral public matters. You analyze reports objectively and provide structured risk assessment. You must respond with valid JSON only. IMPORTANT: All text values in your response (summary, recommended_action, related_current_issue) MUST be written in Bahasa Malaysia (Malay language).';
    }

    protected function buildUserPayload(Report $report): string
    {
        $payload = json_encode([
            'title' => $report->title,
            'category' => $report->category,
            'location' => $report->location,
            'description' => $report->description,
            'date' => $report->incident_date->format('Y-m-d'),
        ]);

        return "Analyze this public report and provide your assessment as JSON with these exact keys: summary, risk_level (Low/Medium/High/Critical), urgency_score (1-10), recommended_action, related_current_issue, spam_probability (0.00-1.00), confidence_score (0.00-1.00).\n\nReport data:\n{$payload}";
    }

    protected function storeAnalysis(Report $report, array $parsed, array $rawResponse): void
    {
        $riskMap = [
            'Low' => 'low',
            'Medium' => 'medium',
            'High' => 'high',
            'Critical' => 'critical',
        ];

        $report->update([
            'ai_summary' => $parsed['summary'] ?? null,
            'ai_risk_level' => $riskMap[$parsed['risk_level'] ?? ''] ?? null,
            'ai_urgency_score' => $parsed['urgency_score'] ?? null,
            'ai_recommended_action' => $parsed['recommended_action'] ?? null,
            'ai_related_issue' => $parsed['related_current_issue'] ?? null,
            'ai_spam_probability' => $parsed['spam_probability'] ?? null,
            'ai_confidence_score' => $parsed['confidence_score'] ?? null,
            'ai_raw_response' => $rawResponse,
            'ai_analyzed_at' => now(),
        ]);
    }

    protected function isRateLimited(int $userId): bool
    {
        $dailyLimit = (int) $this->settingsRepository->get('openai_daily_limit', '100');

        $todayCount = $this->openaiLogRepository->getAll(1)->total();

        return $todayCount >= $dailyLimit;
    }

    protected function estimateCost(string $model, array $usage): float
    {
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;

        // Pricing per 1M tokens (approximate, configurable)
        $pricing = [
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
            'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
            'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
            'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
        ];

        $rates = $pricing[$model] ?? $pricing['gpt-4o-mini'];

        return ($promptTokens * $rates['input'] / 1_000_000)
            + ($completionTokens * $rates['output'] / 1_000_000);
    }

    protected function logUsage(array $data): void
    {
        $this->openaiLogRepository->log($data);
    }
}
