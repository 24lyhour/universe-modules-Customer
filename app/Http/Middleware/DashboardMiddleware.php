<?php

namespace Modules\Customer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Menu items are registered in CustomerServiceProvider.
     * This middleware can be used for other Customer-specific request handling.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
