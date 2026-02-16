<?php

namespace App\Enums;

enum SecurityEventType: string
{
    case FailedLogin = 'failed_login';
    case CaptchaFailure = 'captcha_failure';
    case RateLimitHit = 'rate_limit_hit';
    case SuspiciousPayload = 'suspicious_payload';
    case UnauthorizedAccess = 'unauthorized_access';

    public function label(): string
    {
        return match ($this) {
            self::FailedLogin => 'Failed Login',
            self::CaptchaFailure => 'CAPTCHA Failure',
            self::RateLimitHit => 'Rate Limit Hit',
            self::SuspiciousPayload => 'Suspicious Payload',
            self::UnauthorizedAccess => 'Unauthorized Access',
        };
    }
}
