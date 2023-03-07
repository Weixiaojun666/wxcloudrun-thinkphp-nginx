<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Db;
use EasyWeChat\OfficialAccount\Application;
use think\facade\Cookie;
use think\facade\Config;
class Login extends BaseController
{

    public function Test(){
        return Config::get("wechat")["app_id"];
    }
    public function getInfo($openid=''){

        $result=  (new User)->where('openid',$openid)->find(1);
        //$result= Db::table("tb_user")->where('openid',$openid)->find();
        if ($result == null){
            returnjson(900,"openid错误");
        } else{
            $data=[
                "nickname"=>$result["nickname"],
                "headimgurl"=>$result["headimgurl"],
                "IDV"=>false,
                "PHV"=>false
            ];
            returnjson(200,"成功",$data);
        }
    }
    public function token($token= ''){
//        if ($token != "QwjXZidxIfy7LwmY2QZu3tMxqauJTBbQ"){
//            return null;
//        }
        $app = new Application();

        $token = $app->getAccessToken()->getToken();
        return $token ;

    }
    public function index(){
        $app = new Application(Config::get("wechat"));
        $oauth = $app->getOauth();
        if (empty(Cookie::get('wbx_wechat'))) {
            //生成完整的授权URL
            $redirectUrl = $oauth->redirect('https://api.5bianxing.com/Login/Callback');
            return redirect($redirectUrl);
        } else {
            // 已经登录过，则从 session 中取授权者信息
            // $user = Session::get('PHPSESSID');
            $user=Cookie::get('wbx_wechat');
            $user=json_decode($user,true);
            $url="https://www.5bianxing.com/wechat.html?openid=".$user['id'];
            $data =[
                'openid'  =>  $user['id'],
                'nickname' => $user['nickname'],
                'headimgurl' => $user['avatar'],
                'update_time' => date('Y-m-d H:i:s', time())
            ];


            $result= Db::table("tb_user")->where('openid',$user['id'])->findOrEmpty();
            //$user = User::where('openid',$user['id'])->findOrEmpty();
            if ($result == null){
                //库中无
                (new User)->where('openid',$user['id'])->insert($data);
                //Db::table("tb_user")->where('openid',$user['id'])->insert($data);
            }else{
                //库中有
                (new User)->where('openid',$user['id'])->update($data);
                Db::table("tb_user")->where('openid',$user['id'])->update($data);
            }

            return redirect($url);
        }

    }
    public function Callback()
    {
        $app = new Application();
        $oauth = $app->getOauth();
        $user = $oauth->userFromCode($_GET['code']);
        Cookie::set('wbx_wechat', json_encode($user->toArray()), 3600);
        //Session::set('PHPSESSID',$user->toArray());
        return redirect("https://api.5bianxing.com/login");
        //$targetUrl = empty($_SESSION['intend_url']) ? '/' : $_SESSION['intend_url'];
    }
}