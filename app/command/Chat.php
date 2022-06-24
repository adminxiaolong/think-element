<?php

namespace app\command;

use server\swoole\SwooleWebsocketServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Chat extends Command
{
    protected function configure()
    {
        $this->setName('chat')->setDescription('The chart');
    }

    protected function execute(Input $input, Output $output)
    {
        SwooleWebsocketServer::getInstance('chat')->start();
    }
}