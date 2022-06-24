<?php

namespace server\swoole\chat;

use server\jwt\Jwt;
use server\swoole\Handle;
use server\swoole\socketIo\Packet;
use think\facade\Config;

class Event
{
    use Handle;

    public static function open(array $config = []): \Closure
    {
        return function ($server, $request) use ($config) {

            $token = $request->get['token'];

            if(empty($token)){
                $server->close($request->fd,true);
            }
            $user_info  = Jwt::decode($token, Config::get('jwt.key'), ['HS256']);

            Relation::getInstance('default','default',$config['redis'])->bindUidToFd($user_info->uid,$request->fd);

            $payload = json_encode([
                'sid' => base64_encode(uniqid()),
                'upgrades' => [],
                'pingInterval' => $config['ping_interval'],
                'pingTimeout' => $config['ping_timeout'],
            ]);

            $initPayload = Packet::OPEN . $payload;
            $connectPayload = Packet::MESSAGE . Packet::CONNECT;
            $server->push($request->fd, $initPayload);
            $server->push($request->fd, $connectPayload);

        };
    }
}