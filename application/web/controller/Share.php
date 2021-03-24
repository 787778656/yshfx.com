<?php
/**
* zht-web首页
*/
namespace app\web\controller;
use think\Cookie;
use \think\Db;
use think\Controller;
use think\Request;
use think\Session;
use think\Config;
use \think\cache\driver\Redis;
use app\common\controller\Common as AppCommon;

class Share extends Common
{

    public function __construct(){
        parent::__construct();
        $inviteUid = input('?get.uid')?input('get.uid'):0;
        $inviteInfo = Db::name('user')->where('uid', $inviteUid)->find();
        $this->assign('inviteInfo', $inviteInfo);
    }

    /**
     * 我的交易
     */
    public function trade($uid = 0)
    {
        $uid = input('get.uid');
        $account = 0;
        $userInfo = array();
        $beginMoney = 0;
        if ( ! empty($uid)){
            $userInfo = Db::name('user')->where('uid', $uid)->find();
            if ( ! empty($userInfo)){
                // 账户实时信息
                if ( ! empty($userInfo['zhmt4uid'])) $account = $userInfo['zhmt4uid'];
                $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
                $arrCache = array();
                $redis = new Redis();
                if ($redis->get($key)){
                    $arrCache = unserialize($redis->get($key));
                }
                $this->assign('accountDtail', $arrCache);
                // 账户相关信息

                // 获取总统计
                $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();
                $this->assign('arrTrade', $arrTrade);            

                $arrFollow = Db::name('mt4_diy_account')->alias('a')->join('mt4_account b', 'a.mt4id = b.mt4id')->field('a.weight, a.maxhold, a.maxloss, b.name, b.country')->where('a.uid', $this->userInfo['uid'])->order('a.id desc')->paginate(30);
                $this->assign('arrFollow', $arrFollow);

                // 初始入金
                $beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance'); 
            }
        }

        $this->assign('beginMoney', $beginMoney);

        $this->assign('shareUserInfo', $userInfo);

        $this->assign('account', $account);    

        // 国家配置
        $mt4country = Config::get('options.mt4country');
        $this->assign('mt4country', array_flip($mt4country));
        
        // 服务商
        $mt4service = Config::get('options.mt4service');
        $this->assign('mt4service', array_flip($mt4service));

        return $this->fetch();
    }
}
