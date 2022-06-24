<?php
namespace server\swoole\chat;

use server\swoole\socketIo\Parser;
use server\swoole\socketIo\Pusher;
use think\facade\Db;

class PrivateChat
{
    const FORMAT_DATA = [
        'sender' => 0,
        'to' => 0,
        'text' => '',
        'create_time' => ''
    ];

    public static function send($server,$frame,$config)
    {
        $payload = Parser::decode($frame);

        if (Parser::execute($server, $frame)) {
            return;
        }

        ['event' => $event, 'data' => $data] = $payload;

        if(empty($data['to'])){
            $data['text'] = '必须选择发送对象';
        }

        $tofd = Relation::getInstance('default','default',$config['redis'])->getFdByUid($data['to']); //
        $sessionid = [$data['sender'],$data['to']];
        ksort($sessionid);
        $sessionid = implode('_',$sessionid);

        if($data['sender'] < 0){
            $user = Db::name('admin')->field('uname')->where('id',abs($data['sender']))->findOrEmpty();
            $user = [
                'avatar' => 'https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png',
                'nickname' => $user['uname']
            ];
        }else{
            $user = Db::name('users')->field('nickname,avatar')->where('id',$data['sender'])->findOrEmpty();
        }
        $send_time = date('Y-m-d H:i:s',time());
        $data['create_time'] =  $send_time;
        $data = array_merge($data,$user);

        $payload = [
            'sender' => $frame->fd,
            'fds' => $tofd,
            'broadcast' => false,
            'assigned' => false,
            'event' => $event,
            'message' => $data,
        ];

        Db::name('chat_customer')->insertGetId([
            'sessionid' => $sessionid,
            'uid' => $data['sender'],
            'to_uid' =>$data['to'],
            'text' => $data['text'],
            'nickname' => $data['nickname'],
            'avatar' => $data['avatar'],
            'create_time' => $send_time,
        ]);

        $pusher = Pusher::make($payload, $server);
        $pusher->push(Parser::encode($pusher->getEvent(), $pusher->getMessage()));
    }
}