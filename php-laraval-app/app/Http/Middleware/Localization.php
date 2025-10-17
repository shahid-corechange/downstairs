<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $locale = $request->getPreferredLanguage(config('app.locales'));
        } else {
            $user = Auth::user();
            $locale = $user ? $user->info->language :
                $request->getPreferredLanguage(config('app.locales'));
        }

        // set laravel localization
        app()->setLocale($locale);

        return $next($request);
    }
}
