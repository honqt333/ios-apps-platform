<?php

namespace App\Http\Middleware;

use App\Services\Audit\Contracts\AuditServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditActivity
{
    public function __construct(protected AuditServiceInterface $audit)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Audit admin writes
        if ($request->is('api/admin/*') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            try {
                $this->audit->log(
                    sprintf('%s %s', $request->method(), $request->path()),
                    null,
                    $request->user(),
                    [
                        'status'  => $response->getStatusCode(),
                        'payload' => $this->safePayload($request),
                    ]
                );
            } catch (\Throwable $e) {
                // never let audit break the request
            }
        }

        return $response;
    }

    protected function safePayload(Request $request): array
    {
        $blacklist = ['password', 'password_confirmation', 'current_password', 'token'];
        $data = $request->all();
        array_walk_recursive($data, function (&$value, $key) use ($blacklist) {
            if (in_array(strtolower($key), $blacklist, true)) {
                $value = '***';
            }
        });
        return $data;
    }
}
