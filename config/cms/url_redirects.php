<?php

return [

    'enabled' => true,

    /**
     * Register middleware on this group (typically your public frontend stack).
     */
    'web_middleware_group' => 'web',

    'middleware_enabled' => true,

    'log_404s' => true,

    /**
     * Path prefixes to skip (no redirect lookup, no 404 logging). CMS admin is added automatically.
     */
    'exclude_path_prefixes' => [
        'api',
        'sanctum',
        'livewire',
    ],

    /**
     * Public URL pattern for detail pages — use {slug} placeholder.
     * Adjust to match your frontend routes (e.g. /blogs/{slug} vs /blog/{slug}).
     */
    'slug_patterns' => [
        'blog' => [
            'detail' => '/blog/{slug}',
        ],
        'career' => [
            'detail' => '/careers/{slug}',
        ],
    ],

    /**
     * When a blog or career is deleted, add a redirect from the old detail URL to this path (301).
     * Set status_code to 410 and target_url to null to emit “Gone” instead.
     */
    'on_delete' => [
        'blog' => [
            'enabled' => true,
            'target_url' => '/blog',
            'status_code' => 301,
        ],
        'career' => [
            'enabled' => true,
            'target_url' => '/careers',
            'status_code' => 301,
        ],
    ],

];
