<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    protected array $suspiciousPatterns = [
        '/<script\b[^>]*>/i',
        '/javascript\s*:/i',
        '/on\w+\s*=\s*["\']?/i',
        '/\bUNION\b.*\bSELECT\b/i',
        '/\bDROP\b.*\bTABLE\b/i',
        '/\bINSERT\b.*\bINTO\b/i',
        '/\bDELETE\b.*\bFROM\b/i',
        '/--\s*$/m',
        '/\/\*.*\*\//s',
    ];

    public function __construct(protected SecurityLogService $securityLogService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        foreach ($input as $key => $value) {
            if (is_string($value) && $this->isSuspicious($value)) {
                $this->securityLogService->logSuspiciousPayload(
                    "Suspicious input detected in field: {$key}",
                    ['field' => $key, 'value_preview' => substr($value, 0, 200)]
                );
            }
        }

        return $next($request);
    }

    protected function isSuspicious(string $value): bool
    {
        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}
