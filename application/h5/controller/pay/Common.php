<?php
/**
* 支付通用方法
* @author efon.cheng<efon@icheng.xyz>
* 2017-09-18
*/
namespace app\h5\controller\pay;
use app\common\controller\Upgrade;
use \think\Config;
use \think\Controller;
use \think\Db;
use \think\Model;
use \think\Exception;
use app\common\controller\Reward;
use AlipayTradeService;

class Common extends controller
{
    protected $userInfo;
    protected $wxServerConf;
    public function __construct(){
        parent::__construct();

        // 验证token
        if (input('?token')){
            $token = input('token');
            if (!empty($token)){
                $this->userInfo = Db::name('user')->where('token', $token)->find();
            }
        }
        $this->wxServerConf = Config::get('wxmp')['server'];
    }

    /**
    * 检测是否登录
    */
    protected function isLogin(){
        if (empty($this->userInfo)){
            exit(json_encode(array('code'=> 301, 'msg' => '请先登录!')));
        }
    }

    protected function callback($data = null, $myConfig = null, $mothod = 'return'){
        if (empty($data) || empty($myConfig)){
            die('参数错误!');
        }

        $postData = $data;
        
        if ($mothod != 'wxNotify'){
            if ($mothod == 'return'){
                $token = '';
                $arrTradeNo = explode('_', $data['out_trade_no']);
                if(count($arrTradeNo) == 2){
                    $uid = Db::name('mt4_payment')->where(['id' => $arrTradeNo[1],'trade_no' => $arrTradeNo[0]])->value('uid');
                    $token = Db::name('user')->where('uid', $uid)->value('token');
                }

                if (!empty($token)){
                    $this->redirect(sprintf('%s?token=%s', $myConfig['redirectUrl'], $token));
                }else{
                    $this->redirect($myConfig['redirectUrl']);
                }                
                exit();
            }
            // 支付宝配制
            vendor('alipay.wappay.service.AlipayTradeService');
            require dirname(dirname(dirname(APP_PATH))).'/vendor/alipay/config.php';
            $alipaySevice = new AlipayTradeService($config);
            $result = $alipaySevice->check($postData);
            if ($result){
                // 验证成功
                if ($postData['trade_status'] == 'TRADE_SUCCESS'){
                    $this->dopayment($data);
                }
                echo "success";
            }else{
                echo "fail";
            }
            //if ($mothod == 'return') $this->redirect($myConfig['redirectUrl']);        
            exit();
        }else{
            // 发送数据
            $result = simplexml_load_string($postData['xml'], 'SimpleXMLElement', LIBXML_NOCDATA);

            if (isset($result->return_code) && $result->return_code == 'SUCCESS'){
                // 开始处理订单
                $orderDate['out_trade_no'] = strval($result->out_trade_no);
                $orderDate['trade_no'] = strval($result->transaction_id);

                $this->dopayment($orderDate);
            }
        }
    }

    /**
    * 处理支付订单
    */
    protected function dopayment($data = null){
        if ( ! empty($data)){
            $arrTradeNo = explode('_', $data['out_trade_no']);
            if(count($arrTradeNo) != 2) exit();
            $arrPayment = Db::name('mt4_payment')->where(['id' => $arrTradeNo[1],'trade_no' => $arrTradeNo[0]])->find();
            if ( ! empty($arrPayment)){
                switch ($arrPayment['type']) {
                    case 10: // 充值
                        $this->recharge($data);
                        break;
                    default: // 购买vip
                        $this->vip($data);
                        break;
                }
            }
        }
    }

    /**
    * 账号充值
    */
    private function recharge($data = null){
        if ( ! empty($data)){
            $arrTradeNo = explode('_', $data['out_trade_no']);
            if(count($arrTradeNo) != 2) exit();

            $map['id'] = $arrTradeNo[1];
            $map['trade_no'] = $arrTradeNo[0];
            $map['status'] = 0;
            if(Db::name('mt4_payment')->where($map)->update(['status' => 1, 'third_trade_no' => $data['trade_no']])==true){
                $map['status'] = 1;
                $arrPayment = Db::name('mt4_payment')->where($map)->order('id desc')->field('id,uid,amount,trade_no')->find();
                if ( ! empty($arrPayment)){
                    // 更新余额
                    Db::name('user')->where('uid', $arrPayment['uid'])->setInc('imoney', $arrPayment['amount']);
                }
            }
        }
    }

    /**
     * 购买vip
     */
    private function vip($data = null){
        if ( ! empty($data)){
            $arrTradeNo = explode('_', $data['out_trade_no']);
            if(count($arrTradeNo) != 2) exit();

            $map['id'] = $arrTradeNo[1];
            $map['trade_no'] = $arrTradeNo[0];
            $map['status'] = 0;
            if(Db::name('mt4_payment')->where($map)->update(['status' => 1, 'third_trade_no' => $data['trade_no']])){
                // 更新用户表
                $map['status'] = 1;
                $arrPayment = Db::name('mt4_payment')->where($map)->order('id desc')->field('id,uid,amount,trade_no,server,expire_time')->find();
                if ( ! empty($arrPayment)){
                    $days = ($arrPayment['expire_time']-time())/24/3600; //vip购买的天数
                    // 续费处理
                    $umap['uid'] = $arrPayment['uid'];
                    $umap['server'] = $arrPayment['server'];
                    $userInfo = Db::name('user')->where($umap)->field('server,server_expire')->find();
                    if (!empty($userInfo)){
                        if (empty($userInfo['server']) || $userInfo['server_expire'] < time()){
                            $userInfo['server_expire'] = time(); // 已过期从当天开始计算
                        }

                        $arrPayment['expire_time'] = $userInfo['server_expire']+$arrPayment['expire_time']-time();
                    }

                    Db::name('user')->where('uid', $arrPayment['uid'])->update(['server' => $arrPayment['server'], 'server_expire' => $arrPayment['expire_time']]);

                    //vip日志
                    Upgrade::record($arrPayment['uid'],$days,$arrPayment['server'], $arrPayment['expire_time'], 'h5-vip购买', 'api-buy-h5');
                    // 分佣
                    Reward::recharge($arrPayment['uid'], $arrPayment['amount'], $arrPayment['trade_no']);
                }
            }
        }
    }
}