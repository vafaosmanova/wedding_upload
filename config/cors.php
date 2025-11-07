<?php

return [
'paths' => ['api/*', 'sanctum/csrf-cookie', '/register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
    'http://wedding-upload.test',
    'https://wedding-upload.test',
    'http://localhost:5173',
    'http://[::1]:5173',
    'https://vite.wedding-upload.test:5174',
],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
