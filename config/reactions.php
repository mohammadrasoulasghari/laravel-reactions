<?php

return [

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Reaction model shipped with this package.
     */
    'table_name' => 'reactions',
    'cache' => [
        'summary' => [
            'enabled' => env('REACTIONS_SUMMARY_CACHE_ENABLED', true), //
            'driver' => env('REACTIONS_SUMMARY_CACHE_DRIVER', 'redis'),
            'ttl'=> env('REACTIONS_SUMMARY_CACHE_TTL', 60),
        ]
    ]
];
