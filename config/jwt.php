<?php

return [

    'secret' => env('JWT_SECRET'),

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
    ],

    'ttl' => env('JWT_TTL', 60),

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    'algo' => env('JWT_ALGO', 'HS256'),

    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'jti',
    ],

    'persistent_claims' => [

    ],

    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    'blacklist_storage' => env('JWT_BLACKLIST_STORAGE', 'cache'),

    'blacklist_cache_key' => env('JWT_BLACKLIST_CACHE_KEY', 'jwt.blacklist'),

];
