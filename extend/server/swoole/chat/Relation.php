<?php

namespace server\swoole\chat;

use server\redis\Redis;
use server\Single;

class Relation
{
    use Single;

    public $config = [];

    const REDIS_BIND_UID_TO_FD = 'redis:bind:uid:fd';


    protected $redis = null;


    public function __construct($name,$key,$option)
    {
        $this->redis = Redis::getInstance()->i(true,false,$option);
    }


    /**
     * @param $uid
     * @param $fd
     * @param Redis $redis_handle
     * @return bool|int
     */
    public function bindUidToFd($uid, $fd)
    {
        return $this->redis->hSet(self::REDIS_BIND_UID_TO_FD, $uid, $fd);
    }


    /**
     * @param $uid
     * @return array
     */
    public function getFdByUid($uid)
    {
        $fd = $this->redis->hGet(self::REDIS_BIND_UID_TO_FD, $uid);

        return  $fd ? [$fd] : [];
    }
}