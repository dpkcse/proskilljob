<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminActivityLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! auth('admin')->check()) {
            return $response;
        }

        if ($request->is('admin/login') || $request->is('admin/logout')) {
            return $response;
        }

        $admin = auth('admin')->user();

        $payload = $request->except([
            '_token',
            'password',
            'password_confirmation',
            'current_password',
        ]);

        ActivityLog::create([
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'roles' => $admin->getRoleNames()->implode(', '),
            'method' => $request->method(),
            'route_name' => optional($request->route())->getName(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
        ]);

        return $response;
    }
}
