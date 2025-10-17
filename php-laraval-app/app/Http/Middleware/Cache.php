<?php

namespace App\Http\Middleware;

use Cache as CacheFacade;
use Closure;
use Illuminate\Http\Request;

class Cache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|string[]  $tag
     */
    public function handle(Request $request, Closure $next, string|array $tag, int $ttl = null)
    {
        $key = $this->makeCacheKey($request);
        $tags = is_array($tag) ? $tag : explode('|', $tag);

        if ($request->method() === 'GET') {
            $data = CacheFacade::tags($tags)->get($key);

            if (! is_null($data)) {
                return response()->json($data);
            }

            $response = $next($request);

            if ($response->isSuccessful()) {
                $content = $response->getOriginalContent();

                if (is_array($content)) {
                    $ttl = $ttl ?? config('downstairs.cache.ttl');
                    CacheFacade::tags($tags)->put($key, $content, $ttl);
                }
            }

            return $response;
        }

        $response = $next($request);
        CacheFacade::tags($tags)->flush();

        return $response;
    }

    /**
     * Make the cache key from the given request path and query string.
     */
    protected function makeCacheKey(Request $request): string
    {
        $locale = app()->getLocale();
        $timezone = request()->header('X-Timezone');
        $user = $request->user();
        $query = $request->getQueryString();
        $key = $request->path().($query ? '?'.$query : '');
        $key .= $user ? '|'.$user->id : '';
        $key .= $locale ? '|'.$locale : '';

        if ($timezone) {
            $key .= '|'.$timezone;
        } elseif ($user && $user->info->timezone) {
            $key .= '|'.$user->info->timezone;
        } else {
            $key .= '|UTC';
        }

        return hash('sha256', $key);
    }
}
