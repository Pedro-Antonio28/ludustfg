<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRoleGuard
{
    public function handle(Request $request, Closure $next, $guard)
    {
        if (!Auth::guard($guard)->check()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        Auth::shouldUse($guard);

        return $next($request);
    }
}

