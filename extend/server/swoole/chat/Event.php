<?php

namespace server\swoole\chat;

use server\jwt\Jwt;
use server\swoole\Handle;
use server\swoole\socketIo\Packet;
use think\facade\Config;

class Event
{
    use Handle;

    /**
     * @param array $config
     * @return \Closure
     * todo 重写 open方法，简单的使用jwt进行了一个鉴权，没有深入判断token是否有效，自己根据业务变化而变化，也可以使用 handle中的公用open方法
     */
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