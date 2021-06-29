<?php

return [
    'default'         => env('database.driver', 'mysql'),

    'time_query_rule' => [],

    'resultset_type' => 'array',

    'auto_timestamp'  => true,

    'datetime_format' => 'Y-m-d H:i:s',


    'connections'     => [
    'mysql' => [

    'type'            => env('database.type', 'mysql'),

    'hostname'        => env('database.hostname', '127.0.0.1'),

    'database'        => env('database.database', 'think-element'),

    'username'        => env('database.username', 'root'),

    'password'        => env('database.password', 'root'),

    'hostport'        => env('database.hostport', '3306'),

    'params'          => [],

    'charset'         => env('database.charset', 'utf8'),

    'prefix'          => env('database.prefix', 'td_'),


    'deploy'          => 0,

    'rw_separate'     => false,

    'master_num'      => 1,

    'slave_no'        => '',

    'fields_strict'   => true,

    'break_reconnect' => false,

    'trigger_sql'     => env('app_debug', true),

    'fields_cache'    => false,
    ]
],
];
