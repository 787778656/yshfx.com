<?php
/**
* LRANK提供的支付接口
* @author tq
* 2018-08-01
*/
namespace app\api\controller\pay;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\api\controller\pay\Common;
use think\Request;

class Lrankpay extends Common
{
    private $lrankConf;
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('PRC');
        header("Content-type: text/html; charset=utf-8");
        $this->lrankConf = Config::get('lrankpay');
    }

    public function create_order()
    {
        // 获取数据
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $tradeNo = sprintf('%s-%s%s', 'lrankpay', $msectime, rand(1,9));
        $uid = input('post.uid');
        $server = input('post.server'); # vip类型
        $amount = input('post.amount'); # 总价
        $type = input('?post.type') ? input('post.type') : 1;
        $tradetype = input('post.tradetype');
        $tradetype_arr = ['21'=>'900021','22'=>'900022'];

        if (empty($uid) || empty($server) || (isset($this->lrankConf['server'][$server]) && ! in_array($amount, $this->lrankConf['server'][$server]))){
            die('参数错误!');
        }

        $tradetype_v = 0;
        if(array_key_exists($tradetype,$tradetype_arr))
        {
            $tradetype_v = $tradetype_arr[$tradetype];
        }else{
            die('参数错误!');
        }

        // 生成订单
        $expireDay = array_search($amount, $this->lrankConf['server'][$server]);
        // test
        if ($uid == '8795') $amount = 0.01;
        $payment = array(
                'uid' => $uid,
                'trade_no' => $tradeNo,
                'server' => $server,
                'amount' => $amount,
                'type' => $type,
                'expire_time' => time()+$expireDay*24*3600,
                'operator' => 'lrankpay-api',
                'modify_time' => time(),
                'add_time' => time(),
            );

        // 创建支付单
        $paymentId = Db::name('mt4_payment')->insertGetId($payment);

        if($paymentId){
            $Md5key = $this->lrankConf['md5key'];   //密钥
            $tjurl = $this->lrankConf['payUrl'];   //网关提交地址
            $jsapi = array(
                "pay_memberid" => $this->lrankConf['partner'],
                "pay_orderid" => sprintf('%s_%s', $tradeNo, $paymentId),
                "pay_amount" => $amount,
                "pay_applydate" => date("Y-m-d H:i:s"),
                "pay_bankcode" => $this->lrankConf['bankcode'],
                "pay_notifyurl" => $this->lrankConf['notifyUrl'],
                "pay_callbackurl" => $this->lrankConf['callbackUrl'],
            );

            ksort($jsapi);
            $md5str = "";
            foreach ($jsapi as $key => $val) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
            //echo($md5str . "key=" . $Md5key."<br>");
            $sign = strtoupper(md5($md5str . "key=" . $Md5key));
            $jsapi["pay_md5sign"] = $sign;
            $jsapi["pay_tongdao"] = 'Wlzhifu'; //通道
            $jsapi["pay_tradetype"] = $tradetype_v; //通道类型   900021 微信支付，900022 支付宝支付 
            $jsapi["pay_productname"] = 'LRANK服务'; //商品名称

            // 发送数据
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tjurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsapi);
            $output = curl_exec($ch);
            curl_close($ch);
            // 输出表单
            echo $output;
        }
    }

    public function notify_url(Request $request)
    {
        $notify_data = $request->param();
        $ReturnArray = array( // 返回字段
            "memberid" => $notify_data["memberid"], // 商户ID
            "orderid" =>  $notify_data["orderid"], // 订单号
            "amount" =>  $notify_data["amount"], // 交易金额
            "datetime" =>  $notify_data["datetime"], // 交易时间
            "returncode" => $notify_data["returncode"]
        );

        $Md5key = $this->lrankConf['md5key'];
   
		ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key)); 
        if ($sign == $notify_data["sign"]) {
			
            if ($notify_data["returncode"] == "00") {
                # 支付成功
                // $str = "交易成功！订单号：".$_REQUEST["orderid"];
                // file_put_contents("success.txt",$str."\n", FILE_APPEND);
                // exit("ok");
                $success_data['out_trade_no'] = $notify_data["orderid"];
                $success_data['trade_no'] = '0';

                $this->dopayment($success_data);
            }
        }
    }

    public function callback_url(Request $request)
    {
        $callback_data = $request->param();
        $ReturnArray = array( // 返回字段
            "memberid" => $callback_data["memberid"], // 商户ID
            "orderid" =>  $callback_data["orderid"], // 订单号
            "amount" =>  $callback_data["amount"], // 交易金额
            "datetime" =>  $callback_data["datetime"], // 交易时间
            "returncode" => $callback_data["returncode"]
        );

        $Md5key = $this->lrankConf['md5key'];
   
		ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key)); 
        if ($sign == $callback_data["sign"]) {
			
            if ($callback_data["returncode"] == "00") {
                # 支付成功
                // $str = "交易成功！订单号：".$_REQUEST["orderid"];
                // file_put_contents("success.txt",$str."\n", FILE_APPEND);
                // exit("ok");
                
                return $this->redirect('web/user/vip');
            }
        }

        return $this->error('支付失败','web/user/vip','',3);
    }
}