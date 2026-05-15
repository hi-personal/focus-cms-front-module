<?php

namespace Modules\FocusCmsFrontModule\Services\Taxonomy;

class TaxonomyRegistry
{
    protected array $taxonomies = [];

    public function __construct()
    {
        $this->taxonomies = config('taxonomies', []);

        $this->validate();
    }

    /**
     * Get all validated taxonomies
     */
    public function all(): array
    {
        return $this->taxonomies;
    }

    /**
     * Validate taxonomy configuration
     */
    protected function validate(): void
    {
        $supportedLocales = config('app.supported_locales', []);
        $defaultLocale = config('app.locale');

        $usedSlugs = [];

        foreach ($this->taxonomies as $taxonomyKey => $taxonomy) {

            /*
            |--------------------------------------------------------------------------
            | Required route config
            |--------------------------------------------------------------------------
            */

            if (! isset($taxonomy['route'])) {
                throw new \RuntimeException(
                    "Missing route config in taxonomy [$taxonomyKey]"
                );
            }

            if (! is_bool($taxonomy['route']['enabled'] ?? null)) {
                throw new \RuntimeException(
                    "Invalid route.enabled in taxonomy [$taxonomyKey]"
                );
            }

            if (($taxonomy['route']['enabled'] ?? false) === false) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Slug config required
            |--------------------------------------------------------------------------
            */

            if (! isset($taxonomy['route']['slug'])) {
                throw new \RuntimeException(
                    "Missing route.slug in taxonomy [$taxonomyKey]"
                );
            }

            if (! is_array($taxonomy['route']['slug'])) {
                throw new \RuntimeException(
                    "route.slug must be array in taxonomy [$taxonomyKey]"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate locale slugs
            |--------------------------------------------------------------------------
            */

            foreach ($supportedLocales as $locale) {

                $slug = $taxonomy['route']['slug'][$locale]
                    ?? $taxonomy['route']['slug'][$defaultLocale]
                    ?? null;

                if (! is_string($slug) || trim($slug) === '') {

                    throw new \RuntimeException(
                        "Missing taxonomy slug [$taxonomyKey][$locale]"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | URL-safe slug
                |--------------------------------------------------------------------------
                */

                if (! preg_match('/^[a-z0-9\-]+$/', $slug)) {

                    throw new \RuntimeException(
                        "Invalid taxonomy slug [$slug] in [$taxonomyKey][$locale]"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate slug detection
                |--------------------------------------------------------------------------
                */

                if (isset($usedSlugs[$locale][$slug])) {

                    throw new \RuntimeException(
                        "Duplicate taxonomy slug [$slug] in locale [$locale]"
                    );
                }

                $usedSlugs[$locale][$slug] = $taxonomyKey;
            }
        }
    }
}