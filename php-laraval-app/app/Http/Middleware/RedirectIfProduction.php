<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfProduction
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (app()->environment() !== 'local') {
            return abort(403, 'You are not authorized to access this');
        }

        return $next($request);
    }
}
