<?php

namespace Modules\FocusCmsFrontModule\Services\PageCache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PageCacheService
{
    /**
     * cache engedélyezve?
     */
    public function enabled(): bool
    {
        return config(
            'module.focuscmsfrontmodule.page_cache.enabled',
            false
        );
    }

    /**
     * cache-elhető request?
     */
    public function isCacheableRequest(Request $request): bool
    {
        /*
         * csak GET
         */
        if (!$request->isMethod('GET')) {
            return false;
        }

        /*
         * auth user bypass
         */
        if (auth()->check()) {

            return false;

        }

        /*
         * ajax bypass
         */
        if ($request->ajax()) {

            return false;

        }

        return true;
    }

    /**
     * route cache-elhető?
     */
    public function isCacheableRoute(Request $request): bool
    {
        $routeName =
            $request->route()?->getName();

        if (empty($routeName)) {

            return false;

        }

        /*
         * ignored routes
         */
        foreach (
            config(
                'module.focuscmsfrontmodule.page_cache.ignored_routes',
                []
            ) as $pattern
        ) {

            if (Str::is($pattern, $routeName)) {

                return false;

            }

        }

        /*
         * cache routes
         */
        foreach (
            config(
                'module.focuscmsfrontmodule.page_cache.cache_routes',
                []
            ) as $pattern
        ) {

            if (Str::is($pattern, $routeName)) {

                return true;

            }

        }

        return false;
    }

    /**
     * cache path
     */
    // public function getCachePath(Request $request): string
    // {
    //     $path =
    //         trim($request->path(), '/');

    //     /*
    //      * homepage
    //      */
    //     if (empty($path)) {

    //         $path = 'home';

    //     }

    //     /*
    //      * query params
    //      */
    //     $query =
    //         $request->query();

    //     $ignored =
    //         config(
    //             'module.focuscmsfrontmodule.page_cache.ignored_query_params',
    //             []
    //         );

    //     foreach ($ignored as $param) {

    //         unset($query[$param]);

    //     }

    //     if (!empty($query)) {

    //         ksort($query);

    //         $path .= '_'.md5(
    //             http_build_query($query)
    //         );

    //     }

    //     return rtrim(
    //         config(
    //             'module.focuscmsfrontmodule.page_cache.storage_path'
    //         ),
    //         '/'
    //     )
    //     .'/'
    //     .md5($path)
    //     .'.html';
    // }

    public function getCachePath(
        Request $request
    ): string {

        $path =
            trim(
                $request->path(),
                '/'
            );

        /*
        |--------------------------------------------------------------------------
        | Homepage
        |--------------------------------------------------------------------------
        */

        if (empty($path)) {

            return
                rtrim(
                    config(
                        'module.focuscmsfrontmodule.page_cache.storage_path'
                    ),
                    '/'
                )
                . '/index.html';

        }

        /*
        |--------------------------------------------------------------------------
        | Query params
        |--------------------------------------------------------------------------
        */

        $query =
            $request->query();

        $ignored =
            config(
                'module.focuscmsfrontmodule.page_cache.ignored_query_params',
                []
            );

        foreach ($ignored as $param) {

            unset($query[$param]);

        }

        /*
        |--------------------------------------------------------------------------
        | Query hash
        |--------------------------------------------------------------------------
        */

        if (!empty($query)) {

            ksort($query);

            $path .= '/'
                . md5(
                    http_build_query($query)
                );

        }

        /*
        |--------------------------------------------------------------------------
        | Final path
        |--------------------------------------------------------------------------
        */

        return
            rtrim(
                config(
                    'module.focuscmsfrontmodule.page_cache.storage_path'
                ),
                '/'
            )
            . '/'
            . $path
            . '/index.html';
    }

    // public function getCachePathByUrl(
    //     string $url
    // ): string {

    //     $path =
    //         parse_url(
    //             $url,
    //             PHP_URL_PATH
    //         );

    //     $path = trim($path, '/');

    //     if (empty($path)) {
    //         $path = 'home';
    //     }

    //     $storage_path = config('module.focuscmsfrontmodule.page_cache.storage_path');

    //     if (! File::exists($storage_path) ) {
    //      //   File::makeDirectory($storage_path, 0777, true);
    //     }

    //     return rtrim($storage_path, '/')
    //         . '/'
    //         . md5($path)
    //         . '.html';
    // }


    public function getCachePathByUrl(
        string $url
    ): string {

        $path =
            parse_url(
                $url,
                PHP_URL_PATH
            );

        $path =
            trim($path, '/');

        /*
        |--------------------------------------------------------------------------
        | Homepage
        |--------------------------------------------------------------------------
        */

        if (empty($path)) {

            return
                rtrim(
                    config(
                        'module.focuscmsfrontmodule.page_cache.storage_path'
                    ),
                    '/'
                )
                . '/index.html';

        }

        return
            rtrim(
                config(
                    'module.focuscmsfrontmodule.page_cache.storage_path'
                ),
                '/'
            )
            . '/'
            . $path
            . '/index.html';
    }



    /**
     * cache létezik?
     */
    public function has(Request $request): bool
    {
        return File::exists(
            $this->getCachePath($request)
        );
    }

    /**
     * cache tartalom
     */
    public function get(Request $request): string
    {
        return File::get(
            $this->getCachePath($request)
        );
    }

    /**
     * cache mentés
     */
    public function put(
        Request $request,
        string $content
    ): void {

        $path =
            $this->getCachePath($request);

        File::ensureDirectoryExists(
            dirname($path)
        );

        File::put(
            $path,
            $content
        );
    }

    public function putByUrl(
        string $url,
        string $content
    ): void {
        $path =
            $this->getCachePathByUrl(
                $url
            );

        File::ensureDirectoryExists(
            dirname($path)
        );

        try {

            File::ensureDirectoryExists(
                dirname($path)
            );

            File::put(
                $path,
                $content
            );

            clearstatcache();
        } catch (\Throwable $e) {

            Log::error('CacheWriteFailed', [
                'path' => $path,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    protected function buildCachePathFromUrl(
        string $url
    ): string {

        $path =
            parse_url(
                $url,
                PHP_URL_PATH
            );

        $path =
            trim($path, '/');

        /*
        * homepage
        */
        if (empty($path)) {

            $path = 'home';

        }

        return
            rtrim(
                config(
                    'module.focuscmsfrontmodule.page_cache.storage_path'
                ),
                '/'
            )
            . '/'
            . md5($path)
            . '.html';
    }
}