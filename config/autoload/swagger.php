<?php

declare(strict_types=1);

return [
    'enable' => true,
    'json_dir' => BASE_PATH . '/storage/swagger',
    'html' => BASE_PATH . '/storage/swagger/index.html',
    'url' => '/swagger',
    'auto_generate' => true,
    'info' => [
        'title' => 'Hyperf PIX Example API',
        'version' => '1.0.0',
        'description' => 'API para saques PIX criado com HyperF 3',
    ],
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
    ],
];
