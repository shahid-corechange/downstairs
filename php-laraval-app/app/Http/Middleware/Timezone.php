<?php

namespace App\Http\Middleware;

use Cache as CacheFacade;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Timezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = request()->header('X-Timezone', 'Europe/Stockholm');
        $user = $request->user();

        if ($timezone && $user && $user->info->timezone !== $timezone) {
            $user->info->timezone = $timezone;
            $user->info->save();
            CacheFacade::forget("user_{$user->id}");
        }

        return $next($request);
    }
}
