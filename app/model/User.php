<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class User extends Model
{
    protected $table = 'User';
    public $openid;
    public $nickname;
    public $headimgurl;
    public $update_time;
    public $subscribe;
    public $unionid;
    public $subscribe_time;

}