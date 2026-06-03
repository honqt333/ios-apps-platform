<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        if (! method_exists($user, 'can') || ! $user->can($permission)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.permission_required', ['permission' => $permission]),
            ], 403);
        }

        return $next($request);
    }
}
