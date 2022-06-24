<?php

namespace server\swoole\socketIo;

use think\facade\App;

class Parser
{
    protected $strategies = [];

    public static function execute($server, $frame)
    {
        $skip = false;

        $result = Heartbeat::handle($server,$frame);

        if ($result === true) {
            $skip = true;
        }

        return $skip;
    }

     public static function encode(string $event, $data){
         $packet = Packet::MESSAGE . Packet::EVENT;
         $shouldEncode = is_array($data) || is_object($data);
         $data = $shouldEncode ? json_encode($data) : $data;
         $format = $shouldEncode ? '["%s",%s]' : '["%s","%s"]';

         return $packet . sprintf($format, $event, $data);
     }

     public static function decode($frame)
     {
         $payload = Packet::getPayload($frame->data);

         return [
             'event' => $payload['event'] ?? null,
             'data' => $payload['data'] ?? null,
         ];
     }
}