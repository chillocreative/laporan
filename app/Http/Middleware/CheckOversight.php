<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows view-only oversight: Admin, Super Admin or any user flagged
 * `can_view_all`, plus any of the extra roles passed as parameters.
 */
class CheckOversight
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Tidak disahkan.');
        }

        if (! $user->canViewAll() && ! $user->hasAnyRole($roles)) {
            abort(403, 'Tidak dibenarkan. Peranan tidak mencukupi.');
        }

        return $next($request);
    }
}
