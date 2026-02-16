<?php

namespace App\Services;

use App\Enums\SecurityEventType;
use App\Enums\SecuritySeverity;
use App\Repositories\Contracts\SecurityLogRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class SecurityLogService
{
    public function __construct(
        protected SecurityLogRepositoryInterface $securityLogRepository,
    ) {}

    public function logFailedLogin(string $email): void
    {
        $this->securityLogRepository->log([
            'user_id' => null,
            'event_type' => SecurityEventType::FailedLogin->value,
            'severity' => SecuritySeverity::Medium->value,
            'description' => "Failed login attempt for email: {$email}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logCaptchaFailure(): void
    {
        $this->securityLogRepository->log([
            'user_id' => Auth::id(),
            'event_type' => SecurityEventType::CaptchaFailure->value,
            'severity' => SecuritySeverity::Low->value,
            'description' => 'CAPTCHA verification failed',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logRateLimitHit(string $endpoint): void
    {
        $this->securityLogRepository->log([
            'user_id' => Auth::id(),
            'event_type' => SecurityEventType::RateLimitHit->value,
            'severity' => SecuritySeverity::Medium->value,
            'description' => "Rate limit hit on: {$endpoint}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logSuspiciousPayload(string $description, ?array $payload = null): void
    {
        $this->securityLogRepository->log([
            'user_id' => Auth::id(),
            'event_type' => SecurityEventType::SuspiciousPayload->value,
            'severity' => SecuritySeverity::High->value,
            'description' => $description,
            'payload' => $payload,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logUnauthorizedAccess(string $description): void
    {
        $this->securityLogRepository->log([
            'user_id' => Auth::id(),
            'event_type' => SecurityEventType::UnauthorizedAccess->value,
            'severity' => SecuritySeverity::High->value,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getAll(int $perPage = 25)
    {
        return $this->securityLogRepository->getAll($perPage);
    }

    public function getRecentSuspiciousCount(int $hours = 24): int
    {
        return $this->securityLogRepository->getRecentSuspicious($hours);
    }
}
