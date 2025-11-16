<?php

return [
'paths' => ['api/*', 'sanctum/csrf-cookie', '/register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
    'https://wedding-upload.test',
    'https://vite.wedding-upload.test:5173',
],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
