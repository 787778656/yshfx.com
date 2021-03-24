<?php
/**
* 微信支付程序
* @author coder<coder@qq.com>
* 2017-10-09
*/
namespace app\api\controller\pay;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\api\controller\pay\Common;
use QRcode;

class Wxpay extends Common
{
    private $wxConf;
    public function __construct()
    {
        parent::__construct();
        $this->wxConf = Config::get('wxpay');
    }

    public function qrcode()
    {
        // 获取数据
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $tradeNo = sprintf('%s-%s%s', 'wxpay', $msectime, rand(1,9));
        $uid = input('get.uid');
        $server = input('get.server');
        $amount = input('get.amount');
        $type = input('?get.type') ? input('get.type') : 1;

        if (empty($uid) || empty($server) || (isset($this->wxConf['server'][$server]) && ! in_array($amount, $this->wxConf['server'][$server]))){
            die('参数错误!');
        }

        // 生成订单
        $expireDay = array_search($amount, $this->wxConf['server'][$server]);
        // test
        if ($uid == '9231') $amount = 0.01;
        $payment = array(
                'uid' => $uid,
                'trade_no' => $tradeNo,
                'server' => $server,
                'amount' => $amount,
                'type' => $type,
                'expire_time' => time()+$expireDay*24*3600,
                'operator' => 'wxpay-api',
                'modify_time' => time(),
                'add_time' => time(),
            );

        // 创建支付单
        $paymentId = Db::name('mt4_payment')->insertGetId($payment);

        if($paymentId){
        // 构造数据        
            $postData = array(
                'WXout_trade_no' => sprintf('%s_%s', $tradeNo, $paymentId),
                'WXsubject' => $this->wxConf['trade']['subject'],
                'WXbody' => $this->wxConf['trade']['body'],
                'WXtotal_amount' => $amount*100,
                'WXnotify_url' => $this->wxConf['wxNotifyUrl'],
            );

            // 发送数据
            $this->doCurl($postData);
        }
    }

    public function notify_url()
    {
        $postData['xml'] = file_get_contents('php://input');
        $this->callback($postData, $this->wxConf, 'wxNotify');
    }

    private function doCurl($postData = null){
        if ( ! empty($postData)){
            // 发送数据
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->wxConf['payUrl']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $output = curl_exec($ch);
            curl_close($ch);
            if (strstr($output, 'weixin')){
                vendor('phpqrcode.phpqrcode');
                QRcode::png($output);
            }
        }
    }
}