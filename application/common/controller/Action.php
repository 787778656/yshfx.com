<?php
/**
* 活动类程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-11-23
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use think\Cookie;
use \think\Db;
use \think\Session;
use \think\cache\driver\Redis;

class Action extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
    * 免费领取7天vip
    */
    public static function vip7($uid = 0)
    {
        if ( ! empty($uid)){
            $user = Db::name('user')->where('uid', $uid)->find();
            if ( ! empty($user)){
                $log = Db::name('action_vip7')->where('uid', $uid)->count();
                if (empty($log)){
                    // 非vip直接加vip1                
                    if (empty($user['server']) || $user['server_expire'] < time()){
                        $data['server'] = 'vip1';
                        $data['server_expire'] = time(); // 已过期从当天开始计算
                    }else{
                        $data['server'] = $user['server'];
                        $data['server_expire'] = $user['server_expire'];
                    }                
                    $data['server_expire'] += 3600*24*7; // 加7天
                    if (Db::name('user')->where('uid', $uid)->update($data)){
                        $logData = array(
                                'uid' => $uid,
                                'operator' => 'vip7-api',
                                'add_time' => time(),
                                'modify_time' => time()
                            );
                        Db::name('action_vip7')->insert($logData); // 添加记录
                        return true;
                    }
                }
            }
        }
        return false;
    }
}