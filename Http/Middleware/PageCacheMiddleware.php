<?php

namespace Modules\FocusCmsFrontModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\FocusCmsFrontModule\Services\PageCache\PageCacheService;

class PageCacheMiddleware
{
    protected PageCacheService $cache;

    public function __construct(
        PageCacheService $cache
    ) {
        $this->cache = $cache;
    }

    /**
     * Handle
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | CACHE WARMUP BYPASS
        |--------------------------------------------------------------------------
        */

        if (
            $request->header('X-Cache-Warmup')
        ) {

            return $next($request);

        }

        /*
         * cache disabled
         */
        if (!$this->cache->enabled()) {

            return $next($request);

        }

        /*
         * request bypass
         */
        if (
            !$this->cache->isCacheableRequest($request)
        ) {

            return $next($request);

        }

        /*
         * route bypass
         */
        if (
            !$this->cache->isCacheableRoute($request)
        ) {

            return $next($request);

        }

        /*
         * cache hit
         */
        if ($this->cache->has($request)) {

            return response(
                $this->cache->get($request),
                200,
                [
                    'X-Page-Cache' => 'HIT',
                    'Content-Type' => 'text/html; charset=UTF-8',
                ]
            );

        }

        /*
         * normal render
         */
        $response =
            $next($request);

        /*
         * only successful html response
         */
        if (
            $response->getStatusCode() === 200
            &&
            str_contains(
                $response->headers->get(
                    'Content-Type',
                    ''
                ),
                'text/html'
            )
        ) {

            $this->cache->put(
                $request,
                $response->getContent()
            );

            $response->headers->set(
                'X-Page-Cache',
                'MISS'
            );

        }

        return $response;
    }
}