<?php
/**
* 支付宝支付程序
* @author coder<coder@qq.com>
* 2017-10-09
*/
namespace app\api\controller\pay;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\api\controller\pay\Common;

class Alipay extends Common
{
    private $aliConf;
    public function __construct()
    {
        parent::__construct();
        $this->aliConf = Config::get('alipay');
    }

    public function create_order()
    {
        // 获取数据
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $tradeNo = sprintf('%s-%s%s', 'alipay', $msectime, rand(1,9));
        $uid = input('post.uid');
        $server = input('post.server');
        $amount = input('post.amount');
        $type = input('?post.type') ? input('post.type') : 1;

        if (empty($uid) || empty($server) || (isset($this->aliConf['server'][$server]) && ! in_array($amount, $this->aliConf['server'][$server]))){
            die('参数错误!');
        }

        // 生成订单
        $expireDay = array_search($amount, $this->aliConf['server'][$server]);
        // test
        if ($uid == '13567') $amount = 0.01;
        $payment = array(
                'uid' => $uid,
                'trade_no' => $tradeNo,
                'server' => $server,
                'amount' => $amount,
                'type' => $type,
                'expire_time' => time()+$expireDay*24*3600,
                'operator' => 'alipay-api',
                'modify_time' => time(),
                'add_time' => time(),
            );

        // 创建支付单
        $paymentId = Db::name('mt4_payment')->insertGetId($payment);

        if($paymentId){
        // 构造数据        
            $postData = array(
                'WIDout_trade_no' => sprintf('%s_%s', $tradeNo, $paymentId),
                'WIDsubject' => $this->aliConf['trade']['subject'],
                'WIDbody' => $this->aliConf['trade']['body'],
                'WIDtotal_amount' => $amount,
            );

            // 发送数据
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->aliConf['payUrl']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $output = curl_exec($ch);
            curl_close($ch);
            // 输出表单
            echo $output;
        }
    }

    public function notify_url()
    {
        $postData = $_POST;
        $this->callback($postData, $this->aliConf, 'notify');
    }

    public function return_url()
    {
        $postData = $_GET;
        $this->callback($postData, $this->aliConf, 'return');
    }
}