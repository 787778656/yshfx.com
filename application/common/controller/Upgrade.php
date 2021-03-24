<?php
/**
* 奖励公用类程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-11-17
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use think\Cookie;
use \think\Db;
use \think\Session;
use \think\cache\driver\Redis;

class Upgrade extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
    * 送vip天数
    */
    public static function updateVip($day = 7, $uid = 0, $remark = null, $operator = null, $vip='vip1'){
        if ( ! empty($uid)){ // 加vip7天
            $user = Db::name('user')->where('uid', $uid)->find();
            // 非vip直接加vip1                
            if (empty($user['server']) || $user['server_expire'] < time()){
                $data['server'] = $vip;
                $data['server_expire'] = time(); // 已过期从当天开始计算
            }else{ // 保持原vip
                $data['server'] = $user['server'];
                $data['server_expire'] = $user['server_expire'];
            }                
            $data['server_expire'] += 3600*24*$day; // 加7天

            // 写入log
            if (Db::name('user')->where('uid', $uid)->update($data)){
                $logData = array(
                        'uid' => $uid,
                        'server' => $data['server'],
                        'server_expire' => $data['server_expire'],
                        'days' => $day,
                        'remark' => $remark,
                        'operator' => $operator,
                        'add_time' => time(),
                        'modify_time' => time()
                    );                        
                if (Db::name('mt4_vip_log')->insert($logData)){ // 添加记录
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * 写入记录
     */
    public static function record($uid = 0, $days = 0, $server = null, $expire_day = null, $remark = null, $operator = 'apis-buy-wxcode'){
        if ( ! empty($uid)){
            $data['uid'] = $uid;
            $data['days'] = $days;
            $data['server'] = $server;
            $data['server_expire'] = $expire_day;
            $data['remark'] = $remark;
            $data['add_time'] = time();
            $data['modify_time'] = time();
            $data['operator'] = $operator;
            // 写入记录
            Db::name('Mt4VipLog')->insert($data);
        }
    }
}