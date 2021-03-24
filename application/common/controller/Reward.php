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

class Reward extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
    * 邀请注册充值分成
    */
    public static function recharge($uid = 0, $amount = 0, $remark = null)
    {
        if ( ! empty($uid)){
            $parentId = Db::name('user')->where('uid', $uid)->value('parent_id');
            if ( ! empty($parentId) && $parentId != $uid){
                self::record($parentId, $amount*0.2, $uid, 'recharge', $remark);// 一级分成
                // self::record($parentId, $amount*0.3, $uid, 'recharge', $remark);// 一级分成
                // $parentId2 = Db::name('user')->where('uid', $parentId)->value('parent_id');
                // if ( ! empty($parentId2) && $parentId2 != $parentId){
                //     $srcId = sprintf('%s-%s', $uid, $parentId, $remark);
                //     self::record($parentId2, $amount*0.1, $srcId, 'recharge', $remark);// 二级分成
                // }
            }            
        }
    }

    /**
    * 写入记录
    */
    private static function record($uid = 0, $amount = 0, $srcId = null, $class = null, $remark = null){
        if ( ! empty($uid)){
            $data['uid'] = $uid;
            $data['amount'] = $amount;
            $data['src_id'] = $srcId;
            $data['class'] = $class;
            $data['remark'] = $remark;
            $data['add_time'] = time();
            $data['modify_time'] = time();
            $data['operator'] = 'reward-api';
            // 写入记录
            Db::name('user_reward')->insert($data);
        }
    }
}