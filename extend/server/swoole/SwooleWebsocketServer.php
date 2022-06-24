<?php

namespace server\swoole;

use server\Single;
use think\facade\Config;

class SwooleWebsocketServer
{
    use Single;

    public $ws = null;

    public $name;

    public $config = [];

    public function __construct($name = 'default')
    {
        $this->name = $name;

        $this->config = Config::load('swoole', 'config')['websocket'][$name];

        $this->ws = new \Swoole\WebSocket\Server($this->config['host'], $this->config['port']);

        $this->ws->set($this->config['socket_config']);

        if (isset($this->config['process_name']) && !empty($this->config['process_name'])) {
            swoole_set_process_name($this->config['process_name']);
        }
    }


    public function onConnect()
    {
        $this->ws->on('connect', call_user_func_array(["server\\swoole\\{$this->name}\\Event", "connect"], [$this->config]));
    }

    public function onOpen()
    {
        $this->ws->on('open', call_user_func_array(["server\\swoole\\{$this->name}\\Event", "open"], [$this->config]));
    }

    public function onMessage()
    {
        $this->ws->on('message', call_user_func_array(["server\\swoole\\{$this->name}\\Event", 'message'],[$this->config]));
    }


    public function onClose()
    {
        $this->ws->on('close', call_user_func_array(["server\\swoole\\{$this->name}\\Event", 'close'],[$this->config]));
    }

    public function onTask()
    {
        $this->ws->on('task', call_user_func_array(["server\\swoole\\{$this->name}\\Event", 'task'],[]));
    }

    public function onStart()
    {
        $this->ws->start();
    }


    public function start()
    {
        $this->onOpen();
        $this->onConnect();
        $this->onMessage();
        $this->onClose();
        $this->onTask();
        $this->onStart();
    }
}