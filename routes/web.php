<?php

/**
 * Modules/FocusCmsFrontModule/routes/web.php
 */

use Illuminate\Support\Facades\Route;

use Modules\FocusCmsFrontModule\Http\Controllers\PostController;
use Modules\FocusCmsFrontModule\Http\Controllers\TaxonomyController;

use Modules\FocusCmsFrontModule\Services\Taxonomy\TaxonomyRegistry;

use App\Http\Controllers\MaintenanceController;

$multilang = config('app.multilang');

$locales = $multilang
    ? config('app.supported_locales')
    : [config('app.locale')];

$defaultLocale = config('app.locale');

/*
|--------------------------------------------------------------------------
| Validated taxonomies
|--------------------------------------------------------------------------
*/

$taxonomies = app(TaxonomyRegistry::class)->all();

/*
|--------------------------------------------------------------------------
| Localized routes
|--------------------------------------------------------------------------
*/

foreach ($locales as $locale) {

    $prefix = ($multilang && $locale !== $defaultLocale)
        ? $locale
        : '';

    $middleware = ['web'];

    if ($multilang) {
        $middleware[] = 'setLocale';
    }

    $middleware[] =
        \Modules\FocusCmsFrontModule\Http\Middleware\PageCacheMiddleware::class;

    Route::prefix($prefix)
        ->middleware($middleware)
        ->group(function () use (
            $locale,
            $defaultLocale,
            $taxonomies
        ) {

        /*
        |--------------------------------------------------------------------------
        | Home
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [PostController::class,'home']
        )
        ->defaults('locale',$locale)
        ->name("front.home.$locale");

        /*
        |--------------------------------------------------------------------------
        | Taxonomy routes
        |--------------------------------------------------------------------------
        */

        foreach($taxonomies as $taxonomyKey => $taxonomy){

            if(!($taxonomy['route']['enabled'] ?? false)){
                continue;
            }

            $slug = $taxonomy['route']['slug'][$locale]
                ?? $taxonomy['route']['slug'][$defaultLocale];

            Route::get(
                "/{$slug}",
                [TaxonomyController::class,'index']
            )
            ->defaults('locale',$locale)
            ->defaults('taxonomy',$taxonomyKey)
            ->name("taxonomy.$taxonomyKey.index.$locale");

            Route::get(
                "/{$slug}/{term}",
                [TaxonomyController::class,'show']
            )
            ->defaults('locale',$locale)
            ->defaults('taxonomy',$taxonomyKey)
            ->name("taxonomy.$taxonomyKey.show.$locale");
        }

        /*
        |--------------------------------------------------------------------------
        | Post routes
        |--------------------------------------------------------------------------
        */

        $postSlug = config("post.post.route.slug.$locale")
            ?? config("post.post.route.slug.$defaultLocale");

        Route::get(
            "/{$postSlug}/{slug}",
            [PostController::class,'show']
        )
        ->defaults('locale',$locale)
        ->name("post.show.$locale");

    });

}

/*
|--------------------------------------------------------------------------
| Global routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/maintenance',
    [MaintenanceController::class,'index']
)->name('maintenance');