<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@hyperf-pix.com'),
        'name' => env('MAIL_FROM_NAME', 'Hyperf PIX'),
    ],
    'host' => env('MAIL_HOST', 'localhost'),
    'port' => env('MAIL_PORT', 587),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
];
