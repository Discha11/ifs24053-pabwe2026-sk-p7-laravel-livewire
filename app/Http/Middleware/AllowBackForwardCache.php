<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowBackForwardCache
{
    /**
     * Allow browser back/forward cache on GET page responses by removing no-store.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->hasHeader('X-Livewire')) {
            $cacheControl = (string) $response->headers->get('Cache-Control');

            if (str_contains($cacheControl, 'no-store')) {
                $response->headers->set('Cache-Control', 'no-cache, must-revalidate, max-age=0, private');
            }
        }

        return $response;
    }
}
