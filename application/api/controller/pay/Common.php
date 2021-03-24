<?php
/**
* 支付通用方法
* @author coder<coder@qq.com>
* 2017-09-18
*/
namespace app\api\controller\pay;
use \think\Config; 
use \think\Controller;
use \think\Db;
use \think\Model;
use \think\Exception;
use app\common\controller\Reward;

class Common extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function callback($data = null, $config = null, $mothod = 'return'){
        if (empty($data) || empty($config)){
            die('参数错误!');
        }

        $postData = $data;
        if ($mothod != 'wxNotify'){            
            // 发送数据
            $ch = curl_init();
            if ($mothod == 'return'){
                curl_setopt($ch, CURLOPT_URL, $config['returnUrl']);
            }else{
                curl_setopt($ch, CURLOPT_URL, $config['notifyUrl']);
            }
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $output = curl_exec($ch);
            curl_close($ch);
            if ($output == 'success'){
                // 验证成功        
                $this->dopayment($data);
            }
            if ($mothod == 'return') $this->redirect($config['redirectUrl']);        
            exit();
        }else{
            // 发送数据
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config['notifyUrl']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $output = curl_exec($ch);
            curl_close($ch);

            $wxResult = simplexml_load_string($postData['xml'], 'SimpleXMLElement', LIBXML_NOCDATA);
            $result = simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOCDATA);

            if (isset($result->return_code) && $result->return_code == 'SUCCESS'){
                // 开始处理订单
                $orderDate['out_trade_no'] = strval($wxResult->out_trade_no);
                $orderDate['trade_no'] = strval($wxResult->transaction_id);
                
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
            if(Db::name('mt4_payment')->where($map)->update(['status' => 1, 'third_trade_no' => $data['trade_no']])){
                $map['status'] = 1;
                $arrPayment = Db::name('mt4_payment')->where($map)->order('id desc')->field('id,uid,amount,trade_no,server,expire_time')->find();
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

                    // 分佣
                    Reward::recharge($arrPayment['uid'], $arrPayment['amount'], $arrPayment['trade_no']);
                }                
            }
        }
    }
}