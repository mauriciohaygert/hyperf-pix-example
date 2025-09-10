<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

return [
    'default' => [
        'handlers' => [
            [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
        ],
    ],
    'withdraw' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/withdraw.log',
                    'maxFiles' => 14,
                    'level' => Logger::INFO,
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
        ],
    ],
    'email' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/email.log',
                    'maxFiles' => 14,
                    'level' => Logger::INFO,
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
        ],
    ],
    'cron' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/cron.log',
                    'maxFiles' => 30,
                    'level' => Logger::INFO,
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
        ],
    ],
];
