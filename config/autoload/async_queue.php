<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis' => [
            'pool' => 'default'
        ],
        'channel' => 'hyperf:queue',
        'timeout' => 2,
        'retry_seconds' => [1, 5, 10, 20, 60],
        'handle_timeout' => 30,
        'processes' => 2,
        'concurrent' => [
            'limit' => 10,
        ],
        'max_messages' => 0,
    ],
];
