<?php
namespace app\web\controller;
use \think\Config;
use \think\Db;
use \think\Session;
use think\Controller;
use \think\cache\driver\Redis;
use app\common\controller\Common as AppCommon;

class User extends Common
{
    public function __construct(){
        parent::__construct();   
    }
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        if (empty($this->userInfo)){
            // 可用信用金
            return $this->fetch('trade');
        }else{
            // 会员天数
            $serverDay = ceil(($this->userInfo['server_expire']-time())/3600/24);
            if ($serverDay < 0) $serverDay = 0;
            $this->assign('serverDay', $serverDay);
            return $this->fetch();
        }    	
    }

    /**
    * 信用金页面
    */
    public function credit(){
        return $this->fetch();
    }

    /**
     * vip会员系统
     */
    public function vip()
    {
        return $this->fetch();
    }

    /**
     * 三方登录
     */
    // public function tlogin(){
    //     $post = $_POST;
    //     if (input('?get.uid') && input('?get.tlogin')){            
    //         $tlogin = input('get.tlogin');
    //         $uid = input('get.uid');
    //         $cacheKey = sprintf('%s-%s-%s', 'zhtweb-tlogin', $tlogin, $uid);
    //         $redis = new Redis();
    //         if ($redis->has($cacheKey)){
    //             $openid = $redis->get($cacheKey);
    //             $user = Db::name('user')->where($tlogin, $openid)->find();

    //             $redis->rm($cacheKey);
    //             if ( ! empty($user)){
    //                 Session::set('zhtweb', $user);
    //                 $this->redirect('user/trade@www.yshfx.com');
    //             }
    //         }            
    //     }
    // }

    /**
    * 我的交易
    * @return mixed
    */
    public function trade()
    {
        $account = $this->userInfo['zhmt4uid'];
        $account = trim($account);
        $beginMoney = 0;
        if ( ! empty($account)){
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $arrCache = array();
            $redis = new Redis();
            if ($redis->get($key)){
                $arrCache = unserialize($redis->get($key));
            }
            $this->assign('accountDtail', $arrCache);

            // 初始入金
            $beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');           
            
            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();
            $this->assign('arrTrade', $arrTrade);            

            $arrFollow = Db::name('mt4_diy_account')->alias('a')->join('mt4_account b', 'a.mt4id = b.mt4id')->field('a.weight, a.maxhold, a.maxloss, b.name, b.country')->where('a.uid', $this->userInfo['uid'])->order('a.id desc')->paginate(30);
            $this->assign('arrFollow', $arrFollow);
            
            // 获取月统计
            $formatMdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', $account)->order('year,month')->select();
            foreach ($arrMdata as $key => $mdata) {
                $formatMdata[$mdata['year']][$mdata['month']] = $mdata;
            }
        }

        $this->assign('beginMoney', $beginMoney);
        $this->assign('account', $account);

        // 国家配置
        $mt4country = Config::get('options.mt4country');
        $this->assign('mt4country', array_flip($mt4country));
        
        // 服务商
        $mt4service = Config::get('options.mt4service');
        $this->assign('mt4service', array_flip($mt4service));

        return $this->fetch('trade');
    }

    /**
    * 我的交易
    * @return mixed
    */
    public function mytrade($account = 0){
        if ( ! empty($account)){
            $beginMoney = 0;
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $arrCache = array();
            $redis = new Redis();
            if ($redis->get($key)){
                $arrCache = unserialize($redis->get($key));
            }
            $this->assign('accountDtail', $arrCache);

            // 初始入金
            $beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');           
            
            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();
            $this->assign('arrTrade', $arrTrade);            

            $arrFollow = Db::name('mt4_diy_account')->alias('a')->join('mt4_account b', 'a.mt4id = b.mt4id')->field('a.weight, a.maxhold, a.maxloss, b.name, b.country')->where('a.uid', $this->userInfo['uid'])->order('a.id desc')->paginate(30);
            $this->assign('arrFollow', $arrFollow);
            
            // 获取月统计
            $formatMdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', $account)->order('year,month')->select();
            foreach ($arrMdata as $key => $mdata) {
                $formatMdata[$mdata['year']][$mdata['month']] = $mdata;
            }
            $this->assign('beginMoney', $beginMoney);
            $html = $this->fetch();
            echo $html;
        }
    }

    /**用户绑定手机号
     * @return string
     */
    public function mobile_binding()
    {
        $login = trim(input('login'));
        $password = trim(input('password'));
        $verify = trim(input('verify'));
        if ($this->userInfo['uid']) {
            return AppCommon::mobile_binding($login,$password,$verify,$this->userInfo['uid']);
        }
        return json_encode(['code' => 302, 'msg' => '请先登录']);
    }
}
