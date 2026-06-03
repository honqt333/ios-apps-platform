<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: ?lang= query → X-Locale header → Accept-Language → user pref → app default
        $locale = $request->query('lang')
            ?? $request->header('X-Locale')
            ?? $this->fromAcceptLanguage($request)
            ?? optional($request->user())->locale
            ?? config('app.locale');

        if (! in_array($locale, ['en', 'ar'], true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        // Share current locale with the response
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    protected function fromAcceptLanguage(Request $request): ?string
    {
        $accept = $request->header('Accept-Language');
        if (! $accept) {
            return null;
        }

        if (stripos($accept, 'ar') !== false) {
            return 'ar';
        }
        if (stripos($accept, 'en') !== false) {
            return 'en';
        }
        return null;
    }
}
