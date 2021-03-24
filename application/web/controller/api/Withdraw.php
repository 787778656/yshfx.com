<?php
/**
* 接口程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-06
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;

class Withdraw extends Controller
{

    protected $userInfo;
    public function __construct(){
        parent::__construct();
        $this->userInfo = Session::get('zhtweb');
        // 未登录
        if (empty($this->userInfo)){
            exit(json_encode(array('code'=> 301, 'msg' => '请先登录!')));
        }

        $this->userInfo = Db::name('User')->where('uid', $this->userInfo['uid'])->find();
    }

    /**
    * 添加提现
    * @return string
    */
    public function add(){
        if (input('?post.amount') && input('?post.account')){
            $data = array(
                    'uid' => $this->userInfo['uid'],
                    'amount' => input('post.amount'),
                    'account' => input('post.account'),
                    'add_time' => time(),
                    'operator' => 'withdraw-web-api',
                    'remark'   => '支付宝'
                );
            if (floatval($data['amount'])<10) exit(json_encode(array('code'=> 304, 'msg' => '提现金额不能低于10元!')));

            // if ($data['amount'] > $this->userInfo['imoney'] - $this->userInfo['credited']){
            if ($data['amount'] > $this->userInfo['imoney']){
                exit(json_encode(array('code'=> 305, 'msg' => '提现金额不能大于账户净余额!')));
            }

            if (Db::name('user_withdraw')->where(['uid' => $this->userInfo['uid'], 'status' => 0])->count() == 0){
                if (Db::name('user_withdraw')->insert($data)){
                    // AppCommon::sendmsg_czsms('18164026961', '用户提现，请及时处理！');
                    exit(json_encode(array('code'=> 200, 'msg' => '申请提交成功!')));
                }else{
                    exit(json_encode(array('code'=> 302, 'msg' => '申请提交失败, 请稍候再试.')));
                }
            }else{
                exit(json_encode(array('code'=> 303, 'msg' => '您有提现申请尚未处理,不能重复提交.')));                
            }
        }
    }

    /**
    * 获取明细
    * @return string
    */    
    public function get(){
        $page = input('post.page');
        if ($page < 1) $page = 1;
        $arrWithdraw = Db::name('user_money_log')->where('uid', $this->userInfo['uid'])->order('id desc')->limit(($page-1)*5, 5)->select();
        echo json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrWithdraw));
    }

    /**
    * 获取提现记录
    * @return string
    */    
    public function get_withdraw_detail(){
        $page = input('post.page');
        if ($page < 1) $page = 1;
        $arrWithdraw = Db::name('user_withdraw')->where('uid', $this->userInfo['uid'])->order('id desc')->limit(($page-1)*5, 5)->select();
        echo json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrWithdraw));
    }
}