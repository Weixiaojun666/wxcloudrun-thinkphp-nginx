<?php
namespace app\controller;

use app\BaseController;
use app\model\User;
use EasyWeChat\OfficialAccount\Application;
class Index extends BaseController
{
    public function index()
    {
        $app = new Application();
        $server = $app->getServer();
        try {
            $server
                ->addEventListener('subscribe', function ($message, \Closure $next) {
                    //订阅消息
                    //数据库添加信息
                    $data =[
                        'openid'  =>  $message['FromUserName'],
                        'UpdateTime' => $message['CreateTime'],
                        'ToUserName' => $message['ToUserName'],
                        'subscribe' => '1'
                    ];
                    //判断是否注册过
                    $user = User::where('openid',$message['FromUserName'])->findOrEmpty();
                    if ($user ->isEmpty()){
                        //未注册过
                        $user->save($data);
                    }else{
                        //已注册过
                        User::update($data);
                    }
                    return '终于等到您来啦~感谢您的订阅~';
                })
                ->addEventListener('unsubscribe', function ($message, \Closure $next) {
                    //取消订阅
                    //数据库更改状态
                    $user =new User();
                    $user::update([
                        'openid'  =>  $message['FromUserName'],
                        'UpdateTime' => $message['CreateTime'],
                        'ToUserName' => $message['ToUserName'],
                        'subscribe' => '0'
                    ]);
                    return '';
                })
                ->addEventListener('LOCATION', function ($message, \Closure $next) {
                    //位置上报
                    //数据库记录
                    $data=[
                        'openid'  =>  $message['FromUserName'],
                        'UpdateTime' => $message['CreateTime'],
                        'ToUserName' => $message['ToUserName'],
                        'Location_X' =>$message['Latitude'],
                        'Location_Y' =>$message['Longitude'],
                        'Location_P' =>$message['Precision']
                    ];
                    //判断是否关注
                    $user = User::where('openid',$message['FromUserName'])->findOrEmpty();
                    if ($user ->isEmpty()){
                        //未关注
                        $user->save($data);
                    }else{
                        //已关注
                        User::update($data);
                    }
                    return '';
                })

                ->addMessageListener('text', function ($message, \Closure $next) {
                    //文本消息
                    return $message["Content"];
                })
                ->addMessageListener('image', function ($message, \Closure $next) {
                    //图片消息
                    return [
                        'MsgType' => 'image',
                        'Image' => [
                            'MediaId' => $message['MediaId'],
                        ],
                    ];
                })
                ->addMessageListener('voice', function ($message, \Closure $next) {
                    //语音消息
                    return [
                        'MsgType' => 'voice',
                        'Voice' => [
                            'MediaId' => $message['MediaId'],
                        ],
                    ];
                })
                ->addMessageListener('video', function ($message, \Closure $next) {
                    //视频消息
                    return [
                        'MsgType' => 'video',
                        'Video' => [
                            'MediaId' => $message['MediaId'],
                        ],
                    ];
                })
                ->addMessageListener('shortvideo', function ($message, \Closure $next) {
                    //小视频消息
                    return [
                        'MsgType' => 'video',
                        'Video' => [
                            'MediaId' => $message['MediaId'],
                        ],
                    ];
                })
                ->addMessageListener('location', function ($message, \Closure $next) {
                    //位置消息
                    return '你发了一个位置';
                })
                ->addMessageListener('link', function ($message, \Closure $next) {
                    //链接消息
                    return '你发了一个链接';
                })
                ->addMessageListener('file', function ($message, \Closure $next) {
                    //文件消息
                    return '你发了一个文件';
                });

            return $server->serve();

        } catch (\Throwable $e) {

        }
    }
}
