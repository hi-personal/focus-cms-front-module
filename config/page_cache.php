<?php

/**
 * focus-cms-front-module/config/page-cache.php
 */

return [
    'enabled' => true,
    'storage_path' => storage_path('page-cache'),
    'cache_routes' => [
        'front.home.*',
        'post.show.*',
        'taxonomy.*',
    ],
    'ignored_routes' => [
        'admin.*',
    ],
    'ignored_query_params' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'fbclid',
    ],
];