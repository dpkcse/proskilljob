<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceAppUrl
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the request's host matches the application's configured URL.
     * If they do not match, it redirects the request to the application's URL with a 301 status code.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $appUrl = config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $requestHost = $request->getHost();

        if ($appHost && $requestHost !== $appHost) {
            $redirectUrl = $appUrl.$request->getRequestUri();

            return redirect()->to($redirectUrl, 301);
        }

        return $next($request);
    }
}
