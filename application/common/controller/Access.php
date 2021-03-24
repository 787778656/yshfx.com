<?php
/**
* 钱包通用方法
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-27
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use think\Cookie;
use \think\Db;
use \think\Session;
use \think\Request;
use \think\cache\driver\Redis;

class Access extends Controller
{
    protected $redis;
	public function __construct()
    {
        parent::__construct();
        $this->redis = new Redis();
        // 获取用户信息
        $this->userInfo = Session::get('zhtweb');
        $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();
    }

    /**
    * 写入记录
    */
    public static function record($class = 'studio', $ip = null){
        $uid = Session::get('zhtweb')['uid'];
        if ( ! empty($this->userInfo)){
            $key = sprintf('%s%s', '', $this->userInfo);
            $this->redis->set($key, $arrCache);
        }
    }
}