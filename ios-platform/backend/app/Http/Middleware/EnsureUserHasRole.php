<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        if (! method_exists($user, 'hasAnyRole')) {
            return response()->json([
                'success' => false,
                'message' => __('auth.forbidden'),
            ], 403);
        }

        if (! $user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.role_required', ['roles' => implode(', ', $roles)]),
            ], 403);
        }

        return $next($request);
    }
}
