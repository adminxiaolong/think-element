<?php
namespace server\swoole\chat;

class Room
{
    const FRAME_ROOM_MESSAGE = [
        'room_id' => '',
        'token' => '',
    ];

    /**
     * @param $server
     * @param $frame
     * 解析参数
     */
    public function send($server,$frame)
    {

    }
}