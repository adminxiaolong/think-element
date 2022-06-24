<?php
return [
    'websocket' => [
        'chat' => [
            'host' => '0.0.0.0',
            'port' => '9509',
            'process_name' => 'chart',
            'socket_config' => [
                'open_websocket_pong_frame' => true,
                'heartbeat_idle_time' => 60,
                'heartbeat_check_interval' => 30,
                'open_websocket_ping_frame' => true,
                'reactor_num' => 2,
                'worker_num' => 2,
                'task_worker_num' => 1,
                'daemonize' => false,
                //'log_file' => '/www/wwwlogs/run.log',
                //'pid_file' => '/www/wwwlogs/server.pid',
                'reload_async' => false,
                'dispatch_mode' => 5
            ],
            'ping_interval' => 5000,
            'ping_timeout' => 6000,
            'event' => [
                'PrivateChat' => \server\swoole\chat\PrivateChat::class,
                'room' => \server\swoole\chat\Room::class
            ],
            'redis' => [
                'host' => '127.0.0.1',
                'port' => '6379',
                'timeout' => 5,
                'pass' => ''
            ],
            'room' => [
                'prefix_room_id' => 'chat_'
            ]
        ]
    ]
];