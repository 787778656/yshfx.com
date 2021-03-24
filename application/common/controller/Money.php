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
use \think\cache\driver\Redis;

class Money extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
    * 写入记录
    */
    public static function record($uid = 0, $amount = 0, $srcId = null, $class = null, $remark = null,$operator = null, $gmsRemark = null){
        if ( ! empty($uid)){
            $data['uid'] = $uid;
            $data['amount'] = $amount;
            $data['src_id'] = $srcId;
            $data['class'] = $class;
            $data['remark'] = $remark;
            $data['gms_remark'] = $gmsRemark;
            $data['add_time'] = time();
            // $data['modify_time'] = time();
            // $data['operator'] = 'money-api';
            $data['operator'] = empty($operator)?'money-api':$operator;
            // 写入记录
            Db::name('user_money_log')->insert($data);
        }
    }


    /**
    * 信用金记录
    */
    public static function record_credit($uid = 0, $amount = 0, $srcId = null, $class = null, $remark = null, $operator = null, $gmsRemark = null){
        if ( ! empty($uid)){
            $data['uid'] = $uid;
            $data['amount'] = $amount;
            $data['src_id'] = $srcId;
            $data['class'] = $class;
            $data['remark'] = $remark;
            $data['gms_remark'] = $gmsRemark;
            $data['add_time'] = time();
            // $data['modify_time'] = time();
            $data['operator'] = empty($operator)?'credit-api':$operator;
            // 写入记录
            Db::name('user_credit_log')->insert($data);
        }
    }

}