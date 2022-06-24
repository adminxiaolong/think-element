<?php
namespace server\swoole\chat;

use server\swoole\socketIo\Parser;
use server\swoole\socketIo\Pusher;
use think\facade\Db;

/**
 * Class PrivateChat
 * @package server\swoole\chat
 * 实现私聊事件的消息发送
 */
class PrivateChat
{
    const FORMAT_DATA = [
        'sender' => 0,
        'to' => 0,
        'text' => '',
        'create_time' => ''
    ];

    /**
     * @param $server
     * @param $frame
     * @param $config
     * 发送消息的demo,这个数据入表需要根据自身情况来定义，主要用来查询历史消息等
     */
    public static function send($server,$frame,$config)
    {
        $payload = Parser::decode($frame);

        if (Parser::execute($server, $frame)) {
            return;
        }

        ['event' => $event, 'data' => $data] = $payload;

        //发送对象
        if(empty($data['to'])){
            $data['text'] = '必须选择发送对象';
        }
        //根据发送的对象来获取fd
        $tofd = Relation::getInstance('default','default',$config['redis'])->getFdByUid($data['to']); //

        //私聊消息的会话ID，根据自身业务来定，我这里只是写的一个demo
        $sessionid = [$data['sender'],$data['to']];
        ksort($sessionid);
        $sessionid = implode('_',$sessionid);

        //由于客服是admin,用户是user表，因此 用 uid < 0 来区分是后台用户还是客户端用户
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

        //todo 对于上面的逻辑都是可以不要的，只需要这里就可以了，根据自身情况来定
        $payload = [
            'sender' => $frame->fd,
            'fds' => $tofd,
            'broadcast' => false,
            'assigned' => false,
            'event' => $event,
            'message' => $data,
        ];

        //todo 数据入库，根据自身情况来定
        Db::name('chat_customer')->insertGetId([
            'sessionid' => $sessionid,
            'uid' => $data['sender'],
            'to_uid' =>$data['to'],
            'text' => $data['text'],
            'nickname' => $data['nickname'],
            'avatar' => $data['avatar'],
            'create_time' => $send_time,
        ]);

        //todo 发送消息生成
        $pusher = Pusher::make($payload, $server);
        $pusher->push(Parser::encode($pusher->getEvent(), $pusher->getMessage()));
    }
}