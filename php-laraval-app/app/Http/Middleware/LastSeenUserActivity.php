<?php

namespace App\Http\Middleware;

use App\Jobs\UpdateLastSeenJob;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LastSeenUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Update last seen if it's more than 5 minutes ago
        if ($user && (! $user->last_seen || $user->last_seen->diffInMinutes(now()) >= 5)) {
            UpdateLastSeenJob::dispatchAfterResponse($user);
        }

        return $next($request);
    }
}
