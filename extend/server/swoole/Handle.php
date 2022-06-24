<?php

namespace server\swoole;

use server\swoole\socketIo\Packet;
use server\swoole\socketIo\Parser;

trait  Handle
{
    /**
     * @param array $config
     * @return \Closure
     *
     */
    public static function connect(array  $config) : \Closure
    {
        return function ($server,$fd) use ($config)
        {

        };
    }

    /**
     * @param array $config
     * @return \Closure
     *
     */
    public static function open(array $config = []): \Closure
    {
        return function ($server, $request) use ($config) {

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

    /**
     * @param array $config
     * @return \Closure
     *
     */
    public static function message(array $config = []): \Closure
    {
        return function ($server, $frame) use ($config) {

            $payload = Parser::decode($frame);

            if (Parser::execute($server, $frame)) {
                return;
            }
            ['event' => $event] = $payload;
            return call_user_func_array([$config['event'][$event], 'send'], [$server, $frame,$config]);
        };
    }


    /**
     * @param array $config
     * @return \Closure
     */
    public static function close($config = []) : \Closure
    {
        return function ($ws, $fd) use ($config) {
            echo "client-{$fd} is closed\n";
        };
    }

    /**
     * @return \Closure
     */
    public static function task() : \Closure
    {
        return function ($ws, $task_id, $reactor_id, $data) {

        };
    }
}