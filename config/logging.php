<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    'default' => env('LOG_CHANNEL', 'stack'),
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'ignore_exceptions' => false,
            'channels' => ['daily', 'syslog', 'slack'],
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],
        'slack' => [
                'driver' => 'slack',
                'url' => env('LOG_SLACK_WEBHOOK_URL'),
                'name' => 'dev',
                'username' => env('SLACK_USERNAME'),
                'emoji' => ':boom:',
                'level' => 'notice',
            ],
        ],
        'rollbar' => [
            'driver' => 'monolog',
            'handler' => Rollbar\Monolog\Handler\RollbarHandler::class,
            'access_token' => env('ROLLBAR_ACCESS_TOKEN'),
            'level' => 'debug',
        ],
 ]
 ?>
