<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::get('/', function () {
    return [
        'name' => 'Hyperf PIX Example API',
        'version' => '1.0.0',
        'description' => 'API para saques PIX criado com HyperF 3',
        'endpoints' => [
            'GET /account/{accountId}' => 'Get account details',
            'GET /account/{accountId}/balance' => 'Get account balance',
            'POST /account/{accountId}/balance/withdraw' => 'Create withdraw request',
            'GET /account/{accountId}/withdraws' => 'Get withdraw history',
            'GET /account/{accountId}/withdraws/{withdrawId}' => 'Get specific withdraw',
        ]
    ];
});

