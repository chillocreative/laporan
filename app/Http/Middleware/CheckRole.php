<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Tidak disahkan.');
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Tidak dibenarkan. Peranan tidak mencukupi.');
        }

        return $next($request);
    }
}
