<?php

namespace server\redis;

use server\Single;

class Redis
{
    use Single;

    public function i($pconnect = false,$serializer = false,$config = [])
    {
        try {
            $redis = new \Redis();

            if(empty($config)){
                $host = env('redis.host','127.0.0.1');
                $port = env('redis.port','6379');
                $pass = env('auth');
                $timeout = env('redis.timeout',5);
            }else{
                ['host' => $host,'port' => $port,'timeout' => $timeout,'pass' => $pass] = $config;
            }

            if($pconnect){
                $redis->pconnect($host,$port,$timeout);
            }else{
                $redis->connect($host,$port,$timeout);
            }
            if (!empty($pass)) $redis->auth($pass);
            if($serializer == true){
                $redis->setOption( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
                $redis->setOption( \Redis::OPT_READ_TIMEOUT,  env('redis.timeout') );
            }
            return $redis;
        }catch (\Exception $exception){
            var_dump($exception->getMessage());
        }
    }
}