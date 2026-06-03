<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        if (! method_exists($user, 'canAccessAdmin') || ! $user->canAccessAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.forbidden_admin'),
            ], 403);
        }

        return $next($request);
    }
}
