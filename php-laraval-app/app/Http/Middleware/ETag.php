<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ETag
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->isSuccessful()) {
            $content = $response->getOriginalContent();

            if (! is_array($content)) {
                return $response;
            }

            $etag = md5(json_encode($content['data']));
            $requestIfNoneMatch = $request->headers->get('If-None-Match');

            if ($requestIfNoneMatch && $requestIfNoneMatch === $etag) {
                $response->setNotModified();
            }

            if (! isset($content['meta'])) {
                $content['meta'] = [];
            }

            $content['meta']['etag'] = $etag;

            $response->setEtag($etag);
            $response->setContent(json_encode($content));
        }

        return $response;
    }
}
